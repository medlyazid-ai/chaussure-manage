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
    // 1) Fetch rows safely
    $rows = RealStock::getByCountry($countryId);
    if (!is_array($rows)) {
        $rows = [];
    }

    // 2) Attach adjustments to each row
    foreach ($rows as $i => $r) {
        $rows[$i]['adjustments'] = StockAdjustment::getByCountryAndVariant(
            (int)$countryId,
            (int)$r['variant_id']
        );
    }
    // Prevent PHP reference side-effects
    unset($r);

    // 3) Provide the vars your view expects
    $stocks = array_values($rows); // numeric indexing for foreach()
    $country = [
        'country_id'   => (int)$countryId,
        'country_name' => $rows[0]['country_name'] ?? '',
        'country_flag' => $rows[0]['flag'] ?? '',
    ];

    // (Optional) quick sanity log — remove later
    // error_log('stocks count for country '.$countryId.': '.count($stocks));

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
