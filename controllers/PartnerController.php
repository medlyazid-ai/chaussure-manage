<?php

require_once 'models/Partner.php';
require_once 'models/PartnerAccount.php';
require_once 'models/PartnerExpense.php';
require_once 'models/CompanyPayment.php';
require_once 'models/Payment.php';
require_once 'models/CompanyInvoice.php';
require_once 'models/Supplier.php';

function listPartners()
{
    $partners = Partner::all();
    include 'views/partners/index.php';
}

function showCreatePartnerForm()
{
    include 'views/partners/create.php';
}

function storePartner()
{
    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        $_SESSION['error'] = "Nom obligatoire.";
        header('Location: ?route=partners/create');
        exit;
    }
    Partner::create($name);
    $_SESSION['success'] = "Partenaire ajouté.";
    header('Location: ?route=partners');
    exit;
}

function showEditPartnerForm($id)
{
    $partner = Partner::find($id);
    if (!$partner) {
        $_SESSION['error'] = "Partenaire introuvable.";
        header('Location: ?route=partners');
        exit;
    }
    include 'views/partners/edit.php';
}

function updatePartner($id)
{
    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        $_SESSION['error'] = "Nom obligatoire.";
        header("Location: ?route=partners/edit/$id");
        exit;
    }
    Partner::update($id, $name);
    $_SESSION['success'] = "Partenaire mis à jour.";
    header('Location: ?route=partners');
    exit;
}

function deletePartner($id)
{
    Partner::delete($id);
    $_SESSION['success'] = "Partenaire supprimé.";
    header('Location: ?route=partners');
    exit;
}

function partnerDashboard($id)
{
    global $pdo;
    if (!$id) {
        $_SESSION['error'] = "ID partenaire manquant.";
        header('Location: ?route=partners');
        exit;
    }

    $partner = Partner::find($id);
    if (!$partner) {
        $_SESSION['error'] = "Partenaire introuvable.";
        header('Location: ?route=partners');
        exit;
    }

    $dateFrom = $_GET['date_from'] ?? null;
    $dateTo = $_GET['date_to'] ?? null;

    // Encaissements sociétés
    $params = [$id];
    $where = '';
    if ($dateFrom) { $where .= " AND cp.payment_date >= ?"; $params[] = $dateFrom; }
    if ($dateTo) { $where .= " AND cp.payment_date <= ?"; $params[] = $dateTo; }
    $stmt = $pdo->prepare("
        SELECT cp.*, cc.name AS company_name, ci.id AS invoice_id, pa.account_label
        FROM company_payments cp
        JOIN company_invoices ci ON ci.id = cp.invoice_id
        JOIN country_companies cc ON cc.id = ci.company_id
        LEFT JOIN partner_accounts pa ON pa.id = cp.account_id
        WHERE cp.partner_id = ? $where
        ORDER BY cp.payment_date DESC
    ");
    $stmt->execute($params);
    $companyPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Paiements fournisseurs (charges)
    $params = [$id];
    $where = '';
    if ($dateFrom) { $where .= " AND p.payment_date >= ?"; $params[] = $dateFrom; }
    if ($dateTo) { $where .= " AND p.payment_date <= ?"; $params[] = $dateTo; }
    $stmt = $pdo->prepare("
        SELECT p.*, s.name AS supplier_name
        FROM payments p
        JOIN suppliers s ON s.id = p.supplier_id
        WHERE p.partner_id = ? $where
        ORDER BY p.payment_date DESC
    ");
    $stmt->execute($params);
    $supplierPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Charges internes
    $params = [$id];
    $where = '';
    if ($dateFrom) { $where .= " AND pe.expense_date >= ?"; $params[] = $dateFrom; }
    if ($dateTo) { $where .= " AND pe.expense_date <= ?"; $params[] = $dateTo; }
    $stmt = $pdo->prepare("
        SELECT pe.*, pa.account_label
        FROM partner_expenses pe
        LEFT JOIN partner_accounts pa ON pa.id = pe.account_id
        WHERE pe.partner_id = ? $where
        ORDER BY pe.expense_date DESC
    ");
    $stmt->execute($params);
    $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Totaux par devise
    $totals = [
        'received' => ['MAD' => 0, 'USD' => 0],
        'charges' => ['MAD' => 0, 'USD' => 0],
        'supplier_payments' => ['MAD' => 0, 'USD' => 0],
        'expenses' => ['MAD' => 0, 'USD' => 0],
    ];
    foreach ($companyPayments as $cp) {
        $cur = $cp['currency'] ?? 'MAD';
        if (!isset($totals['received'][$cur])) $totals['received'][$cur] = 0;
        $totals['received'][$cur] += (float)$cp['amount'];
    }
    foreach ($supplierPayments as $sp) {
        $cur = $sp['currency'] ?? 'MAD';
        if (!isset($totals['supplier_payments'][$cur])) $totals['supplier_payments'][$cur] = 0;
        $totals['supplier_payments'][$cur] += (float)$sp['amount'];
    }
    foreach ($expenses as $ex) {
        $cur = $ex['currency'] ?? 'MAD';
        if (!isset($totals['expenses'][$cur])) $totals['expenses'][$cur] = 0;
        $totals['expenses'][$cur] += (float)$ex['amount'];
    }
    foreach (['MAD','USD'] as $cur) {
        $totals['charges'][$cur] =
            ($totals['supplier_payments'][$cur] ?? 0) + ($totals['expenses'][$cur] ?? 0);
    }

    include 'views/partners/dashboard.php';
}
