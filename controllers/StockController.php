<?php

// controllers/StockController.php

require_once 'models/RealStock.php';
require_once 'models/StockAdjustment.php';

function listRealStocks()
{
    $stocks = RealStock::getAll();

    $groupedStocks = [];

    foreach ($stocks as $stock) {
        $country = $stock['country_name'];
        $product = $stock['product_name'];
        $variantKey = "{$stock['size']}|{$stock['color']}";

        $groupedStocks[$country]['flag'] = $stock['flag'];
        $groupedStocks[$country]['products'][$product]['variants'][$variantKey] = $stock;

        // Ajoute aussi les ajustements
        $stock['adjustments'] = StockAdjustment::getByCountryAndVariant($stock['country_id'], $stock['variant_id']);
        $groupedStocks[$country]['products'][$product]['variants'][$variantKey]['adjustments'] = $stock['adjustments'];
    }

    include 'views/stocks/index.php';
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
