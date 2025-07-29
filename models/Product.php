<?php
class Product {
    public static function allWithVariants() {
        global $pdo;
        $stmt = $pdo->query("SELECT * FROM products");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as &$product) {
            $stmt2 = $pdo->prepare("SELECT * FROM variants WHERE product_id = ?");
            $stmt2->execute([$product['id']]);
            $product['variants'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        }

        return $products;
    }

    public static function find($id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data, $files) {
        global $pdo;

        $imagePath = null;
        if (!empty($files['image']['tmp_name'])) {
            $uploadDir = 'uploads/products';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = time() . '_' . basename($files['image']['name']);
            $targetFile = $uploadDir . '/' . $fileName;

            if (move_uploaded_file($files['image']['tmp_name'], $targetFile)) {
                $imagePath = $targetFile;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO products (name, description, category, image_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['name'], $data['description'], $data['category'], $imagePath]);

        $productId = $pdo->lastInsertId();

        foreach ($data['variants'] as $v) {
            $stmt = $pdo->prepare("INSERT INTO variants (product_id, size, color, sku) VALUES (?, ?, ?, ?)");
            $stmt->execute([$productId, $v['size'], $v['color'], $v['sku']]);
        }

        return $productId;
    }


    public static function update($id, $data, $variants) {
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
            if (isset($oldMap[$v['sku']])) {
                // La variante existe → UPDATE
                $stmt = $pdo->prepare("UPDATE variants SET size = ?, color = ? WHERE id = ?");
                $stmt->execute([$v['size'], $v['color'], $oldMap[$v['sku']]]);
                unset($oldMap[$v['sku']]); // On l’a traitée
            } else {
                // Nouvelle variante → INSERT
                $stmt = $pdo->prepare("INSERT INTO variants (product_id, size, color, sku) VALUES (?, ?, ?, ?)");
                $stmt->execute([$id, $v['size'], $v['color'], $v['sku']]);
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

    public static function all() {
        global $pdo;

        $stmt = $pdo->query("SELECT * FROM products ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    public static function delete($id) {
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

        // 2. Supprimer les variantes associées (non utilisées)
        $pdo->prepare("DELETE FROM variants WHERE product_id = ?")->execute([$id]);

        // 3. Supprimer le produit
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    }

}
