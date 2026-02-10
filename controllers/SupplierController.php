<?php

require_once 'models/Supplier.php';
require_once 'models/Order.php';
require_once 'models/Payment.php';
require_once 'models/Shipment.php';

function listSuppliers()
{
    global $error;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 20;
    $offset = ($page - 1) * $perPage;
    $total = Supplier::countAll();
    $totalPages = (int)ceil($total / $perPage);

    $suppliers = Supplier::allPaged($perPage, $offset);
    include 'views/suppliers/index.php';
}

function showCreateSupplierForm()
{
    include 'views/suppliers/create.php';
}

function storeSupplier()
{
    Supplier::create($_POST);
    header('Location: ?route=suppliers');
}

function showEditSupplierForm($id)
{
    $supplier = Supplier::find($id);
    include 'views/suppliers/edit.php';
}

function updateSupplier($id)
{
    Supplier::update($id, $_POST);
    header('Location: ?route=suppliers');
}

function deleteSupplier($id)
{
    try {
        Supplier::delete($id);
        header("Location: ?route=suppliers");
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
        $suppliers = Supplier::all();
        include 'views/suppliers/index.php';
    }
}


function dashboard()
{
    if (empty($_GET['id'])) {
        $error = "ID fournisseur manquant.";
        include 'views/errors/404.php';
        return;
    }

    $supplierId = intval($_GET['id']);
    $supplier = Supplier::find($supplierId);

    if (!$supplier) {
        $error = "Fournisseur introuvable.";
        include 'views/errors/404.php';
        return;
    }

    $orders = Order::findBySupplier($supplierId);
    $payments = Payment::findBySupplier($supplierId);
    $shipments = Shipment::bySupplier($supplierId);

    // Stats
    $totalOrders = count($orders);
    $totalAmount = 0;
    $totalPaid = 0;
    $statusCounts = [];

    foreach ($orders as $order) {
        $total = Order::getTotalAmount($order['id']);
        $paid = Payment::totalAllocatedToOrder($order['id']);
        $totalAmount += $total;
        $totalPaid += $paid;
        $statusCounts[$order['status']] = ($statusCounts[$order['status']] ?? 0) + 1;
    }
    $totalRemaining = max($totalAmount - $totalPaid, 0);

    include 'views/suppliers/dashboard.php';
}
