<?php
// controllers/ShipmentController.php

require_once 'models/Shipment.php';
require_once 'models/Order.php';
require_once 'models/Payment.php';


function listShipments() {
    $shipments = Shipment::all();
    $availableOrders = Order::allWithSupplier(); // <- nécessaire pour peupler le dropdown
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
    $newStatus = $_POST['status'];
    Shipment::updateStatus($id, $newStatus);
    header("Location: ?route=shipments/show/$id");
    exit;
}

