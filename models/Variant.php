<?php

class Variant
{
    public static function findByProduct($product_id)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM variants WHERE product_id = ?");
        $stmt->execute([$product_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function filter($productId = null, $search = null, $size = null, $color = null)
    {
        global $pdo;

        $sql = "
            SELECT v.*, p.name AS product_name,
                   COALESCE(SUM(rsv.current_stock), 0) AS stock_total
            FROM variants v
            JOIN products p ON v.product_id = p.id
            LEFT JOIN real_stock_view rsv ON rsv.variant_id = v.id
            WHERE 1=1
        ";
        $params = [];

        if ($productId) {
            $sql .= " AND v.product_id = ?";
            $params[] = $productId;
        }
        if ($search) {
            $sql .= " AND (v.sku LIKE ? OR p.name LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        if ($size) {
            $sql .= " AND v.size = ?";
            $params[] = $size;
        }
        if ($color) {
            $sql .= " AND v.color = ?";
            $params[] = $color;
        }

        $sql .= " GROUP BY v.id ORDER BY p.name, v.size, v.color";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($productId, $size, $color, $sku = null)
    {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO variants (product_id, size, color, sku) VALUES (?, ?, ?, ?)");
        $stmt->execute([$productId, $size, $color, $sku]);
        return $pdo->lastInsertId();
    }

    public static function update($id, $size, $color, $sku = null)
    {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE variants SET size = ?, color = ?, sku = ? WHERE id = ?");
        return $stmt->execute([$size, $color, $sku, $id]);
    }

    public static function delete($id)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE variant_id = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            throw new Exception("Variante utilisée dans des commandes.");
        }
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM client_sale_items WHERE variant_id = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            throw new Exception("Variante utilisée dans des ventes.");
        }
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM stock_adjustments WHERE variant_id = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            throw new Exception("Variante utilisée dans des ajustements de stock.");
        }
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM country_stocks WHERE variant_id = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            throw new Exception("Variante présente dans le stock.");
        }

        $stmt = $pdo->prepare("DELETE FROM variants WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function stockByCountry($variantId)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT c.name AS country_name, c.flag, rsv.current_stock
            FROM real_stock_view rsv
            JOIN countries c ON c.id = rsv.country_id
            WHERE rsv.variant_id = ?
            ORDER BY c.name
        ");
        $stmt->execute([$variantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
