<?php
// controllers/ShipmentController.php

require_once 'models/Shipment.php';
require_once 'models/Order.php';
require_once 'models/Payment.php';
require_once 'models/Stock.php';


function listShipments() {
    $rawShipments = Shipment::allWithOrderAndSupplier(); // ➜ On crée cette méthode dans le modèle
    $availableOrders = Order::allWithSupplier();

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

    include 'views/shipments/index.php';
}



function showCreateShipmentForm() {
    if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
        echo "ID de commande manquant.";
        return;
    }

    $orderId = $_GET['order_id'];
    $order = Order::findWithSupplier($orderId);
    //$orderItems = Order::orderItems($orderId);
    $orderItems = Order::orderItemsWithSentQuantities($orderId);


    if (!$order) {
        echo "Commande introuvable.";
        return;
    }

    include 'views/shipments/create.php';
}


function storeShipment() {
    $shipmentId = Shipment::create($_POST, $_FILES);
    if ($shipmentId) {
        header("Location: index.php?route=orders/show/" . $_POST['order_id']);
        exit;
    } else {
        echo "❌ Erreur lors de la création de l'envoi partiel.";
    }
}


function deleteShipment($id) {
    Shipment::delete($id);
    header("Location: ?route=shipments");
}


function showShipment($id) {
    $shipment = Shipment::find($id);
    $order = Order::findWithSupplier($shipment['order_id']);
    $items = Shipment::getVariants($id);
    include 'views/shipments/show.php';
}

function updateShipmentStatus($id) {
    global $pdo;

    $id = (int) $id;

    // 🔍 Récupérer le shipment
    $stmt = $pdo->prepare("SELECT * FROM shipments WHERE id = ?");
    $stmt->execute([$id]);
    $shipment = $stmt->fetch();

    if (!$shipment) {
        $_SESSION['error'] = "Envoi introuvable.";
        header("Location: ?route=shipments");
        exit;
    }

    $newStatus = $_POST['status'] ?? null;

    // ✅ Si le statut devient "Arrivé à destination" et le stock n’a pas encore été ajouté
    if ($newStatus === 'Arrivé à destination' && !$shipment['is_stock_added']) {

        // 🧠 Étape 1 : Récupérer le country_id via la commande liée à l'envoi
        $stmt = $pdo->prepare("
            SELECT o.country_id
            FROM orders o
            JOIN shipments s ON o.id = s.order_id
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        $country_id = $stmt->fetchColumn();

        if (!$country_id) {
            $_SESSION['error'] = "❌ Impossible de récupérer le pays pour cet envoi.";
            header("Location: ?route=shipments");
            exit;
        }

        // 🧠 Étape 2 : Récupérer les variantes et quantités envoyées
        $itemsStmt = $pdo->prepare("
            SELECT oi.variant_id, si.quantity_sent
            FROM shipment_items si
            JOIN order_items oi ON si.order_item_id = oi.id
            WHERE si.shipment_id = ?
        ");
        $itemsStmt->execute([$id]);
        $items = $itemsStmt->fetchAll();

        // 🧠 Étape 3 : Mettre à jour le stock pays pour chaque variante
        foreach ($items as $item) {
            Stock::addOrUpdateStock($country_id, $item['variant_id'], $item['quantity_sent']);
        }

        // ✅ Marquer que le stock a été ajouté
        $updateStockFlag = $pdo->prepare("UPDATE shipments SET is_stock_added = 1 WHERE id = ?");
        $updateStockFlag->execute([$id]);
    }

    // 🔁 Mise à jour du statut du shipment
    $stmt = $pdo->prepare("UPDATE shipments SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $id]);

    $_SESSION['success'] = "Statut de l’envoi mis à jour avec succès.";
    header("Location: ?route=shipments/show/$id");
    exit;
}



