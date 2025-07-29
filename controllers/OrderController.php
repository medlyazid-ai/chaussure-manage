<?php
// controllers/OrderController.php

require_once 'models/Order.php';
require_once 'models/Product.php';
require_once 'models/Supplier.php';

function listOrders() {
    $supplierId = $_GET['supplier_id'] ?? null;
    $status = $_GET['status'] ?? null;

    $orders = Order::filterWithSupplier($supplierId, $status);
    $suppliers = Supplier::all(); // pour le filtre

    include 'views/orders/index.php';
}




function showCreateOrderForm() {
    $suppliers = Supplier::all();
    $products = Product::all();
    include 'views/orders/create.php';
}

function storeOrder() {
    $orderId = Order::create($_POST);
    header("Location: ?route=orders");
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

