<?php

class Stock
{
    // 📌 Ajouter ou mettre à jour le stock pour une variante dans un pays
    public static function addOrUpdateStock($countryId, $variantId, $quantityToAdd)
    {
        global $pdo;

        $stmt = $pdo->prepare("SELECT id, quantity FROM country_stocks WHERE country_id = ? AND variant_id = ?");
        $stmt->execute([$countryId, $variantId]);
        $existing = $stmt->fetch();

        if ($existing) {
            $newQuantity = $existing['quantity'] + $quantityToAdd;
            $updateStmt = $pdo->prepare("UPDATE country_stocks SET quantity = ?, updated_at = NOW() WHERE id = ?");
            $updateStmt->execute([$newQuantity, $existing['id']]);
        } else {
            $insertStmt = $pdo->prepare("INSERT INTO country_stocks (country_id, variant_id, quantity, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
            $insertStmt->execute([$countryId, $variantId, $quantityToAdd]);
        }

    }

    public static function getAdjustments($countryId, $variantId)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM stock_adjustments WHERE country_id = ? AND variant_id = ? ORDER BY created_at DESC");
        $stmt->execute([$countryId, $variantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }




}
