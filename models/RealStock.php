<?php


// models/RealStock.php
class RealStock
{
    // Get all stocks (supports both country and transport views)
    public static function getAll()
    {
        global $pdo;
        
        // Try transport_stock_view first, fallback to real_stock_view
        $viewExists = self::checkViewExists('transport_stock_view');
        
        if ($viewExists) {
            $stmt = $pdo->query("
                SELECT ts.*, t.name AS transport_name, t.transport_type
                FROM transport_stock_view ts
                JOIN transports t ON t.id = ts.transport_id
                ORDER BY transport_name, product_name, size, color
            ");
        } else {
            // Fallback to country-based view
            $stmt = $pdo->query("
                SELECT rs.*, c.name AS country_name, c.flag
                FROM real_stock_view rs
                JOIN countries c ON c.id = rs.country_id
                ORDER BY country_name, product_name, size, color
            ");
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get available variants by transport
    public static function getAvailableVariantsByTransport($transportId)
    {
        global $pdo;
        
        $viewExists = self::checkViewExists('transport_stock_view');
        
        if ($viewExists) {
            $stmt = $pdo->prepare("
                SELECT ts.variant_id, ts.current_stock, v.size, v.color, v.sku, p.name AS product_name
                FROM transport_stock_view ts
                JOIN variants v ON ts.variant_id = v.id
                JOIN products p ON v.product_id = p.id
                WHERE ts.transport_id = ? AND ts.current_stock > 0
                ORDER BY p.name, v.size, v.color
            ");
            $stmt->execute([$transportId]);
        } else {
            // Return empty array if transport view doesn't exist
            return [];
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get stock by transport
    public static function getByTransport($transportId)
    {
        global $pdo;
        
        $viewExists = self::checkViewExists('transport_stock_view');
        
        if ($viewExists) {
            $stmt = $pdo->prepare("
                SELECT ts.*, t.name AS transport_name, t.transport_type
                FROM transport_stock_view ts
                JOIN transports t ON t.id = ts.transport_id
                WHERE ts.transport_id = ?
                ORDER BY product_name, size, color
            ");
            $stmt->execute([$transportId]);
        } else {
            return [];
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Legacy method - Get available variants by country (for backward compatibility)
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

    // Legacy method - Get stock by country (for backward compatibility)
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

    // Helper method to check if a view exists
    private static function checkViewExists($viewName)
    {
        global $pdo;
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM information_schema.views 
                WHERE table_schema = DATABASE() 
                AND table_name = ?
            ");
            $stmt->execute([$viewName]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

}
