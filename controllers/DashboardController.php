<?php

function showDashboard()
{
    global $pdo;

    $range = $_GET['range'] ?? '30';
    $allowed = ['7', '30', '90', '365', 'all'];
    if (!in_array($range, $allowed, true)) {
        $range = '30';
    }

    $dateFrom = null;
    if ($range !== 'all') {
        $dateFrom = date('Y-m-d', strtotime("-$range days"));
    }

    // ✅ Totaux commandes
    $ordersTotalSql = "
        SELECT COUNT(DISTINCT o.id) AS orders_count,
               COALESCE(SUM(oi.unit_price * oi.quantity_ordered), 0) AS orders_amount
        FROM orders o
        JOIN order_items oi ON oi.order_id = o.id
    ";
    $params = [];
    if ($dateFrom) {
        $ordersTotalSql .= " WHERE DATE(o.created_at) >= ?";
        $params[] = $dateFrom;
    }
    $stmt = $pdo->prepare($ordersTotalSql);
    $stmt->execute($params);
    $ordersTotals = $stmt->fetch(PDO::FETCH_ASSOC);

    // ✅ Paiements (montant)
    $paymentsSql = "SELECT COALESCE(SUM(amount), 0) AS payments_amount FROM payments";
    $params = [];
    if ($dateFrom) {
        $paymentsSql .= " WHERE DATE(payment_date) >= ?";
        $params[] = $dateFrom;
    }
    $stmt = $pdo->prepare($paymentsSql);
    $stmt->execute($params);
    $paymentsTotals = $stmt->fetch(PDO::FETCH_ASSOC);

    // ✅ Allocations (montant payé réel)
    $allocSql = "
        SELECT COALESCE(SUM(pa.amount_allocated), 0) AS allocations_amount
        FROM payment_allocations pa
        JOIN payments p ON p.id = pa.payment_id
    ";
    $params = [];
    if ($dateFrom) {
        $allocSql .= " WHERE DATE(p.payment_date) >= ?";
        $params[] = $dateFrom;
    }
    $stmt = $pdo->prepare($allocSql);
    $stmt->execute($params);
    $allocTotals = $stmt->fetch(PDO::FETCH_ASSOC);

    // ✅ Envois
    $shipmentsSql = "
        SELECT
            SUM(CASE WHEN status = 'Livré à destination' THEN 1 ELSE 0 END) AS delivered,
            SUM(CASE WHEN status != 'Livré à destination' THEN 1 ELSE 0 END) AS pending
        FROM shipments
    ";
    $params = [];
    if ($dateFrom) {
        $shipmentsSql .= " WHERE DATE(shipment_date) >= ?";
        $params[] = $dateFrom;
    }
    $stmt = $pdo->prepare($shipmentsSql);
    $stmt->execute($params);
    $shipmentsTotals = $stmt->fetch(PDO::FETCH_ASSOC);

    // ✅ Ventes client
    $salesSql = "SELECT COUNT(*) AS sales_count FROM client_sales";
    $params = [];
    if ($dateFrom) {
        $salesSql .= " WHERE DATE(sale_date) >= ?";
        $params[] = $dateFrom;
    }
    $stmt = $pdo->prepare($salesSql);
    $stmt->execute($params);
    $salesTotals = $stmt->fetch(PDO::FETCH_ASSOC);

    $ordersAmount = (float)($ordersTotals['orders_amount'] ?? 0);
    $allocAmount = (float)($allocTotals['allocations_amount'] ?? 0);
    $unpaid = max($ordersAmount - $allocAmount, 0);

    $stats = [
        'orders_count' => (int)($ordersTotals['orders_count'] ?? 0),
        'orders_amount' => $ordersAmount,
        'payments_amount' => (float)($paymentsTotals['payments_amount'] ?? 0),
        'allocations_amount' => $allocAmount,
        'unpaid_amount' => $unpaid,
        'shipments_delivered' => (int)($shipmentsTotals['delivered'] ?? 0),
        'shipments_pending' => (int)($shipmentsTotals['pending'] ?? 0),
        'sales_count' => (int)($salesTotals['sales_count'] ?? 0),
    ];

    // ✅ Dernières activités
    $recentOrdersSql = "
        SELECT o.id, o.created_at, o.status, s.name AS supplier_name, c.name AS country_name,
               COALESCE(SUM(oi.unit_price * oi.quantity_ordered), 0) AS total_amount
        FROM orders o
        JOIN suppliers s ON o.supplier_id = s.id
        JOIN countries c ON o.country_id = c.id
        JOIN order_items oi ON oi.order_id = o.id
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT 5
    ";
    $recentOrders = $pdo->query($recentOrdersSql)->fetchAll(PDO::FETCH_ASSOC);

    $recentPaymentsSql = "
        SELECT p.id, p.payment_date, p.amount, p.payment_method, s.name AS supplier_name
        FROM payments p
        JOIN suppliers s ON p.supplier_id = s.id
        ORDER BY p.payment_date DESC
        LIMIT 5
    ";
    $recentPayments = $pdo->query($recentPaymentsSql)->fetchAll(PDO::FETCH_ASSOC);

    $recentShipmentsSql = "
        SELECT s.id, s.shipment_date, s.status, o.id AS order_id, sup.name AS supplier_name
        FROM shipments s
        JOIN orders o ON o.id = s.order_id
        JOIN suppliers sup ON sup.id = o.supplier_id
        ORDER BY s.shipment_date DESC
        LIMIT 5
    ";
    $recentShipments = $pdo->query($recentShipmentsSql)->fetchAll(PDO::FETCH_ASSOC);

    include 'views/dashboard/index.php';
}
