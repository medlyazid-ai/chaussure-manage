<?php

require_once 'models/CompanyInvoice.php';
require_once 'models/CompanyInvoiceItem.php';
require_once 'models/CompanyPayment.php';
require_once 'models/Company.php';
require_once 'models/CompanyStock.php';
require_once 'models/Partner.php';
require_once 'models/PartnerAccount.php';
require_once 'utils.php';

function listCompanyInvoices()
{
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 20;
    $offset = ($page - 1) * $perPage;
    $total = CompanyInvoice::countAll();
    $totalPages = (int)ceil($total / $perPage);

    $invoices = CompanyInvoice::allWithCompany($perPage, $offset);
    include 'views/company_invoices/index.php';
}

function showCreateCompanyInvoiceForm()
{
    $companies = Company::all();
    $selectedCompanyId = $_GET['company_id'] ?? null;
    include 'views/company_invoices/create.php';
}

function storeCompanyInvoice()
{
    $companyId = $_POST['company_id'] ?? null;
    $invoiceDate = $_POST['invoice_date'] ?? null;
    $amountDue = $_POST['amount_due'] ?? null;
    $notes = $_POST['notes'] ?? '';
    $items = $_POST['items'] ?? [];

    if (!$companyId || !$invoiceDate || $amountDue === null) {
        $_SESSION['error'] = "Champs obligatoires manquants.";
        header('Location: ?route=company_invoices/create');
        exit;
    }

    $hasQty = false;
    foreach ($items as $row) {
        $qty = (int)($row['quantity_sold'] ?? 0);
        if ($qty > 0) {
            $hasQty = true;
            break;
        }
    }
    if (!$hasQty) {
        $_SESSION['error'] = "Veuillez saisir au moins une quantité vendue.";
        header('Location: ?route=company_invoices/create&company_id=' . (int)$companyId);
        exit;
    }

    $proofPath = null;
    if (!empty($_FILES['proof_file']['name'])) {
        validate_upload_or_throw(
            $_FILES['proof_file'],
            ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'],
            5 * 1024 * 1024
        );
        $uploadDir = 'uploads/company_invoices/';
        ensure_upload_dir($uploadDir);
        $fileName = time() . '_' . bin2hex(random_bytes(4)) . '_' . sanitize_filename($_FILES['proof_file']['name']);
        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['proof_file']['tmp_name'], $targetPath)) {
            $proofPath = $targetPath;
        }
    }

    $pdo = Database::getInstance();
    try {
        $pdo->beginTransaction();
        $invoiceId = CompanyInvoice::create($companyId, $invoiceDate, $amountDue, $notes, $proofPath);
        foreach ($items as $variantId => $row) {
            $qty = (int)($row['quantity_sold'] ?? 0);
            if ($qty <= 0) {
                continue;
            }
            CompanyInvoiceItem::create($invoiceId, $variantId, $qty, $row['unit_price'] ?? null);
        }
        $pdo->commit();
        $_SESSION['success'] = "Facture société enregistrée.";
        header("Location: ?route=company_invoices/show/$invoiceId");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Erreur lors de l'enregistrement : " . $e->getMessage();
        header('Location: ?route=company_invoices/create');
        exit;
    }
}

function showCompanyInvoice($id)
{
    $invoice = CompanyInvoice::findWithCompany($id);
    if (!$invoice) {
        $_SESSION['error'] = "Facture introuvable.";
        header('Location: ?route=company_invoices');
        exit;
    }
    $items = CompanyInvoiceItem::getItemsWithDetails($id);
    $payments = CompanyPayment::byInvoice($id);
    $partners = Partner::all();
    $accounts = [];
    include 'views/company_invoices/show.php';
}

function deleteCompanyInvoice($id)
{
    CompanyInvoice::delete($id);
    $_SESSION['success'] = "Facture supprimée.";
    header('Location: ?route=company_invoices');
    exit;
}

function fetchCompanyVariants($companyId)
{
    header('Content-Type: text/html; charset=utf-8');
    $variants = [];
    if ($companyId) {
        $variants = CompanyStock::getAvailableVariantsByCompany($companyId);
    }
    include 'views/company_invoices/_items_rows.php';
    exit;
}

function storeCompanyPayment()
{
    $invoiceId = $_POST['invoice_id'] ?? null;
    $partnerId = $_POST['partner_id'] ?? null;
    $accountId = $_POST['account_id'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $currency = $_POST['currency'] ?? 'MAD';
    $paymentDate = $_POST['payment_date'] ?? null;
    $method = $_POST['method'] ?? null;
    $notes = $_POST['notes'] ?? '';

    if (!$invoiceId || !$partnerId || !$amount || !$paymentDate || !$method) {
        $_SESSION['error'] = "Champs obligatoires manquants.";
        header("Location: ?route=company_invoices/show/$invoiceId");
        exit;
    }

    $proofPath = null;
    if (!empty($_FILES['proof_file']['name'])) {
        validate_upload_or_throw(
            $_FILES['proof_file'],
            ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'],
            5 * 1024 * 1024
        );
        $uploadDir = 'uploads/company_payments/';
        ensure_upload_dir($uploadDir);
        $fileName = time() . '_' . bin2hex(random_bytes(4)) . '_' . sanitize_filename($_FILES['proof_file']['name']);
        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['proof_file']['tmp_name'], $targetPath)) {
            $proofPath = $targetPath;
        }
    }

    CompanyPayment::create($invoiceId, $partnerId, $accountId, $amount, $currency, $paymentDate, $method, $notes, $proofPath);
    $_SESSION['success'] = "Encaissement enregistré.";
    header("Location: ?route=company_invoices/show/$invoiceId");
    exit;
}
