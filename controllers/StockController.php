<?php

require_once 'models/RealStock.php';
require_once 'models/StockAdjustment.php';

function listRealStocks()
{
    $stocks = RealStock::getAll();
    include 'views/stocks/index.php';
}


function adjustStock() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $countryId = $_POST['country_id'] ?? null;
        $variantId = $_POST['variant_id'] ?? null;
        $quantity = $_POST['adjusted_quantity'] ?? null;
        $reason = $_POST['reason'] ?? null;

        if ($countryId && $variantId && is_numeric($quantity) && $reason) {
            StockAdjustment::adjust($countryId, $variantId, (int)$quantity, $reason);
            $_SESSION['success'] = "Ajustement de stock enregistré avec succès.";
        } else {
            $_SESSION['error'] = "Données manquantes pour l'ajustement.";
        }
    }

    header("Location: ?route=stocks");
    exit;
}