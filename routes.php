<?php
$route = $_GET['route'] ?? '';

// 📦 Importation des contrôleurs
require_once 'controllers/AuthController.php';
require_once 'controllers/ProductController.php';
require_once 'controllers/SupplierController.php';
require_once 'controllers/OrderController.php';
require_once 'controllers/PaymentController.php';
require_once 'controllers/ShipmentController.php';


// ✅ Route spéciale "show order" (car hors structure REST classique)
if (preg_match('#^orders/show/(\d+)$#', $route, $matches)) {
    showOrder($matches[1]);
    return;
}


// =========================
// 🔁 ROUTING PRINCIPAL
// =========================
switch ($route) {

    // ----------------------
    // 🔐 Authentification
    // ----------------------
    case 'login':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? login() : loginForm();
        break;

    case 'register':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? register() : registerForm();
        break;

    case 'logout':
        logout();
        break;

    case 'dashboard':
        include 'views/dashboard/index.php';
        break;


    // ----------------------
    // 👞 Produits
    // ----------------------
    case 'products':
        listProducts();
        break;
    case 'products/create':
        showCreateForm();
        break;
    case 'products/store':
        storeProduct();
        break;
    case (preg_match('#^products/edit/(\d+)$#', $route, $m) ? true : false):
        showEditForm($m[1]);
        break;
    case (preg_match('#^products/update/(\d+)$#', $route, $m) ? true : false):
        updateProduct($m[1]);
        break;
    case (preg_match('#^products/delete/(\d+)$#', $route, $m) ? true : false):
        deleteProduct($m[1]);
        break;


    // ----------------------
    // 👤 Fournisseurs
    // ----------------------
    case 'suppliers':
        listSuppliers();
        break;
    case 'suppliers/create':
        showCreateSupplierForm();
        break;
    case 'suppliers/store':
        storeSupplier();
        break;
    case (preg_match('#^suppliers/edit/(\d+)$#', $route, $m) ? true : false):
        showEditSupplierForm($m[1]);
        break;
    case (preg_match('#^suppliers/update/(\d+)$#', $route, $m) ? true : false):
        updateSupplier($m[1]);
        break;
    case (preg_match('#^suppliers/delete/(\d+)$#', $route, $m) ? true : false):
        deleteSupplier($m[1]);
        break;
    case 'suppliers/dashboard':
        dashboard();
        break;


    // ----------------------
    // 💰 Paiements
    // ----------------------
    case 'payments':
        listPayments();
        break;
    case 'payments/create':
        showCreatePaymentForm();
        break;
    case 'payments/store':
        storePayment();
        break;
    case 'payments/fetch_orders_by_supplier':
        fetchOrdersBySupplier($_GET['supplier_id']);
        break;
    case (preg_match('#^payments/delete/(\d+)$#', $route, $m) ? true : false):
        deletePayment($m[1]);
        break;


    // ----------------------
    // 📦 Commandes
    // ----------------------
    case 'orders':
        listOrders();
        break;
    case 'orders/create':
        showCreateOrderForm();
        break;
    case 'orders/store':
        storeOrder();
        break;
    case (preg_match('#^orders/delete/(\d+)$#', $route, $m) ? true : false):
        deleteOrder($m[1]);
        break;


    // ----------------------
    // 🚚 Envois (Shipments)
    // ----------------------
    case 'shipments':
        listShipments();
        break;
    case 'shipments/create':
        showCreateShipmentForm();
        break;
    case 'shipments/store':
        storeShipment();
        break;
    case (preg_match('#^shipments/delete/(\d+)$#', $route, $m) ? true : false):
        deleteShipment($m[1]);
        break;
    case (preg_match('#^shipments/show/(\d+)$#', $route, $m) ? true : false):
        showShipment($m[1]);
        break;
    case (preg_match('#^shipments/update_status/(\d+)$#', $route, $m) ? true : false):
        updateShipmentStatus($m[1]);
        break;


    // ----------------------
    // ❌ Par défaut : erreur 404
    // ----------------------
    default:
        echo "404 - Page non trouvée.";
        break;
}
