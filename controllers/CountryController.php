<?php

require_once 'models/Country.php';

function listCountries()
{
    $countries = Country::all();
    include 'views/countries/index.php';
}

function showCreateCountryForm()
{
    include 'views/countries/create.php';
}

function storeCountry()
{
    $name = trim($_POST['name'] ?? '');
    $flag = trim($_POST['flag'] ?? '');
    $code = trim($_POST['code'] ?? '');

    if ($name === '') {
        $_SESSION['error'] = "Nom du pays obligatoire.";
        header('Location: ?route=countries/create');
        exit;
    }

    Country::create($name, $flag, $code);
    $_SESSION['success'] = "Pays créé.";
    header('Location: ?route=countries');
    exit;
}

function showEditCountryForm($id)
{
    $country = Country::getById($id);
    if (!$country) {
        $_SESSION['error'] = "Pays introuvable.";
        header('Location: ?route=countries');
        exit;
    }
    include 'views/countries/edit.php';
}

function updateCountry($id)
{
    $name = trim($_POST['name'] ?? '');
    $flag = trim($_POST['flag'] ?? '');
    $code = trim($_POST['code'] ?? '');

    if ($name === '') {
        $_SESSION['error'] = "Nom du pays obligatoire.";
        header("Location: ?route=countries/edit/$id");
        exit;
    }

    Country::update($id, $name, $flag, $code);
    $_SESSION['success'] = "Pays mis à jour.";
    header('Location: ?route=countries');
    exit;
}

function deleteCountry($id)
{
    try {
        Country::delete($id);
        $_SESSION['success'] = "Pays supprimé.";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    header('Location: ?route=countries');
    exit;
}
