<?php

class CompanyStockAdjustment
{
    public static function adjust($companyId, $variantId, $quantity, $reason)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO company_stock_adjustments (company_id, variant_id, adjusted_quantity, reason)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$companyId, $variantId, $quantity, $reason]);
    }

    public static function getByCompanyAndVariant($companyId, $variantId)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT * FROM company_stock_adjustments
            WHERE company_id = ? AND variant_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$companyId, $variantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function delete($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM company_stock_adjustments WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
