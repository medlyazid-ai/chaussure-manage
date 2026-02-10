<?php

class CompanyStock
{
    public static function getAll()
    {
        global $pdo;
        $stmt = $pdo->query("
            SELECT csv.*, cc.name AS company_name, c.name AS country_name, c.flag
            FROM company_stock_view csv
            JOIN country_companies cc ON csv.company_id = cc.id
            JOIN countries c ON cc.country_id = c.id
            ORDER BY cc.name, csv.product_name, csv.size, csv.color
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getByCompany($companyId)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT csv.*, cc.name AS company_name, c.name AS country_name, c.flag
            FROM company_stock_view csv
            JOIN country_companies cc ON csv.company_id = cc.id
            JOIN countries c ON cc.country_id = c.id
            WHERE csv.company_id = ?
            ORDER BY csv.product_name, csv.size, csv.color
        ");
        $stmt->execute([$companyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAvailableVariantsByCompany($companyId)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT csv.variant_id, csv.current_stock, v.size, v.color, v.sku, p.name AS product_name
            FROM company_stock_view csv
            JOIN variants v ON csv.variant_id = v.id
            JOIN products p ON v.product_id = p.id
            WHERE csv.company_id = ? AND csv.current_stock > 0
            ORDER BY p.name, v.size, v.color
        ");
        $stmt->execute([$companyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
