<?php

class ClientSaleItem
{
    public static function create($saleId, $variantId, $quantity)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO client_sale_items (sale_id, variant_id, quantity_sold)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$saleId, $variantId, $quantity]);
    }

    public static function getItemsWithDetails($saleId)
    {
        global $pdo;
        $stmt = $pdo->prepare("
	        SELECT csi.*, v.size, v.color, v.sku, p.name AS product_name
	        FROM client_sale_items csi
	        JOIN variants v ON csi.variant_id = v.id
	        JOIN products p ON v.product_id = p.id
	        WHERE csi.sale_id = ?
	    ");
        $stmt->execute([$saleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function deleteBySaleId($saleId)
    {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM client_sale_items WHERE sale_id = ?");
        $stmt->execute([$saleId]);
    }


}
