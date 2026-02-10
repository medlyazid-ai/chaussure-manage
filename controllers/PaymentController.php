<?php
// controllers/PaymentController.php
require_once 'models/Payment.php';
require_once 'models/Supplier.php';
require_once 'models/Order.php';
require_once 'models/Partner.php';

function listPayments() {
    $search = $_GET['search'] ?? null;
    $dateFrom = $_GET['date_from'] ?? null;
    $dateTo = $_GET['date_to'] ?? null;
    $method = $_GET['method'] ?? null;

    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 20;
    $offset = ($page - 1) * $perPage;
    $total = Payment::countFiltered($search, $dateFrom, $dateTo, $method);
    $totalPages = (int)ceil($total / $perPage);

    $payments = Payment::filter($search, $dateFrom, $dateTo, $method, $perPage, $offset);
    $methods = Payment::methods();
    include 'views/payments/index.php';
}

function showCreatePaymentForm() {
    $suppliers = Supplier::all();
    $partners = Partner::all();
    $prefillSupplierId = $_GET['supplier_id'] ?? null;
    $prefillOrderId = $_GET['order_id'] ?? null;
    include 'views/payments/create.php';
}

function storePayment() {
    $supplierId = $_POST['supplier_id'] ?? null;
    $partnerId = $_POST['partner_id'] ?? null;

    if (!$supplierId) {
        $error = "Aucun fournisseur sélectionné.";
        listPayments(); // réaffiche la liste avec erreur
        return;
    }
    if (!$partnerId) {
        $error = "Aucun partenaire sélectionné.";
        listPayments();
        return;
    }

    // Récupérer les affectations manuelles si présentes
    $allocations = $_POST['allocations'] ?? [];

    // Nettoyage des allocations (enlever celles nulles ou ≤ 0)
    foreach ($allocations as $orderId => $amount) {
        if (floatval($amount) <= 0) {
            unset($allocations[$orderId]);
        }
    }

    // Marqueur si case "auto_allocate" cochée
    $autoAllocate = isset($_POST['auto_allocate']) && $_POST['auto_allocate'] === 'on';

    // ❌ Si aucune allocation et pas d’auto => erreur
    if (empty($allocations) && !$autoAllocate) {
        $error = "Aucune allocation manuelle et aucune répartition automatique sélectionnée.";
        listPayments();
        return;
    }

    try {
        Payment::create($_POST, $_FILES, $allocations);
        header('Location: ?route=payments');
        exit;
    } catch (Exception $e) {
        $error = "Erreur lors de l’enregistrement : " . $e->getMessage();
        listPayments();
    }
}





function deletePayment($id) {
    Payment::delete($id);
    header('Location: ?route=payments');
}

function fetchOrdersBySupplier($supplierId) {
    header('Content-Type: text/html; charset=utf-8');
    $orders = Order::getUnpaidBySupplier($supplierId);
    $prefillOrderId = $_GET['order_id'] ?? null;
    include 'views/payments/_orders_table.php';
    exit;
}
