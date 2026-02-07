<?php

// controllers/StockController.php

require_once 'models/RealStock.php';
require_once 'models/StockAdjustment.php';
require_once 'models/Transport.php';

function listRealStocks()
{
    $stocks = RealStock::getAll();

    // 🧠 Detect if we're using transport or country based system
    $useTransport = !empty($stocks) && isset($stocks[0]['transport_id']);

    if ($useTransport) {
        // Group by transport
        $grouped = [];
        foreach ($stocks as $stock) {
            $tid = $stock['transport_id'];
            if (!isset($grouped[$tid])) {
                $grouped[$tid] = [
                    'transport_id' => $tid,
                    'transport_name' => $stock['transport_name'],
                    'transport_type' => $stock['transport_type'],
                    'total_received' => 0,
                    'total_sold' => 0,
                    'manual_adjustment' => 0,
                ];
            }
            $grouped[$tid]['total_received'] += $stock['total_received'];
            $grouped[$tid]['total_sold'] += $stock['total_sold'];
            $grouped[$tid]['manual_adjustment'] += $stock['manual_adjustment'];
        }
        $transportStocks = $grouped;
        include 'views/stocks/overview_transport.php';
    } else {
        // Legacy: Group by country
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

// New function for transport-based stock view
function showTransportStock($transportId)
{
    // 1) Fetch rows safely
    $rows = RealStock::getByTransport($transportId);
    if (!is_array($rows)) {
        $rows = [];
    }

    // 2) Attach adjustments to each row
    foreach ($rows as $i => $r) {
        $rows[$i]['adjustments'] = StockAdjustment::getByTransportAndVariant(
            (int)$transportId,
            (int)$r['variant_id']
        );
    }
    // Prevent PHP reference side-effects
    unset($r);

    // 3) Provide the vars your view expects
    $stocks = array_values($rows); // numeric indexing for foreach()
    $transport = [
        'transport_id'   => (int)$transportId,
        'transport_name' => $rows[0]['transport_name'] ?? '',
        'transport_type' => $rows[0]['transport_type'] ?? '',
    ];

    include 'views/stocks/transport.php';
}


function adjustStock()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $countryId = $_POST['country_id'] ?? null;
        $transportId = $_POST['transport_id'] ?? null;
        $variantId = $_POST['variant_id'] ?? null;
        $quantity = $_POST['adjusted_quantity'] ?? null;
        $reason = $_POST['reason'] ?? null;

        if ($variantId && is_numeric($quantity) && $reason) {
            if ($transportId) {
                // Transport-based adjustment
                StockAdjustment::adjustByTransport($transportId, $variantId, (int)$quantity, $reason);
            } elseif ($countryId) {
                // Legacy country-based adjustment
                StockAdjustment::adjust($countryId, $variantId, (int)$quantity, $reason);
            } else {
                $_SESSION['error'] = "Transport ou pays requis.";
                header("Location: ?route=stocks");
                exit;
            }
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
