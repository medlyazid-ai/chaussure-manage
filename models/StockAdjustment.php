<?php

class StockAdjustment
{
    public static function getAll()
    {
        global $pdo;
        $stmt = $pdo->query("SELECT * FROM stock_adjustments");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Legacy method - adjust by country
    public static function adjust($countryId, $variantId, $quantity, $reason)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO stock_adjustments (country_id, variant_id, adjusted_quantity, reason)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$countryId, $variantId, $quantity, $reason]);
    }

    // New method - adjust by transport
    public static function adjustByTransport($transportId, $variantId, $quantity, $reason)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO stock_adjustments (transport_id, variant_id, adjusted_quantity, reason)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$transportId, $variantId, $quantity, $reason]);
    }

    public static function getAdjustment($countryId, $variantId)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT SUM(adjusted_quantity) AS total_adjustment
            FROM stock_adjustments
            WHERE country_id = ? AND variant_id = ?
        ");
        $stmt->execute([$countryId, $variantId]);
        return $stmt->fetchColumn() ?? 0;
    }

    public static function getByCountryAndVariant($countryId, $variantId)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM stock_adjustments WHERE country_id = ? AND variant_id = ? ORDER BY created_at DESC");
        $stmt->execute([$countryId, $variantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getByTransportAndVariant($transportId, $variantId)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM stock_adjustments WHERE transport_id = ? AND variant_id = ? ORDER BY created_at DESC");
        $stmt->execute([$transportId, $variantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function delete($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM stock_adjustments WHERE id = ?");
        return $stmt->execute([$id]);
    }


}
