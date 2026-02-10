<?php

require_once 'models/PartnerExpense.php';
require_once 'models/Partner.php';
require_once 'models/PartnerAccount.php';
require_once 'utils.php';

function listPartnerExpenses()
{
    $expenses = PartnerExpense::all();
    $partners = Partner::all();
    $accounts = [];
    include 'views/partner_expenses/index.php';
}

function storePartnerExpense()
{
    $partnerId = $_POST['partner_id'] ?? null;
    $accountId = $_POST['account_id'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $currency = $_POST['currency'] ?? 'MAD';
    $expenseDate = $_POST['expense_date'] ?? null;
    $category = $_POST['category'] ?? '';
    $notes = $_POST['notes'] ?? '';

    if (!$partnerId || !$amount || !$expenseDate) {
        $_SESSION['error'] = "Champs obligatoires manquants.";
        header('Location: ?route=partner_expenses');
        exit;
    }

    $proofPath = null;
    if (!empty($_FILES['proof_file']['name'])) {
        validate_upload_or_throw(
            $_FILES['proof_file'],
            ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'],
            5 * 1024 * 1024
        );
        $uploadDir = 'uploads/partner_expenses/';
        ensure_upload_dir($uploadDir);
        $fileName = time() . '_' . bin2hex(random_bytes(4)) . '_' . sanitize_filename($_FILES['proof_file']['name']);
        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['proof_file']['tmp_name'], $targetPath)) {
            $proofPath = $targetPath;
        }
    }

    PartnerExpense::create($partnerId, $accountId, $amount, $currency, $expenseDate, $category, $notes, $proofPath);
    $_SESSION['success'] = "Charge enregistrée.";
    header('Location: ?route=partner_expenses');
    exit;
}
