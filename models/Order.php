<?php
class Order {

    public static function find($id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function all() {
        global $pdo;
        $stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function allWithCountry() {
        global $pdo;

        $stmt = $pdo->query("
            SELECT o.*, s.name AS supplier_name, c.name AS destination_country, c.flag
            FROM orders o
            JOIN suppliers s ON o.supplier_id = s.id
            JOIN countries c ON o.country_id = c.id
            ORDER BY o.created_at DESC
        ");

        return $stmt->fetchAll();
    }

    public static function update($id, $data) {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE orders SET supplier_id = ?, country_id = ?, product_id = ? WHERE id = ?");
        return $stmt->execute([
            $data['supplier_id'],
            $data['country_id'],
            $data['product_id'],
            $id
        ]);
    }

    public static function deleteOrderItems($orderId) {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
    }

    public static function addOrderItem($orderId, $variant, $productId) {
        global $pdo;

        // 1. Rechercher la variante existante
        $stmtFind = $pdo->prepare("SELECT id FROM variants WHERE product_id = ? AND size = ? AND color = ?");
        $stmtFind->execute([
            $productId,
            $variant['size'],
            $variant['color']
        ]);
        $existingVariant = $stmtFind->fetch(PDO::FETCH_ASSOC);

        // 2. Si elle n'existe pas, l'insérer
        if (!$existingVariant) {
            $sku = strtoupper($productId . '-' . $variant['size'] . '-' . $variant['color']);
            $stmtInsert = $pdo->prepare("INSERT INTO variants (product_id, size, color, sku) VALUES (?, ?, ?, ?)");
            $stmtInsert->execute([
                $productId,
                $variant['size'],
                $variant['color'],
                $sku
            ]);
            $variantId = $pdo->lastInsertId();
        } else {
            $variantId = $existingVariant['id'];
        }

        // 3. Ajouter dans order_items
        $stmtAddItem = $pdo->prepare("INSERT INTO order_items (order_id, variant_id, quantity_ordered, unit_price) VALUES (?, ?, ?, ?)");
        $stmtAddItem->execute([
            $orderId,
            $variantId,
            $variant['quantity_ordered'],
            $variant['unit_price']
        ]);
    }


    public static function create($data) {
        global $pdo;

        $variants = $data['variants'] ?? [];
        $totalQuantity = array_sum(array_column($variants, 'quantity_ordered'));

        $stmt = $pdo->prepare("INSERT INTO orders (supplier_id, product_id, country_id, status, total_quantity) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['supplier_id'],
            $data['product_id'],
            $data['country_id'],
            'Initial',
            $totalQuantity
        ]);

        $orderId = $pdo->lastInsertId();

        $stmtFindVariant = $pdo->prepare("SELECT id FROM variants WHERE product_id = ? AND size = ? AND color = ?");
        $stmtInsertVariant = $pdo->prepare("INSERT INTO variants (product_id, size, color, sku) VALUES (?, ?, ?, ?)");
        $stmtInsertItem = $pdo->prepare("INSERT INTO order_items (order_id, variant_id, quantity_ordered, unit_price) VALUES (?, ?, ?, ?)");

        foreach ($variants as $v) {
            $stmtFindVariant->execute([$data['product_id'], $v['size'], $v['color']]);
            $variant = $stmtFindVariant->fetch(PDO::FETCH_ASSOC);

            if ($variant) {
                $variantId = $variant['id'];
            } else {
                $sku = strtoupper($data['product_id'] . '-' . $v['size'] . '-' . $v['color']);
                $stmtInsertVariant->execute([
                    $data['product_id'],
                    $v['size'],
                    $v['color'],
                    $sku
                ]);
                $variantId = $pdo->lastInsertId();
            }

            $stmtInsertItem->execute([
                $orderId,
                $variantId,
                $v['quantity_ordered'],
                $v['unit_price']
            ]);
        }

        return $orderId;
    }

    public static function delete($id) {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function items($orderId) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function orderItems($orderId) {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT oi.*, v.size, v.color
            FROM order_items oi
            JOIN variants v ON oi.variant_id = v.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function orderItemsWithSentQuantities($orderId) {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT 
                oi.*, 
                v.size, v.color,
                COALESCE(SUM(si.quantity_sent), 0) AS quantity_sent
            FROM order_items oi
            JOIN variants v ON oi.variant_id = v.id
            LEFT JOIN shipment_items si ON si.order_item_id = oi.id
            WHERE oi.order_id = ?
            GROUP BY oi.id
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function partialShipments($orderId) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM shipments WHERE order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function allWithSupplier() {
        global $pdo;
        $sql = "SELECT 
                    o.*, 
                    s.name AS supplier_name,
                    c.name AS destination_country,
                    c.flag AS country_flag
                FROM orders o
                JOIN suppliers s ON o.supplier_id = s.id
                JOIN countries c ON o.country_id = c.id
                ORDER BY o.created_at DESC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public static function findWithSupplier($orderId) {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT o.*, s.name AS supplier_name, c.name AS destination_country, c.flag
            FROM orders o
            JOIN suppliers s ON o.supplier_id = s.id
            JOIN countries c ON o.country_id = c.id
            WHERE o.id = ?
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function payments($orderId) {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT p.* 
            FROM payments p
            JOIN orders o ON o.supplier_id = p.supplier_id
            WHERE o.id = ?
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUnpaidBySupplier($supplierId) {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT o.*, 
                IFNULL(SUM(pa.amount_allocated), 0) AS already_paid,
                (SELECT SUM(oi.unit_price * oi.quantity_ordered) FROM order_items oi WHERE oi.order_id = o.id) AS total_amount
            FROM orders o
            LEFT JOIN payment_allocations pa ON o.id = pa.order_id
            WHERE o.supplier_id = ?
            GROUP BY o.id
            HAVING total_amount > already_paid
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$supplierId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findBySupplier($supplierId) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE supplier_id = ? ORDER BY id DESC");
        $stmt->execute([$supplierId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTotalAmount($orderId) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT SUM(unit_price * quantity_ordered) FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetchColumn() ?: 0;
    }

    public static function filter($supplierId = null, $status = null) {
        global $pdo;

        $sql = "
            SELECT o.*, s.name AS supplier_name, 
                   (SELECT SUM(quantity_ordered) FROM order_items WHERE order_id = o.id) AS total_quantity
            FROM orders o
            JOIN suppliers s ON o.supplier_id = s.id
            WHERE 1=1
        ";
        $params = [];

        if ($supplierId) {
            $sql .= " AND o.supplier_id = ?";
            $params[] = $supplierId;
        }

        if ($status) {
            $sql .= " AND o.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY o.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function filterWithSupplier($supplierId = null, $status = null) {
        global $pdo;

        $sql = "
            SELECT o.*, s.name AS supplier_name, c.name AS destination_country, c.flag
            FROM orders o
            JOIN suppliers s ON o.supplier_id = s.id
            JOIN countries c ON o.country_id = c.id
            WHERE 1=1
        ";

        $params = [];

        if ($supplierId) {
            $sql .= " AND o.supplier_id = ?";
            $params[] = $supplierId;
        }

        if ($status) {
            $sql .= " AND o.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY o.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function updateStatus($id, $newStatus) {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        return $stmt->execute([$newStatus, $id]);
    }

}
