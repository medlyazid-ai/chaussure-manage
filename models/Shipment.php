<?php
class Shipment {
	public static function all() {
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


	public static function create($data, $files) {
	    global $pdo;

	    // Gérer le reçu
	    $receiptPath = null;
	    if (!empty($files['receipt']['tmp_name'])) {
	        $uploadDir = 'uploads/receipts/' . $data['order_id'];
	        if (!is_dir($uploadDir)) {
	            mkdir($uploadDir, 0777, true);
	        }
	        $fileName = time() . '_' . basename($files['receipt']['name']);
	        $targetFile = $uploadDir . '/' . $fileName;

	        if (move_uploaded_file($files['receipt']['tmp_name'], $targetFile)) {
	            $receiptPath = $targetFile;
	        }
	    }

	    // Insérer l'envoi partiel
	    $stmt = $pdo->prepare("
		    INSERT INTO shipments (order_id, shipment_date, notes, receipt_path, status)
		    VALUES (?, ?, ?, ?, ?)
		");
		$stmt->execute([
		    $data['order_id'],
		    $data['shipment_date'],
		    $data['notes'],
		    $receiptPath,
		    'En attente de confirmation'
		]);

	    $shipmentId = $pdo->lastInsertId();

	    // Insérer les lignes d'envoi (shipment_items)
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

	public static function find($id) {
	    global $pdo;
	    $stmt = $pdo->prepare("SELECT * FROM shipments WHERE id = ?");
	    $stmt->execute([$id]);
	    return $stmt->fetch(PDO::FETCH_ASSOC);
	}

    public static function findByOrder($orderId) {
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

    public static function delete($id) {
        global $pdo;
        $pdo->prepare("DELETE FROM shipment_items WHERE shipment_id = ?")->execute([$id]);
        return $pdo->prepare("DELETE FROM shipments WHERE id = ?")->execute([$id]);
    }
    public static function findWithDetails($id) {
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

	public static function itemsWithVariants($shipmentId) {
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

	public static function itemsWithDetails($shipment_id) {
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
	public static function getTotalSentByOrderItem($orderItemId) {
	    global $pdo;
	    $stmt = $pdo->prepare("
	        SELECT SUM(quantity_sent) as total_sent
	        FROM shipment_items
	        WHERE order_item_id = ?
	    ");
	    $stmt->execute([$orderItemId]);
	    return (int) $stmt->fetchColumn();
	}


	public static function updateStatus($id, $status) {
	    global $pdo;
	    $stmt = $pdo->prepare("UPDATE shipments SET status = ? WHERE id = ?");
	    return $stmt->execute([$status, $id]);
	}

public static function getVariants($shipmentId) {
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

public static function getTotalSentForItem($orderItemId) {
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

public static function allWithOrderAndSupplier() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT s.*, o.order_date, c.name AS destination_country, c.flag AS country_flag, sup.name AS supplier_name
        FROM shipments s
        JOIN orders o ON s.order_id = o.id
        JOIN suppliers sup ON o.supplier_id = sup.id
        JOIN countries c ON o.country_id = c.id
        ORDER BY s.shipment_date DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}






}
