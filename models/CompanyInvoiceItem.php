<?php

class CompanyInvoiceItem
{
    public static function create($invoiceId, $variantId, $quantitySold, $unitPrice = null)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO company_invoice_items (invoice_id, variant_id, quantity_sold, unit_price)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$invoiceId, $variantId, $quantitySold, $unitPrice]);
    }

    public static function getItemsWithDetails($invoiceId)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT cii.*, v.size, v.color, v.sku, p.name AS product_name
            FROM company_invoice_items cii
            JOIN variants v ON cii.variant_id = v.id
            JOIN products p ON v.product_id = p.id
            WHERE cii.invoice_id = ?
        ");
        $stmt->execute([$invoiceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
