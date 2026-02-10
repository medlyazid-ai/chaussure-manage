<?php

require_once 'models/Company.php';

function partnerReport()
{
    global $pdo;
    $dateFrom = $_GET['date_from'] ?? null;
    $dateTo = $_GET['date_to'] ?? null;

    $partners = $pdo->query("SELECT id, name FROM partners ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $map = [];
    foreach ($partners as $p) {
        $map[$p['id']] = [
            'id' => $p['id'],
            'name' => $p['name'],
            'received' => ['MAD' => 0, 'USD' => 0],
            'charges' => ['MAD' => 0, 'USD' => 0],
            'supplier_payments' => ['MAD' => 0, 'USD' => 0],
            'expenses' => ['MAD' => 0, 'USD' => 0],
        ];
    }

    // Encaissements sociétés
    $params = [];
    $where = '';
    if ($dateFrom) { $where .= " AND cp.payment_date >= ?"; $params[] = $dateFrom; }
    if ($dateTo) { $where .= " AND cp.payment_date <= ?"; $params[] = $dateTo; }
    $stmt = $pdo->prepare("
        SELECT cp.partner_id, cp.currency, SUM(cp.amount) AS total_amount
        FROM company_payments cp
        WHERE cp.partner_id IS NOT NULL $where
        GROUP BY cp.partner_id, cp.currency
    ");
    $stmt->execute($params);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $cur = $r['currency'] ?? 'MAD';
        if (isset($map[$r['partner_id']])) {
            $map[$r['partner_id']]['received'][$cur] = (float)$r['total_amount'];
        }
    }

    // Paiements fournisseurs (charges)
    $params = [];
    $where = '';
    if ($dateFrom) { $where .= " AND p.payment_date >= ?"; $params[] = $dateFrom; }
    if ($dateTo) { $where .= " AND p.payment_date <= ?"; $params[] = $dateTo; }
    $stmt = $pdo->prepare("
        SELECT p.partner_id, p.currency, SUM(p.amount) AS total_amount
        FROM payments p
        WHERE p.partner_id IS NOT NULL $where
        GROUP BY p.partner_id, p.currency
    ");
    $stmt->execute($params);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $cur = $r['currency'] ?? 'MAD';
        if (isset($map[$r['partner_id']])) {
            $map[$r['partner_id']]['supplier_payments'][$cur] = (float)$r['total_amount'];
        }
    }

    // Charges internes
    $params = [];
    $where = '';
    if ($dateFrom) { $where .= " AND pe.expense_date >= ?"; $params[] = $dateFrom; }
    if ($dateTo) { $where .= " AND pe.expense_date <= ?"; $params[] = $dateTo; }
    $stmt = $pdo->prepare("
        SELECT pe.partner_id, pe.currency, SUM(pe.amount) AS total_amount
        FROM partner_expenses pe
        WHERE pe.partner_id IS NOT NULL $where
        GROUP BY pe.partner_id, pe.currency
    ");
    $stmt->execute($params);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $cur = $r['currency'] ?? 'MAD';
        if (isset($map[$r['partner_id']])) {
            $map[$r['partner_id']]['expenses'][$cur] = (float)$r['total_amount'];
        }
    }

    foreach ($map as $id => $row) {
        foreach (['MAD','USD'] as $cur) {
            $map[$id]['charges'][$cur] =
                ($row['supplier_payments'][$cur] ?? 0) + ($row['expenses'][$cur] ?? 0);
        }
    }

    $rows = array_values($map);
    include 'views/reports/partners.php';
}

function companyStockReport()
{
    global $pdo;
    $companyId = $_GET['company_id'] ?? null;

    $sql = "
        SELECT csv.*, cc.name AS company_name, c.name AS country_name
        FROM company_stock_view csv
        JOIN country_companies cc ON csv.company_id = cc.id
        JOIN countries c ON cc.country_id = c.id
        WHERE 1=1
    ";
    $params = [];
    if ($companyId) {
        $sql .= " AND csv.company_id = ?";
        $params[] = $companyId;
    }
    $sql .= " ORDER BY cc.name, csv.product_name, csv.size, csv.color";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $companies = Company::all();
    include 'views/reports/company_stock.php';
}

function companyDashboard()
{
    global $pdo;
    $dateFrom = $_GET['date_from'] ?? null;
    $dateTo = $_GET['date_to'] ?? null;

    $params = [];
    $where = '';
    if ($dateFrom) {
        $where .= " AND ci.invoice_date >= ?";
        $params[] = $dateFrom;
    }
    if ($dateTo) {
        $where .= " AND ci.invoice_date <= ?";
        $params[] = $dateTo;
    }

    $stmt = $pdo->prepare("
        SELECT
            cc.id,
            cc.name AS company_name,
            c.name AS country_name,
            COALESCE(SUM(ci.amount_due), 0) AS total_invoiced,
            COALESCE(SUM(cp.amount), 0) AS total_paid
        FROM country_companies cc
        JOIN countries c ON cc.country_id = c.id
        LEFT JOIN company_invoices ci ON ci.company_id = cc.id $where
        LEFT JOIN company_payments cp ON cp.invoice_id = ci.id
        GROUP BY cc.id
        ORDER BY total_invoiced DESC
    ");
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    include 'views/reports/company_dashboard.php';
}
