<?php

// controllers/StockController.php

require_once 'models/RealStock.php';
require_once 'models/StockAdjustment.php';

function listRealStocks()
{
    $stocks = RealStock::getAll();

    // 🧠 Regrouper par pays
    $grouped = [];
    foreach ($stocks as $stock) {
        $cid = $stock['country_id'];
        if (!isset($grouped[$cid])) {
            $grouped[$cid] = [
                'country_id' => $cid,
                'country_name' => $stock['country_name'],
                'country_flag' => $stock['flag'],
                'total_received' => 0,
                'total_sold' => 0,
                'manual_adjustment' => 0,
            ];
        }
        $grouped[$cid]['total_received'] += $stock['total_received'];
        $grouped[$cid]['total_sold'] += $stock['total_sold'];
        $grouped[$cid]['manual_adjustment'] += $stock['manual_adjustment'];
    }

    $countryStocks = $grouped;
    include 'views/stocks/overview.php';
}

function showCountryStock($countryId)
{
    $stocks = RealStock::getByCountry($countryId);
    foreach ($stocks as &$stock) {
        $stock['adjustments'] = StockAdjustment::getByCountryAndVariant($countryId, $stock['variant_id']);
    }
    include 'views/stocks/country.php';
}

function adjustStock()
{
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

function deleteStockAdjustment()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['adjustment_id'])) {
        $deleted = StockAdjustment::delete($_POST['adjustment_id']);
        if ($deleted) {
            $_SESSION['success'] = "Ajustement supprimé.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression.";
        }
    } else {
        $_SESSION['error'] = "ID manquant pour la suppression.";
    }

    header("Location: ?route=stocks");
    exit;
}
