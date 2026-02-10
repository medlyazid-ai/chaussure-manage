<?php

require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/config/db.php';
$pdo = Database::getInstance();

$route = $_GET['route'] ?? '';
$parts = explode('/', $route);
$resource = $parts[0] ?? '';
$action = $parts[1] ?? null;
$id = $parts[2] ?? null;

start_session_if_needed();

// 🔐 Auth guard (tout sauf login/register)
$publicRoutes = ['login', 'register'];
if (!in_array($resource, $publicRoutes, true)) {
    require_once 'auth_check.php';
}

// 🛡️ CSRF protection for all POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
}

// 📦 Importation des contrôleurs
require_once 'controllers/AuthController.php';
require_once 'controllers/DashboardController.php';
require_once 'controllers/ProductController.php';
require_once 'controllers/SupplierController.php';
require_once 'controllers/CountryController.php';
require_once 'controllers/CompanyController.php';
require_once 'controllers/VariantController.php';
require_once 'controllers/PartnerController.php';
require_once 'controllers/PartnerAccountController.php';
require_once 'controllers/CompanyInvoiceController.php';
require_once 'controllers/PartnerExpenseController.php';
require_once 'controllers/ReportController.php';
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
        showDashboard();
        break;

        // 👞 Produits
    case 'products':
        switch ($action) {
            case null: listProducts();
                break;
            case 'create': showCreateForm();
                break;
            case 'store': storeProduct();
                break;
            case 'edit': showEditForm($id);
                break;
            case 'update': updateProduct($id);
                break;
            case 'delete': deleteProduct($id);
                break;
            default: echo "404 - Action produits inconnue.";
                break;
        }
        break;

        // 🔖 Variantes
    case 'variants':
        switch ($action) {
            case null: listVariants();
                break;
            case 'store': storeVariant();
                break;
            case 'update': updateVariant($id);
                break;
            case 'delete': deleteVariant($id);
                break;
            case 'stock': showVariantStockByCountry();
                break;
            default: echo "404 - Action variante inconnue.";
                break;
        }
        break;

        // 👤 Fournisseurs
    case 'suppliers':
        switch ($action) {
            case null: listSuppliers();
                break;
            case 'create': showCreateSupplierForm();
                break;
            case 'store': storeSupplier();
                break;
            case 'edit': showEditSupplierForm($id);
                break;
            case 'update': updateSupplier($id);
                break;
            case 'delete': deleteSupplier($id);
                break;
            case 'dashboard': dashboard();
                break;
            default: echo "404 - Action fournisseur inconnue.";
                break;
        }
        break;

        // 🌍 Pays
    case 'countries':
        switch ($action) {
            case null: listCountries();
                break;
            case 'create': showCreateCountryForm();
                break;
            case 'store': storeCountry();
                break;
            case 'edit': showEditCountryForm($id);
                break;
            case 'update': updateCountry($id);
                break;
            case 'delete': deleteCountry($id);
                break;
            default: echo "404 - Action pays inconnue.";
                break;
        }
        break;

        // 🏢 Sociétés (par pays)
    case 'companies':
        switch ($action) {
            case null: listCompanies();
                break;
            case 'create': showCreateCompanyForm();
                break;
            case 'store': storeCompany();
                break;
            case 'edit': showEditCompanyForm($id);
                break;
            case 'update': updateCompany($id);
                break;
            case 'delete': deleteCompany($id);
                break;
            case 'by_country': fetchCompaniesByCountry($_GET['country_id'] ?? null);
                break;
            default: echo "404 - Action société inconnue.";
                break;
        }
        break;

        // 🤝 Partenaires
    case 'partners':
        switch ($action) {
            case null: listPartners();
                break;
            case 'dashboard': partnerDashboard($id ?? ($_GET['id'] ?? null));
                break;
            case 'create': showCreatePartnerForm();
                break;
            case 'store': storePartner();
                break;
            case 'edit': showEditPartnerForm($id);
                break;
            case 'update': updatePartner($id);
                break;
            case 'delete': deletePartner($id);
                break;
            default: echo "404 - Action partenaire inconnue.";
                break;
        }
        break;

        // 🏦 Comptes partenaires
    case 'accounts':
        switch ($action) {
            case null: listPartnerAccounts();
                break;
            case 'create': showCreatePartnerAccountForm();
                break;
            case 'store': storePartnerAccount();
                break;
            case 'edit': showEditPartnerAccountForm($id);
                break;
            case 'update': updatePartnerAccount($id);
                break;
            case 'delete': deletePartnerAccount($id);
                break;
            case 'by_partner': fetchAccountsByPartner($_GET['partner_id'] ?? null);
                break;
            default: echo "404 - Action compte inconnue.";
                break;
        }
        break;

        // 🧾 Factures sociétés
    case 'company_invoices':
        switch ($action) {
            case null: listCompanyInvoices();
                break;
            case 'create': showCreateCompanyInvoiceForm();
                break;
            case 'store': storeCompanyInvoice();
                break;
            case 'show': showCompanyInvoice($id);
                break;
            case 'delete': deleteCompanyInvoice($id);
                break;
            case 'variants': fetchCompanyVariants($_GET['company_id'] ?? null);
                break;
            case 'pay': storeCompanyPayment();
                break;
            default: echo "404 - Action facture société inconnue.";
                break;
        }
        break;

        // 💸 Charges partenaires
    case 'partner_expenses':
        switch ($action) {
            case null: listPartnerExpenses();
                break;
            case 'store': storePartnerExpense();
                break;
            default: echo "404 - Action charge inconnue.";
                break;
        }
        break;

        // 📊 Rapports
    case 'reports':
        switch ($action) {
            case 'partners': partnerReport();
                break;
            case 'company_stock': companyStockReport();
                break;
            case 'company_dashboard': companyDashboard();
                break;
            default: echo "404 - Action rapport inconnue.";
                break;
        }
        break;

        // 💰 Paiements
    case 'payments':
        switch ($action) {
            case null: listPayments();
                break;
            case 'create': showCreatePaymentForm();
                break;
            case 'store': storePayment();
                break;
            case 'fetch_orders_by_supplier':
                fetchOrdersBySupplier($_GET['supplier_id']);
                break;
            case 'delete': deletePayment($id);
                break;
            default: echo "404 - Action paiement inconnue.";
                break;
        }
        break;

        // 📦 Commandes
    case 'orders':
        switch ($action) {
            case null: listOrders();
                break;
            case 'create': showCreateOrderForm();
                break;
            case 'store': storeOrder();
                break;
            case 'variants':
                fetchVariantsByProduct($_GET['product_id'] ?? null);
                break;
            case 'edit': showEditOrderForm($id);
                break;
            case 'update': updateOrder($id);
                break;
            case 'delete': deleteOrder($id);
                break;
            case 'show': showOrder($id);
                break;
            case 'update-status':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    updateOrderStatus($id);
                }
                break;
            default: echo "404 - Action commande inconnue.";
                break;
        }
        break;

        // 🚚 Envois
    case 'shipments':
        switch ($action) {
            case null: listShipments();
                break;
            case 'create': showCreateShipmentForm();
                break;
            case 'store': storeShipment();
                break;
            case 'delete': deleteShipment($id);
                break;
            case 'show': showShipment($id);
                break;

            case 'update_status':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    updateShipmentStatus($id);      // ✅ met à jour
                } else {
                    showUpdateShipmentStatusForm($id); // ✅ affiche le formulaire
                }
                break;

            default: echo "404 - Action envoi inconnue.";
                break;
        }
        break;


        // 🧾 Ventes Client
    case 'client_sales':
        switch ($action) {
            case null: listClientSales();
                break;
            case 'create': createClientSale($_GET['country_id'] ?? null);
                break;
            case 'variants_by_company': fetchCompanySaleVariants($_GET['company_id'] ?? null);
                break;
            case 'store': storeClientSale();
                break;
            case 'edit': editClientSale($id);
                break;
            case 'update': updateClientSale($id);
                break;
            case 'delete': deleteClientSale($id);
                break;
            case 'show': showClientSale($id);
                break;
            default: echo "404 - Action vente client inconnue.";
                break;
        }
        break;

        // 🏷 Stocks
    case 'stocks':
        switch ($action) {
            case null: listRealStocks();
                break;
            case 'adjust': adjustStock();
                break;
            case 'delete-adjustment': deleteStockAdjustment();
                break;
            case 'country': showCountryStock($id);
                break;
            default: echo "404 - Action stock inconnue.";
                break;
        }
        break;

        // 🚛 Transporteurs
    case 'transports':
        switch ($action) {
            case null: listTransports();
                break;
            case 'create': createTransportForm();
                break;
            case 'store': storeTransport();
                break;
            case 'edit': editTransportForm($id);
                break;
            case 'update': updateTransport($id);
                break;
            case 'delete': deleteTransport($id);
                break;
            default: echo "404 - Action transport inconnue.";
                break;
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
