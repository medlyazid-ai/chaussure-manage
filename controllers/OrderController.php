<?php
// controllers/OrderController.php

require_once 'models/Order.php';
require_once 'models/Product.php';
require_once 'models/Supplier.php';
require_once 'models/Country.php';

function listOrders() {
    $supplierId = $_GET['supplier_id'] ?? null;
    $status = $_GET['status'] ?? null;

    $orders = Order::filterWithSupplier($supplierId, $status);
    $suppliers = Supplier::all();
    $countries = Country::all(); // Si tu veux afficher les filtres pays plus tard

    include 'views/orders/index.php';
}




function showCreateOrderForm() {
    $suppliers = Supplier::all();
    $products = Product::all();
    $countries = Country::all();
    include 'views/orders/create.php';
}

function storeOrder() {
    if (empty($_POST['supplier_id']) || empty($_POST['country_id']) || empty($_POST['product_id'])) {
        echo "Champs obligatoires manquants.";
        return;
    }

    // ✅ Inclure les variantes dans le tableau transmis
    $orderId = Order::create([
        'supplier_id' => $_POST['supplier_id'],
        'country_id' => $_POST['country_id'],
        'product_id' => $_POST['product_id'],
        'variants' => $_POST['variants'] ?? [] // <--- important !
    ]);

    header("Location: ?route=orders");
    exit;
}



function deleteOrder($id) {
    Order::delete($id);
    header("Location: ?route=orders");
}

function showOrder($id) {
    $order = Order::findWithSupplier($id); // commande + fournisseur
    $orderItems = Order::orderItems($id);  // variantes commandées
    $partialShipments = Order::partialShipments($id); // envois liés
    $payments = Order::payments($id); // paiements liés
    include 'views/orders/show.php';
}


function showEditOrderForm($id) {
    $order = Order::find($id);
    if (!$order) {
        echo "Commande introuvable.";
        return;
    }

    $suppliers = Supplier::all();
    $products = Product::all();
    $variants = Order::orderItems($id); 
    $countries = Country::all();

    include 'views/orders/edit.php';
}



function updateOrder($id) {
    if (empty($_POST['supplier_id']) || empty($_POST['country_id']) || empty($_POST['product_id'])) {
        echo "Champs obligatoires manquants.";
        return;
    }

    Order::update($id, [
        'supplier_id' => $_POST['supplier_id'],
        'country_id' => $_POST['country_id'],
        'product_id' => $_POST['product_id']
    ]);

    try {
        Order::deleteOrderItems($id);
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'a foreign key constraint fails')) {
            echo "❌ Impossible de modifier cette commande car des envois partiels y sont déjà liés.";
            return;
        } else {
            throw $e; // autre erreur SQL
        }
    }

    if (!empty($_POST['variants'])) {
        foreach ($_POST['variants'] as $variant) {
            Order::addOrderItem($id, $variant, $_POST['product_id']);
        }
    }

    header('Location: ?route=orders');
    exit;
}



function updateOrderStatus($id) {
    if (!isset($_POST['status'])) {
        echo "Statut manquant.";
        return;
    }

    Order::updateStatus($id, $_POST['status']);
    header("Location: ?route=orders");
    exit;
}


