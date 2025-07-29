<?php
require_once 'models/Supplier.php';

function listSuppliers() {
    global $error;
    $suppliers = Supplier::all();
    include 'views/suppliers/index.php';
}

function showCreateSupplierForm() {
    include 'views/suppliers/create.php';
}

function storeSupplier() {
    Supplier::create($_POST);
    header('Location: ?route=suppliers');
}

function showEditSupplierForm($id) {
    $supplier = Supplier::find($id);
    include 'views/suppliers/edit.php';
}

function updateSupplier($id) {
    Supplier::update($id, $_POST);
    header('Location: ?route=suppliers');
}

function deleteSupplier($id) {
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


function dashboard() {
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

    include 'views/suppliers/dashboard.php';
}
