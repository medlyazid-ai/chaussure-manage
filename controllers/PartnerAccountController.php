<?php

require_once 'models/PartnerAccount.php';
require_once 'models/Partner.php';

function listPartnerAccounts()
{
    $accounts = PartnerAccount::all();
    $partners = Partner::all();
    include 'views/accounts/index.php';
}

function showCreatePartnerAccountForm()
{
    $partners = Partner::all();
    include 'views/accounts/create.php';
}

function storePartnerAccount()
{
    $partnerId = $_POST['partner_id'] ?? null;
    $bankName = trim($_POST['bank_name'] ?? '');
    $accountLabel = trim($_POST['account_label'] ?? '');
    $accountNumber = trim($_POST['account_number'] ?? '');

    if (!$partnerId || $accountLabel === '') {
        $_SESSION['error'] = "Partenaire et libellé obligatoires.";
        header('Location: ?route=accounts/create');
        exit;
    }

    PartnerAccount::create($partnerId, $bankName, $accountLabel, $accountNumber);
    $_SESSION['success'] = "Compte ajouté.";
    header('Location: ?route=accounts');
    exit;
}

function showEditPartnerAccountForm($id)
{
    $account = PartnerAccount::find($id);
    if (!$account) {
        $_SESSION['error'] = "Compte introuvable.";
        header('Location: ?route=accounts');
        exit;
    }
    $partners = Partner::all();
    include 'views/accounts/edit.php';
}

function updatePartnerAccount($id)
{
    $partnerId = $_POST['partner_id'] ?? null;
    $bankName = trim($_POST['bank_name'] ?? '');
    $accountLabel = trim($_POST['account_label'] ?? '');
    $accountNumber = trim($_POST['account_number'] ?? '');

    if (!$partnerId || $accountLabel === '') {
        $_SESSION['error'] = "Partenaire et libellé obligatoires.";
        header("Location: ?route=accounts/edit/$id");
        exit;
    }

    PartnerAccount::update($id, $partnerId, $bankName, $accountLabel, $accountNumber);
    $_SESSION['success'] = "Compte mis à jour.";
    header('Location: ?route=accounts');
    exit;
}

function deletePartnerAccount($id)
{
    PartnerAccount::delete($id);
    $_SESSION['success'] = "Compte supprimé.";
    header('Location: ?route=accounts');
    exit;
}

function fetchAccountsByPartner($partnerId)
{
    header('Content-Type: text/html; charset=utf-8');
    if (!$partnerId) {
        echo '<option value="">-- Choisir un compte --</option>';
        exit;
    }
    $accounts = PartnerAccount::byPartner($partnerId);
    echo '<option value="">-- Choisir un compte --</option>';
    foreach ($accounts as $a) {
        $label = htmlspecialchars($a['account_label']);
        echo "<option value=\"{$a['id']}\">$label</option>";
    }
    exit;
}
