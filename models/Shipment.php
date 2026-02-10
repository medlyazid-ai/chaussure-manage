<?php
require_once 'utils.php';

class Shipment
{
    public static function all()
    {
        global $pdo;
        $stmt = $pdo->query("
	        SELECT s.*, o.supplier_id, o.destination_country, sup.name AS supplier_name 
	        FROM shipments s
	        JOIN orders o ON s.order_id = o.id
	        JOIN suppliers sup ON o.supplier_id = sup.id
	        ORDER BY s.shipment_date DESC
	    ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public static function create($data, $files)
    {
        global $pdo;

        // 📁 Gérer le reçu
        $receiptPath = $data['receipt_path'] ?? null;
        if (!$receiptPath && !empty($files['receipt']['tmp_name'])) {
            validate_upload_or_throw(
                $files['receipt'],
                ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'],
                5 * 1024 * 1024
            );
            $uploadDir = 'uploads/receipts/' . $data['order_id'];
            ensure_upload_dir($uploadDir);
            $fileName = time() . '_' . bin2hex(random_bytes(4)) . '_' . sanitize_filename($files['receipt']['name']);
            $targetFile = $uploadDir . '/' . $fileName;

            if (move_uploaded_file($files['receipt']['tmp_name'], $targetFile)) {
                $receiptPath = $targetFile;
            } else {
                throw new Exception("Échec de l'upload du reçu.");
            }
        }

        // 📁 Gérer l'image du colis
        $packageImagePath = $data['package_image'] ?? null;
        if (!$packageImagePath && !empty($files['package_image']['tmp_name'])) {
            validate_upload_or_throw(
                $files['package_image'],
                ['image/jpeg', 'image/png', 'image/webp'],
                5 * 1024 * 1024
            );
            $uploadDir = 'uploads/package_images/' . $data['order_id'];
            ensure_upload_dir($uploadDir);
            $fileName = time() . '_' . bin2hex(random_bytes(4)) . '_' . sanitize_filename($files['package_image']['name']);
            $targetFile = $uploadDir . '/' . $fileName;

            if (move_uploaded_file($files['package_image']['tmp_name'], $targetFile)) {
                $packageImagePath = $targetFile;
            } else {
                throw new Exception("Échec de l'upload de l'image du colis.");
            }
        }

        // 🧾 Préparer les autres champs (avec fallback null)
        $trackingCode  = $data['tracking_code'] ?? null;
        $packageWeight = !empty($data['package_weight']) ? floatval($data['package_weight']) : null;
        $transportFee  = !empty($data['transport_fee']) ? floatval($data['transport_fee']) : null;

        // 📦 Insérer l'envoi partiel
        $stmt = $pdo->prepare("
            INSERT INTO shipments (
                order_id, shipment_date, notes, receipt_path, status,
                transport_id, tracking_code, package_weight, transport_fee, package_image
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['order_id'],
            $data['shipment_date'],
            $data['notes'],
            $receiptPath,
            'En attente de confirmation',
            $data['transport_id'] ?? null,
            $trackingCode,
            $packageWeight,
            $transportFee,
            $packageImagePath
        ]);

        $shipmentId = $pdo->lastInsertId();

        // 🧾 Insérer les lignes d'envoi (shipment_items)
        if (!empty($data['shipment_items'])) {
            $stmtItem = $pdo->prepare("
                INSERT INTO shipment_items (shipment_id, order_item_id, quantity_sent)
                VALUES (?, ?, ?)
            ");

            foreach ($data['shipment_items'] as $item) {
                $orderItemId = $item['order_item_id'];
                $qty = (int) $item['quantity_sent'];

                if ($qty > 0) {
                    $stmtItem->execute([$shipmentId, $orderItemId, $qty]);
                }
            }
        }

        return $shipmentId;
    }


    public static function find($id)
    {
        global $pdo;
        $stmt = $pdo->prepare("
				SELECT s.*, 
				       t.name AS transport_name, 
				       t.transport_type, 
				       t.contact_info
				FROM shipments s
				LEFT JOIN transports t ON s.transport_id = t.id
				WHERE s.id = ?
			");

        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function findByOrder($orderId)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT s.*, si.order_item_id, si.quantity_sent
            FROM shipments s
            LEFT JOIN shipment_items si ON s.id = si.shipment_id
            WHERE s.order_id = ?
            ORDER BY s.shipment_date DESC
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function delete($id)
    {
        global $pdo;
        $pdo->prepare("DELETE FROM shipment_items WHERE shipment_id = ?")->execute([$id]);
        return $pdo->prepare("DELETE FROM shipments WHERE id = ?")->execute([$id]);
    }
    public static function findWithDetails($id)
    {
        global $pdo;
        $stmt = $pdo->prepare("
	        SELECT s.*, o.supplier_id, sup.name AS supplier_name
	        FROM shipments s
	        JOIN orders o ON s.order_id = o.id
	        JOIN suppliers sup ON o.supplier_id = sup.id
	        WHERE s.id = ?
	    ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function itemsWithVariants($shipmentId)
    {
        global $pdo;
        $stmt = $pdo->prepare("
	        SELECT si.quantity_sent, v.size, v.color
	        FROM shipment_items si
	        JOIN order_items oi ON si.order_item_id = oi.id
	        JOIN variants v ON oi.variant_id = v.id
	        WHERE si.shipment_id = ?
	    ");
        $stmt->execute([$shipmentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function itemsWithDetails($shipment_id)
    {
        global $pdo;
        $stmt = $pdo->prepare("
	        SELECT si.*, 
	               v.size, 
	               v.color,
	               oi.quantity_ordered
	        FROM shipment_items si
	        JOIN order_items oi ON si.order_item_id = oi.id
	        JOIN variants v ON oi.variant_id = v.id
	        WHERE si.shipment_id = ?
	    ");
        $stmt->execute([$shipment_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getTotalSentByOrderItem($orderItemId)
    {
        global $pdo;
        $stmt = $pdo->prepare("
	        SELECT SUM(quantity_sent) as total_sent
	        FROM shipment_items
	        WHERE order_item_id = ?
	    ");
        $stmt->execute([$orderItemId]);
        return (int) $stmt->fetchColumn();
    }


    public static function updateStatus($id, $status)
    {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE shipments SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public static function getVariants($shipmentId)
    {
        global $pdo;

        $stmt = $pdo->prepare("
        SELECT 
            si.order_item_id,
            si.quantity_sent,
            oi.quantity_ordered,
            v.size,
            v.color
        FROM shipment_items si
        JOIN order_items oi ON si.order_item_id = oi.id
        JOIN variants v ON oi.variant_id = v.id
        WHERE si.shipment_id = ?
    ");
        $stmt->execute([$shipmentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTotalSentForItem($orderItemId)
    {
        global $pdo;

        $stmt = $pdo->prepare("
        SELECT SUM(quantity_sent) AS total_sent
        FROM shipment_items
        WHERE order_item_id = ?
    ");
        $stmt->execute([$orderItemId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total_sent'] ?? 0;
    }

    public static function allWithOrderAndSupplier($filters = [], $limit = null, $offset = null)
    {
        global $pdo;
        $sql = "
            SELECT 
                s.*, 
                o.order_date,
                o.product_id,
                p.name AS product_name,
                p.image_path AS product_image,
                c.name AS destination_country, 
                c.flag AS country_flag, 
                sup.name AS supplier_name,
                cc.name AS company_name,
                t.name AS transport_name,
                t.transport_type,
                t.contact_info
            FROM shipments s
            JOIN orders o ON s.order_id = o.id
            JOIN suppliers sup ON o.supplier_id = sup.id
            JOIN countries c ON o.country_id = c.id
            JOIN products p ON o.product_id = p.id
            LEFT JOIN country_companies cc ON o.company_id = cc.id
            LEFT JOIN transports t ON s.transport_id = t.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['supplier_id'])) {
            $sql .= " AND o.supplier_id = ?";
            $params[] = $filters['supplier_id'];
        }
        if (!empty($filters['product_id'])) {
            $sql .= " AND o.product_id = ?";
            $params[] = $filters['product_id'];
        }
        if (!empty($filters['country_id'])) {
            $sql .= " AND o.country_id = ?";
            $params[] = $filters['country_id'];
        }
        if (!empty($filters['company_id'])) {
            $sql .= " AND o.company_id = ?";
            $params[] = $filters['company_id'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(s.shipment_date) >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(s.shipment_date) <= ?";
            $params[] = $filters['date_to'];
        }

        $sql .= " ORDER BY s.shipment_date DESC";
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countFiltered($filters = [])
    {
        global $pdo;
        $sql = "
            SELECT COUNT(*)
            FROM shipments s
            JOIN orders o ON s.order_id = o.id
            JOIN suppliers sup ON o.supplier_id = sup.id
            JOIN countries c ON o.country_id = c.id
            JOIN products p ON o.product_id = p.id
            LEFT JOIN country_companies cc ON o.company_id = cc.id
            LEFT JOIN transports t ON s.transport_id = t.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['supplier_id'])) {
            $sql .= " AND o.supplier_id = ?";
            $params[] = $filters['supplier_id'];
        }
        if (!empty($filters['product_id'])) {
            $sql .= " AND o.product_id = ?";
            $params[] = $filters['product_id'];
        }
        if (!empty($filters['country_id'])) {
            $sql .= " AND o.country_id = ?";
            $params[] = $filters['country_id'];
        }
        if (!empty($filters['company_id'])) {
            $sql .= " AND o.company_id = ?";
            $params[] = $filters['company_id'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(s.shipment_date) >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(s.shipment_date) <= ?";
            $params[] = $filters['date_to'];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public static function getProductImage($shipmentId)
    {
        global $pdo;
        $stmt = $pdo->prepare("
        SELECT p.image_path
        FROM shipment_items si
        JOIN order_items oi ON si.order_item_id = oi.id
        JOIN variants v ON oi.variant_id = v.id
        JOIN products p ON v.product_id = p.id
        WHERE si.shipment_id = ?
        LIMIT 1
    ");
        $stmt->execute([$shipmentId]);
        return $stmt->fetchColumn(); // retourne le chemin de l’image
    }

    public static function getOrderTotals($orderId)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(quantity_ordered), 0) AS total_ordered
            FROM order_items
            WHERE order_id = ?
        ");
        $stmt->execute([$orderId]);
        return (int)$stmt->fetchColumn();
    }

    public static function getOrderTotalSent($orderId)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(si.quantity_sent), 0) AS total_sent
            FROM shipment_items si
            JOIN shipments s ON s.id = si.shipment_id
            WHERE s.order_id = ?
        ");
        $stmt->execute([$orderId]);
        return (int)$stmt->fetchColumn();
    }

    public static function getOrderProgress($orderId)
    {
        $ordered = self::getOrderTotals($orderId);
        $sent = self::getOrderTotalSent($orderId);
        $remaining = max($ordered - $sent, 0);
        $percent = $ordered > 0 ? (int)round(($sent / $ordered) * 100) : 0;
        $status = $ordered > 0 && $remaining === 0 ? 'Livré (complet)' : ($sent > 0 ? 'Envoi partiel' : 'En attente');
        return [
            'ordered' => $ordered,
            'sent' => $sent,
            'remaining' => $remaining,
            'percent' => $percent,
            'status' => $status
        ];
    }

    public static function bySupplier($supplierId)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT s.*, o.id AS order_id, o.status AS order_status,
                   p.name AS product_name, c.name AS country_name,
                   cc.name AS company_name
            FROM shipments s
            JOIN orders o ON s.order_id = o.id
            JOIN products p ON o.product_id = p.id
            JOIN countries c ON o.country_id = c.id
            LEFT JOIN country_companies cc ON o.company_id = cc.id
            WHERE o.supplier_id = ?
            ORDER BY s.shipment_date DESC
        ");
        $stmt->execute([$supplierId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }




}
