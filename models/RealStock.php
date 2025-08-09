<?php


// models/RealStock.php
class RealStock
{
    public static function getAll()
    {
        global $pdo;
        $stmt = $pdo->query("
            SELECT rs.*, c.name AS country_name, c.flag
            FROM real_stock_view rs
            JOIN countries c ON c.id = rs.country_id
            ORDER BY country_name, product_name, size, color
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAvailableVariantsByCountry($countryId)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT rs.variant_id, rs.current_stock, v.size, v.color, v.sku, p.name AS product_name
            FROM real_stock_view rs
            JOIN variants v ON rs.variant_id = v.id
            JOIN products p ON v.product_id = p.id
            WHERE rs.country_id = ? AND rs.current_stock > 0
            ORDER BY p.name, v.size, v.color
        ");
        $stmt->execute([$countryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public static function getByCountry($countryId)
    {
        global $pdo;
        $stmt = $pdo->prepare("
        SELECT rs.*, c.name AS country_name, c.flag
        FROM real_stock_view rs
        JOIN countries c ON c.id = rs.country_id
        WHERE rs.country_id = ?
        ORDER BY product_name, size, color
    ");
        $stmt->execute([$countryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}
