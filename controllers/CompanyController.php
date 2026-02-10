<?php

require_once 'models/Company.php';
require_once 'models/Country.php';

function listCompanies()
{
    $companies = Company::all();
    include 'views/companies/index.php';
}

function showCreateCompanyForm()
{
    $countries = Country::all();
    include 'views/companies/create.php';
}

function storeCompany()
{
    $countryId = $_POST['country_id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (!$countryId || $name === '') {
        $_SESSION['error'] = "Pays et nom obligatoires.";
        header('Location: ?route=companies/create');
        exit;
    }

    Company::create($countryId, $name, $contact, $address, $notes);
    $_SESSION['success'] = "Société créée.";
    header('Location: ?route=companies');
    exit;
}

function showEditCompanyForm($id)
{
    $company = Company::find($id);
    if (!$company) {
        $_SESSION['error'] = "Société introuvable.";
        header('Location: ?route=companies');
        exit;
    }
    $countries = Country::all();
    include 'views/companies/edit.php';
}

function updateCompany($id)
{
    $countryId = $_POST['country_id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (!$countryId || $name === '') {
        $_SESSION['error'] = "Pays et nom obligatoires.";
        header("Location: ?route=companies/edit/$id");
        exit;
    }

    Company::update($id, $countryId, $name, $contact, $address, $notes);
    $_SESSION['success'] = "Société mise à jour.";
    header('Location: ?route=companies');
    exit;
}

function deleteCompany($id)
{
    try {
        Company::delete($id);
        $_SESSION['success'] = "Société supprimée.";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    header('Location: ?route=companies');
    exit;
}

function fetchCompaniesByCountry($countryId)
{
    header('Content-Type: text/html; charset=utf-8');
    if (!$countryId) {
        echo '<option value="">-- Choisir une société --</option>';
        exit;
    }
    $companies = Company::byCountry($countryId);
    echo '<option value="">-- Choisir une société --</option>';
    foreach ($companies as $c) {
        $name = htmlspecialchars($c['name']);
        echo "<option value=\"{$c['id']}\">$name</option>";
    }
    exit;
}
