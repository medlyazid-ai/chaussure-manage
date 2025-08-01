<?php

class StockAdjustment {
    public static function getAll() {
        global $pdo;
        $stmt = $pdo->query("SELECT * FROM stock_adjustments");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function adjust($countryId, $variantId, $quantity, $reason) {
        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO stock_adjustments (country_id, variant_id, adjusted_quantity, reason)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$countryId, $variantId, $quantity, $reason]);
    }

    public static function getAdjustment($countryId, $variantId) {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT SUM(adjusted_quantity) AS total_adjustment
            FROM stock_adjustments
            WHERE country_id = ? AND variant_id = ?
        ");
        $stmt->execute([$countryId, $variantId]);
        return $stmt->fetchColumn() ?? 0;
    }
}
