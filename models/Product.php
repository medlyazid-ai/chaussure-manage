<?php
require_once 'utils.php';

class Product
{
    private static function generateSku($productId, $size, $color)
    {
        global $pdo;
        $base = strtoupper($productId . '-' . $size . '-' . preg_replace('/\s+/', '', $color));
        $sku = $base;
        $i = 1;
        while (true) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM variants WHERE sku = ?");
            $stmt->execute([$sku]);
            if ((int)$stmt->fetchColumn() === 0) {
                return $sku;
            }
            $sku = $base . '-' . $i;
            $i++;
        }
    }
    public static function allWithVariants()
    {
        global $pdo;
        $stmt = $pdo->query("SELECT * FROM products");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as &$product) {
            $stmt2 = $pdo->prepare("
                SELECT v.*, COALESCE(rs.stock_quantity, 0) AS stock_quantity
                FROM variants v
                LEFT JOIN (
                    SELECT variant_id, SUM(current_stock) AS stock_quantity
                    FROM real_stock_view
                    GROUP BY variant_id
                ) rs ON rs.variant_id = v.id
                WHERE v.product_id = ?
            ");
            $stmt2->execute([$product['id']]);
            $product['variants'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        }

        return $products;
    }

    public static function filterWithVariants($search = null, $category = null, $limit = null, $offset = null)
    {
        global $pdo;

        $sql = "SELECT * FROM products WHERE 1=1";
        $params = [];

        if ($search) {
            $sql .= " AND name LIKE ?";
            $params[] = '%' . $search . '%';
        }

        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        $sql .= " ORDER BY name";
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as &$product) {
            $stmt2 = $pdo->prepare("
                SELECT v.*, COALESCE(rs.stock_quantity, 0) AS stock_quantity
                FROM variants v
                LEFT JOIN (
                    SELECT variant_id, SUM(current_stock) AS stock_quantity
                    FROM real_stock_view
                    GROUP BY variant_id
                ) rs ON rs.variant_id = v.id
                WHERE v.product_id = ?
            ");
            $stmt2->execute([$product['id']]);
            $product['variants'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        }

        return $products;
    }

    public static function countFiltered($search = null, $category = null)
    {
        global $pdo;
        $sql = "SELECT COUNT(*) FROM products WHERE 1=1";
        $params = [];

        if ($search) {
            $sql .= " AND name LIKE ?";
            $params[] = '%' . $search . '%';
        }

        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public static function categories()
    {
        global $pdo;
        $stmt = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function find($id)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data, $files)
    {
        global $pdo;

        $imagePath = null;
        if (!empty($files['image']['tmp_name'])) {
            validate_upload_or_throw(
                $files['image'],
                ['image/jpeg', 'image/png', 'image/webp'],
                5 * 1024 * 1024
            );

            $uploadDir = 'uploads/products';
            ensure_upload_dir($uploadDir);

            $fileName = time() . '_' . bin2hex(random_bytes(4)) . '_' . sanitize_filename($files['image']['name']);
            $targetFile = $uploadDir . '/' . $fileName;

            if (move_uploaded_file($files['image']['tmp_name'], $targetFile)) {
                $imagePath = $targetFile;
            } else {
                throw new Exception("Échec de l'upload de l'image.");
            }
        }

        $stmt = $pdo->prepare("INSERT INTO products (name, description, category, image_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['name'], $data['description'], $data['category'], $imagePath]);

        $productId = $pdo->lastInsertId();

        foreach ($data['variants'] as $v) {
            $size = trim($v['size'] ?? '');
            $color = trim($v['color'] ?? '');
            if ($size === '' || $color === '') {
                continue;
            }
            $sku = trim($v['sku'] ?? '');
            if ($sku === '') {
                $sku = self::generateSku($productId, $size, $color);
            }
            $stmt = $pdo->prepare("INSERT INTO variants (product_id, size, color, sku) VALUES (?, ?, ?, ?)");
            $stmt->execute([$productId, $size, $color, $sku]);
        }

        return $productId;
    }


    public static function update($id, $data, $variants)
    {
        global $pdo;

        // 1. Mise à jour des infos produit
        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, category = ? WHERE id = ?");
        $stmt->execute([$data['name'], $data['description'], $data['category'], $id]);

        // 2. Récupérer les anciennes variantes
        $stmtOld = $pdo->prepare("SELECT * FROM variants WHERE product_id = ?");
        $stmtOld->execute([$id]);
        $oldVariants = $stmtOld->fetchAll(PDO::FETCH_ASSOC);

        // 3. On crée une map SKU -> ID pour retrouver rapidement les anciennes
        $oldMap = [];
        foreach ($oldVariants as $v) {
            $oldMap[$v['sku']] = $v['id'];
        }

        // 4. Mettre à jour ou insérer les nouvelles variantes
        foreach ($variants as $v) {
            $size = trim($v['size'] ?? '');
            $color = trim($v['color'] ?? '');
            if ($size === '' || $color === '') {
                continue;
            }
            $sku = trim($v['sku'] ?? '');
            if ($sku === '') {
                $sku = self::generateSku($id, $size, $color);
            }
            if (isset($oldMap[$sku])) {
                // La variante existe → UPDATE
                $stmt = $pdo->prepare("UPDATE variants SET size = ?, color = ?, sku = ? WHERE id = ?");
                $stmt->execute([$size, $color, $sku, $oldMap[$sku]]);
                unset($oldMap[$sku]); // On l’a traitée
            } else {
                // Nouvelle variante → INSERT
                $stmt = $pdo->prepare("INSERT INTO variants (product_id, size, color, sku) VALUES (?, ?, ?, ?)");
                $stmt->execute([$id, $size, $color, $sku]);
            }
        }

        // 5. Supprimer les anciennes variantes qui n’existent plus (et ne sont pas dans une commande)
        foreach ($oldMap as $sku => $variantId) {
            // Vérifie s’il y a des order_items qui utilisent cette variante
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE variant_id = ?");
            $stmt->execute([$variantId]);
            $used = $stmt->fetchColumn();

            if (!$used) {
                $pdo->prepare("DELETE FROM variants WHERE id = ?")->execute([$variantId]);
            }
        }
    }

    public static function all()
    {
        global $pdo;

        $stmt = $pdo->query("SELECT * FROM products ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    public static function delete($id)
    {
        global $pdo;

        // 1. Vérifier si des variantes du produit sont utilisées dans des commandes
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM variants v 
            JOIN order_items oi ON oi.variant_id = v.id 
            WHERE v.product_id = ?
        ");
        $stmt->execute([$id]);
        $used = $stmt->fetchColumn();

        if ($used > 0) {
            // On empêche la suppression
            throw new Exception("Ce produit ne peut pas être supprimé car il est utilisé dans une ou plusieurs commandes.");
        }

        // 1.b Vérifier ajustements de stock liés aux variantes
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM variants v
            JOIN stock_adjustments sa ON sa.variant_id = v.id
            WHERE v.product_id = ?
        ");
        $stmt->execute([$id]);
        $usedAdjust = $stmt->fetchColumn();
        if ($usedAdjust > 0) {
            throw new Exception("Ce produit ne peut pas être supprimé car il a des ajustements de stock.");
        }

        // 1.c Vérifier stocks pays liés aux variantes
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM variants v
            JOIN country_stocks cs ON cs.variant_id = v.id
            WHERE v.product_id = ?
        ");
        $stmt->execute([$id]);
        $usedStock = $stmt->fetchColumn();
        if ($usedStock > 0) {
            throw new Exception("Ce produit ne peut pas être supprimé car il est présent dans le stock.");
        }

        // 1.d Vérifier ventes clients liées aux variantes
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM variants v
            JOIN client_sale_items csi ON csi.variant_id = v.id
            WHERE v.product_id = ?
        ");
        $stmt->execute([$id]);
        $usedSales = $stmt->fetchColumn();
        if ($usedSales > 0) {
            throw new Exception("Ce produit ne peut pas être supprimé car il est utilisé dans des ventes clients.");
        }

        // 2. Supprimer les variantes associées (non utilisées)
        $pdo->prepare("DELETE FROM variants WHERE product_id = ?")->execute([$id]);

        // 3. Supprimer le produit
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    }

}
