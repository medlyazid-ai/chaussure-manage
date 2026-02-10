<?php

// controllers/OrderController.php

require_once 'models/Order.php';
require_once 'models/Product.php';
require_once 'models/Supplier.php';
require_once 'models/Country.php';
require_once 'models/Company.php';
require_once 'models/Variant.php';

function listOrders()
{
    $supplierId = $_GET['supplier_id'] ?? null;
    $status = $_GET['status'] ?? null;
    $countryId = $_GET['country_id'] ?? null;
    $dateFrom = $_GET['date_from'] ?? null;
    $dateTo = $_GET['date_to'] ?? null;
    $companyId = $_GET['company_id'] ?? null;

    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 20;
    $offset = ($page - 1) * $perPage;
    $total = Order::countFiltered($supplierId, $status, $countryId, $dateFrom, $dateTo, $companyId);
    $totalPages = (int)ceil($total / $perPage);

    $orders = Order::filterWithSupplier($supplierId, $status, $countryId, $dateFrom, $dateTo, $companyId, $perPage, $offset);
    $suppliers = Supplier::all();
    $countries = Country::all();
    $companies = Company::all();

    include 'views/orders/index.php';
}



function showCreateOrderForm()
{
    $suppliers = Supplier::all();
    $products = Product::all();
    $countries = Country::all();
    $companies = [];
    include 'views/orders/create.php';
}

function storeOrder()
{
    if (empty($_POST['supplier_id']) || empty($_POST['country_id']) || empty($_POST['product_id'])) {
        echo "Champs obligatoires manquants.";
        return;
    }

    $unitPrice = $_POST['unit_price'] ?? null;
    if ($unitPrice === null || $unitPrice === '') {
        echo "Prix unitaire obligatoire.";
        return;
    }

    // ✅ Inclure les variantes dans le tableau transmis
    $orderId = Order::create([
        'supplier_id' => $_POST['supplier_id'],
        'country_id' => $_POST['country_id'],
        'company_id' => $_POST['company_id'] ?? null,
        'product_id' => $_POST['product_id'],
        'unit_price' => $unitPrice,
        'variants' => $_POST['variants'] ?? [] // <--- important !
    ]);

    header("Location: ?route=orders");
    exit;
}

function fetchVariantsByProduct($productId)
{
    header('Content-Type: text/html; charset=utf-8');
    $variants = [];
    if ($productId) {
        $variants = Variant::findByProduct($productId);
    }
    include 'views/orders/_variant_rows.php';
    exit;
}


function deleteOrder($id)
{
    Order::delete($id);
    header("Location: ?route=orders");
}


function showOrder($id)
{
    $order = Order::findWithSupplier($id);
    $orderItems = Order::orderItems($id);
    $partialShipments = Order::partialShipments($id);
    $payments = Payment::paymentsByOrder($id);

    // ✅ Ajouter le produit lié à la commande
    $product = Product::find($order['product_id']);

    include 'views/orders/show.php';
}



function showEditOrderForm($id)
{
    $order = Order::find($id);
    if (!$order) {
        echo "Commande introuvable.";
        return;
    }

    $suppliers = Supplier::all();
    $products = Product::all();
    $variants = Order::orderItems($id);
    $countries = Country::all();
    $companies = Company::byCountry($order['country_id']);

    include 'views/orders/edit.php';
}


function updateOrder($id)
{
    if (empty($_POST['supplier_id']) || empty($_POST['country_id']) || empty($_POST['product_id'])) {
        echo "Champs obligatoires manquants.";
        return;
    }

    Order::update($id, [
        'supplier_id' => $_POST['supplier_id'],
        'country_id' => $_POST['country_id'],
        'company_id' => $_POST['company_id'] ?? null,
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


function updateOrderStatus($id)
{
    if (!isset($_POST['status'])) {
        echo "Statut manquant.";
        return;
    }

    Order::updateStatus($id, $_POST['status']);
    header("Location: ?route=orders");
    exit;
}
