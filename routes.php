<?php

require_once __DIR__ . '/config/db.php';
$pdo = Database::getInstance();

$route = $_GET['route'] ?? '';
$parts = explode('/', $route);
$resource = $parts[0] ?? '';
$action = $parts[1] ?? null;
$id = $parts[2] ?? null;

// 📦 Importation des contrôleurs
require_once 'controllers/AuthController.php';
require_once 'controllers/ProductController.php';
require_once 'controllers/SupplierController.php';
require_once 'controllers/OrderController.php';
require_once 'controllers/PaymentController.php';
require_once 'controllers/ShipmentController.php';
require_once 'controllers/StockController.php';
require_once 'controllers/ClientSaleController.php';
require_once 'controllers/TransportController.php';

// =====================
// 📌 ROUTING LOGIQUE
// =====================
switch ($resource) {

    // 🔐 Auth
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

    // 👞 Produits
    case 'products':
        switch ($action) {
            case null: listProducts(); break;
            case 'create': showCreateForm(); break;
            case 'store': storeProduct(); break;
            case 'edit': showEditForm($id); break;
            case 'update': updateProduct($id); break;
            case 'delete': deleteProduct($id); break;
            default: echo "404 - Action produits inconnue."; break;
        }
        break;

    // 👤 Fournisseurs
    case 'suppliers':
        switch ($action) {
            case null: listSuppliers(); break;
            case 'create': showCreateSupplierForm(); break;
            case 'store': storeSupplier(); break;
            case 'edit': showEditSupplierForm($id); break;
            case 'update': updateSupplier($id); break;
            case 'delete': deleteSupplier($id); break;
            case 'dashboard': dashboard(); break;
            default: echo "404 - Action fournisseur inconnue."; break;
        }
        break;

    // 💰 Paiements
    case 'payments':
        switch ($action) {
            case null: listPayments(); break;
            case 'create': showCreatePaymentForm(); break;
            case 'store': storePayment(); break;
            case 'fetch_orders_by_supplier':
                fetchOrdersBySupplier($_GET['supplier_id']);
                break;
            case 'delete': deletePayment($id); break;
            default: echo "404 - Action paiement inconnue."; break;
        }
        break;

    // 📦 Commandes
    case 'orders':
        switch ($action) {
            case null: listOrders(); break;
            case 'create': showCreateOrderForm(); break;
            case 'store': storeOrder(); break;
            case 'edit': showEditOrderForm($id); break;
            case 'update': updateOrder($id); break;
            case 'delete': deleteOrder($id); break;
            case 'show': showOrder($id); break;
            case 'update-status':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') updateOrderStatus($id);
                break;
            default: echo "404 - Action commande inconnue."; break;
        }
        break;

    // 🚚 Envois
    case 'shipments':
        switch ($action) {
            case null: listShipments(); break;
            case 'create': showCreateShipmentForm(); break;
            case 'store': storeShipment(); break;
            case 'delete': deleteShipment($id); break;
            case 'show': showShipment($id); break;
            case 'update_status': updateShipmentStatus($id); break;
            default: echo "404 - Action envoi inconnue."; break;
        }
        break;

    // 🧾 Ventes Client
    case 'client_sales':
        switch ($action) {
            case null: listClientSales(); break;
            case 'create': createClientSale($_GET['country_id'] ?? null); break;
            case 'store': storeClientSale(); break;
            case 'edit': editClientSale($id); break;
            case 'update': updateClientSale($id); break;
            case 'delete': deleteClientSale($id); break;
            case 'show': showClientSale($id); break;
            default: echo "404 - Action vente client inconnue."; break;
        }
        break;

    // 🏷 Stocks
    case 'stocks':
        switch ($action) {
            case null: listRealStocks(); break;
            case 'adjust': adjustStock(); break;
            case 'delete-adjustment': deleteStockAdjustment(); break;
            default: echo "404 - Action stock inconnue."; break;
        }
        break;

    // 🚛 Transporteurs
    case 'transports':
        switch ($action) {
            case null: listTransports(); break;
            case 'create': createTransportForm(); break;
            case 'store': storeTransport(); break;
            case 'edit': editTransportForm($id); break;
            case 'update': updateTransport($id); break;
            case 'delete': deleteTransport($id); break;
            default: echo "404 - Action transport inconnue."; break;
        }
        break;

    // 📍 Formulaire de sélection de pays (vente client)
    case 'select_country':
        showCountrySelectionForm();
        break;

    // ❌ Par défaut
    default:
        echo "404 - Page non trouvée.";
        break;
}
