<?php

// controllers/ShipmentController.php

require_once 'models/Shipment.php';
require_once 'models/Order.php';
require_once 'models/Payment.php';
require_once 'models/Stock.php';
require_once 'models/Transport.php';
require_once 'models/Supplier.php';
require_once 'models/Product.php';
require_once 'models/Country.php';
require_once 'models/Company.php';
require_once 'utils.php';


function listShipments()
{
    $filters = [
        'supplier_id' => $_GET['supplier_id'] ?? null,
        'product_id' => $_GET['product_id'] ?? null,
        'country_id' => $_GET['country_id'] ?? null,
        'company_id' => $_GET['company_id'] ?? null,
        'date_from' => $_GET['date_from'] ?? null,
        'date_to' => $_GET['date_to'] ?? null,
    ];

    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 20;
    $offset = ($page - 1) * $perPage;
    $total = Shipment::countFiltered($filters);
    $totalPages = (int)ceil($total / $perPage);

    $rawShipments = Shipment::allWithOrderAndSupplier($filters, $perPage, $offset);
    $availableOrders = Order::withRemainingQuantities();
    $suppliers = Supplier::all();
    $products = Product::all();
    $countries = Country::all();
    $companies = Company::all();

    // Structure : $shipmentsGrouped[Fournisseur][Commande] = liste d'envois
    $shipmentsGrouped = [];

    foreach ($rawShipments as $shipment) {
        $supplierName = $shipment['supplier_name'];
        $orderId = $shipment['order_id'];
        $orderLabel = "Commande #$orderId";

        if (!isset($shipmentsGrouped[$supplierName])) {
            $shipmentsGrouped[$supplierName] = [];
        }

        if (!isset($shipmentsGrouped[$supplierName][$orderLabel])) {
            $shipmentsGrouped[$supplierName][$orderLabel] = [];
        }

        $shipmentsGrouped[$supplierName][$orderLabel][] = $shipment;
    }

    $shipments = $shipmentsGrouped;
    $view = $_GET['view'] ?? 'grouped';

    include 'views/shipments/index.php';
}



function showCreateShipmentForm()
{
    if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
        echo "ID de commande manquant.";
        return;
    }

    $orderId = $_GET['order_id'];
    $order = Order::findWithSupplier($orderId);
    //$orderItems = Order::orderItems($orderId);
    $orderItems = Order::orderItemsWithSentQuantities($orderId);
    $transports = Transport::all();



    if (!$order) {
        echo "Commande introuvable.";
        return;
    }

    include 'views/shipments/create.php';
}


function storeShipment()
{
    try {
        // 🔁 Gérer les fichiers
        $receiptPath = null;
        if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
            validate_upload_or_throw(
                $_FILES['receipt'],
                ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'],
                5 * 1024 * 1024
            );
            $receiptDir = 'uploads/receipts/';
            ensure_upload_dir($receiptDir);
            $fileName = uniqid() . '_' . sanitize_filename($_FILES['receipt']['name']);
            $receiptPath = $receiptDir . $fileName;
            if (!move_uploaded_file($_FILES['receipt']['tmp_name'], $receiptPath)) {
                throw new Exception("Échec de l'upload du reçu.");
            }
        }

        $packageImagePath = null;
        if (isset($_FILES['package_image']) && $_FILES['package_image']['error'] === UPLOAD_ERR_OK) {
            validate_upload_or_throw(
                $_FILES['package_image'],
                ['image/jpeg', 'image/png', 'image/webp'],
                5 * 1024 * 1024
            );
            $imgDir = 'uploads/package_images/';
            ensure_upload_dir($imgDir);
            $fileName = uniqid() . '_' . sanitize_filename($_FILES['package_image']['name']);
            $packageImagePath = $imgDir . $fileName;
            if (!move_uploaded_file($_FILES['package_image']['tmp_name'], $packageImagePath)) {
                throw new Exception("Échec de l'upload de l'image du colis.");
            }
        }

        // 🔁 Préparer les données à envoyer au modèle
        $data = [
            'order_id'       => $_POST['order_id'],
            'shipment_date'  => $_POST['shipment_date'],
            'notes'          => $_POST['notes'] ?? null,
            'transport_id'   => $_POST['transport_id'],
            'receipt_path'   => $receiptPath,
            'tracking_code'  => $_POST['tracking_code'] ?? null,
            'package_weight' => $_POST['package_weight'] ?? null,
            'transport_fee'  => $_POST['transport_fee'] ?? null,
            'package_image'  => $packageImagePath,
            'shipment_items' => $_POST['shipment_items'] ?? [],
        ];

        // 🔁 Créer l'envoi
        $shipmentId = Shipment::create($data, $_FILES);

        if ($shipmentId) {
            header("Location: index.php?route=orders/show/" . $_POST['order_id']);
            exit;
        } else {
            throw new Exception("Erreur lors de la création de l'envoi partiel.");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        $orderId = $_POST['order_id'] ?? '';
        header("Location: ?route=shipments/create&order_id=" . $orderId);
        exit;
    }
}



function deleteShipment($id)
{
    Shipment::delete($id);
    header("Location: ?route=shipments");
}


function showShipment($id)
{
    $shipment = Shipment::find($id);
    $order = Order::findWithSupplier($shipment['order_id']);
    $items = Shipment::getVariants($id);
    include 'views/shipments/show.php';
}

function updateShipmentStatus($id)
{
    global $pdo;
    $id = (int)$id;

    // 🔍 Récupérer le shipment
    $stmt = $pdo->prepare("SELECT * FROM shipments WHERE id = ?");
    $stmt->execute([$id]);
    $shipment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$shipment) {
        $_SESSION['error'] = "Envoi introuvable.";
        header("Location: ?route=shipments");
        exit;
    }

    // Récupérer le statut choisi
    $newStatus = $_POST['status'] ?? null;
    $comment   = $_POST['delivery_comment'] ?? null;

    if (!$newStatus) {
        $_SESSION['error'] = "Statut manquant.";
        header("Location: ?route=shipments/update_status/$id");
        exit;
    }

    // ✅ Si le statut devient "Livré à destination" et le stock n’a pas encore été ajouté
    if ($newStatus === 'Livré à destination' && (int)$shipment['is_stock_added'] === 0) {

        // 1) Récupérer le pays
        $stmt = $pdo->prepare("
            SELECT o.country_id
            FROM orders o
            JOIN shipments s ON o.id = s.order_id
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        $country_id = (int)$stmt->fetchColumn();

        if (!$country_id) {
            $_SESSION['error'] = "Impossible de récupérer le pays pour cet envoi.";
            header("Location: ?route=shipments");
            exit;
        }

        // 2) Variantes et quantités envoyées
        $itemsStmt = $pdo->prepare("
            SELECT oi.variant_id, si.quantity_sent
            FROM shipment_items si
            JOIN order_items oi ON si.order_item_id = oi.id
            WHERE si.shipment_id = ?
        ");
        $itemsStmt->execute([$id]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

        // 3) Mise à jour du stock pays
        foreach ($items as $item) {
            Stock::addOrUpdateStock($country_id, (int)$item['variant_id'], (int)$item['quantity_sent']);
        }

        // 4) Marquer que le stock a été ajouté
        $pdo->prepare("UPDATE shipments SET is_stock_added = 1 WHERE id = ?")->execute([$id]);
    }

    // 🔁 Mise à jour du statut (+ éventuel commentaire)
    $pdo->prepare("UPDATE shipments SET status = ? WHERE id = ?")->execute([$newStatus, $id]);

    if ($newStatus === 'Livré à destination') {
        $pdo->prepare("UPDATE shipments SET delivery_comment = ? WHERE id = ?")->execute([$comment, $id]);
    }

    $_SESSION['success'] = "Statut de l’envoi mis à jour avec succès.";
    header("Location: ?route=shipments/show/$id");
    exit;
}


function showUpdateShipmentStatusForm($id)
{
    global $pdo;
    $id = (int)$id;

    // Récupération du shipment + commande pour contexte
    $stmt = $pdo->prepare("
        SELECT 
            s.*, 
            o.order_date,
            c.name AS country_name,
            COALESCE(o.order_date, s.shipment_date) AS doc_date
        FROM shipments s
        JOIN orders o   ON o.id = s.order_id
        JOIN countries c ON c.id = o.country_id
        WHERE s.id = ?
    ");

    $stmt->execute([$id]);
    $shipment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$shipment) {
        $_SESSION['error'] = "Envoi introuvable.";
        header("Location: ?route=shipments");
        exit;
    }

    // Liste des statuts proposés (standardise ici)
    $availableStatuses = [
        'En attente de confirmation',
        'Validé et en cours de production',
        'Envoi partiel',
        'Envoi complet',
        'Livré à destination' // ✅ cohérent avec ta vue SQL
    ];

    include 'views/shipments/update_status.php';
}
