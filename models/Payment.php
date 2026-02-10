<?php
require_once 'utils.php';

// models/Payment.php
class Payment
{
    public static function all()
    {
        global $pdo;
        $stmt = $pdo->query("SELECT payments.*, suppliers.name AS supplier_name FROM payments JOIN suppliers ON payments.supplier_id = suppliers.id ORDER BY payment_date DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function filter($search = null, $dateFrom = null, $dateTo = null, $method = null, $limit = null, $offset = null)
    {
        global $pdo;

        $sql = "
            SELECT payments.*, suppliers.name AS supplier_name
            FROM payments
            JOIN suppliers ON payments.supplier_id = suppliers.id
            WHERE 1=1
        ";
        $params = [];

        if ($search) {
            $sql .= " AND suppliers.name LIKE ?";
            $params[] = '%' . $search . '%';
        }

        if ($method) {
            $sql .= " AND payments.payment_method = ?";
            $params[] = $method;
        }

        if ($dateFrom) {
            $sql .= " AND DATE(payments.payment_date) >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo) {
            $sql .= " AND DATE(payments.payment_date) <= ?";
            $params[] = $dateTo;
        }

        $sql .= " ORDER BY payment_date DESC";
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countFiltered($search = null, $dateFrom = null, $dateTo = null, $method = null)
    {
        global $pdo;
        $sql = "
            SELECT COUNT(*)
            FROM payments
            JOIN suppliers ON payments.supplier_id = suppliers.id
            WHERE 1=1
        ";
        $params = [];

        if ($search) {
            $sql .= " AND suppliers.name LIKE ?";
            $params[] = '%' . $search . '%';
        }
        if ($method) {
            $sql .= " AND payments.payment_method = ?";
            $params[] = $method;
        }
        if ($dateFrom) {
            $sql .= " AND DATE(payments.payment_date) >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo) {
            $sql .= " AND DATE(payments.payment_date) <= ?";
            $params[] = $dateTo;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public static function methods()
    {
        global $pdo;
        $stmt = $pdo->query("SELECT DISTINCT payment_method FROM payments WHERE payment_method IS NOT NULL AND payment_method != '' ORDER BY payment_method");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function create($data, $file, $allocations = [])
    {
        global $pdo;

        // 📁 Enregistrement du fichier preuve
        $uploadDir = "uploads/payments/" . $data['supplier_id'];
        ensure_upload_dir($uploadDir);

        $proofPath = null;
        if (!empty($file['proof']) && $file['proof']['error'] === UPLOAD_ERR_OK) {
            validate_upload_or_throw(
                $file['proof'],
                ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'],
                5 * 1024 * 1024
            );
            $filename = time() . '_' . bin2hex(random_bytes(4)) . '_' . sanitize_filename($file['proof']['name']);
            $targetPath = "$uploadDir/" . $filename;
            if (move_uploaded_file($file['proof']['tmp_name'], $targetPath)) {
                $proofPath = $targetPath;
            } else {
                throw new Exception("Échec de l'upload du justificatif.");
            }
        }

        // 💰 Calculer le vrai montant à partir des allocations
        $amount = 0;

        if (!empty($data['auto_allocate'])) {
            // Cas allocation auto : on utilisera ce montant après dans la logique
            $amount = 0; // sera recalculé dynamiquement dans la boucle
        } elseif (!empty($data['allocations'])) {
            foreach ($data['allocations'] as $orderId => $allocated) {
                $allocated = floatval($allocated);
                if ($allocated > 0) {
                    $amount += $allocated;
                }
            }
        }

        // 🛑 Si aucun montant n’a été alloué
        if ($amount <= 0 && empty($data['auto_allocate'])) {
            throw new Exception("Aucun montant à allouer. Paiement non enregistré.");
        }

        // 🧾 Enregistrement du paiement
        $stmt = $pdo->prepare("INSERT INTO payments (supplier_id, partner_id, payment_date, amount, currency, payment_method, notes, proof_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['supplier_id'],
            $data['partner_id'],
            $data['payment_date'],
            $amount, // calculé automatiquement
            $data['currency'] ?? 'MAD',
            $data['payment_method'],
            $data['notes'],
            $proofPath
        ]);

        $paymentId = $pdo->lastInsertId();

        // 💼 Affectation manuelle
        if (!empty($data['allocations']) && empty($data['auto_allocate'])) {
            $stmtAlloc = $pdo->prepare("INSERT INTO payment_allocations (payment_id, order_id, amount_allocated) VALUES (?, ?, ?)");
            foreach ($data['allocations'] as $orderId => $allocated) {
                $allocated = floatval($allocated);
                if ($allocated > 0) {
                    $stmtAlloc->execute([$paymentId, $orderId, $allocated]);
                }
            }
        }

        // 💼 Affectation automatique
        if (!empty($data['auto_allocate'])) {
            $remainingAmount = floatval($data['allocations_auto_amount'] ?? 0); // sécurité future, ou gérer dynamiquement

            // Charger les commandes impayées
            $stmtOrders = $pdo->prepare("
                SELECT o.id,
                       (SELECT SUM(unit_price * quantity_ordered) FROM order_items WHERE order_id = o.id) AS total_order,
                       (SELECT SUM(amount_allocated) FROM payment_allocations WHERE order_id = o.id) AS total_paid
                FROM orders o
                WHERE o.supplier_id = ?
                ORDER BY o.created_at ASC
            ");
            $stmtOrders->execute([$data['supplier_id']]);
            $orders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);

            $stmtAlloc = $pdo->prepare("INSERT INTO payment_allocations (payment_id, order_id, amount_allocated) VALUES (?, ?, ?)");

            foreach ($orders as $order) {
                $orderId = $order['id'];
                $total = floatval($order['total_order']);
                $paid = floatval($order['total_paid'] ?? 0);
                $toPay = $total - $paid;

                if ($toPay > 0 && $remainingAmount > 0) {
                    $allocate = min($toPay, $remainingAmount);
                    $stmtAlloc->execute([$paymentId, $orderId, $allocate]);
                    $amount += $allocate; // on cumule le montant final du paiement
                    $remainingAmount -= $allocate;
                }

                if ($remainingAmount <= 0) {
                    break;
                }
            }

            // 🔄 Mise à jour du montant réel du paiement
            $pdo->prepare("UPDATE payments SET amount = ? WHERE id = ?")->execute([$amount, $paymentId]);
        }

        return $paymentId;
    }



    public static function delete($id)
    {
        global $pdo;
        $pdo->prepare("DELETE FROM payment_allocations WHERE payment_id = ?")->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM payments WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function findBySupplier($supplierId)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE supplier_id = ? ORDER BY payment_date DESC");
        $stmt->execute([$supplierId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function allocationsByPayment($paymentId)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT pa.*, c.name AS destination_country
            FROM payment_allocations pa 
            JOIN orders o ON pa.order_id = o.id 
            JOIN countries c ON o.country_id = c.id
            WHERE pa.payment_id = ?
        ");
        $stmt->execute([$paymentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function paymentsByOrder($orderId)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT p.*, pa.amount_allocated, pr.name AS partner_name
            FROM payment_allocations pa
            JOIN payments p ON p.id = pa.payment_id
            LEFT JOIN partners pr ON p.partner_id = pr.id
            WHERE pa.order_id = ?
            ORDER BY p.payment_date DESC
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public static function totalAllocatedToOrder($orderId)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT SUM(amount_allocated) FROM payment_allocations WHERE order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetchColumn() ?: 0;
    }

    public static function getRemainingAmount($orderId)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT price_validated FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $total = $stmt->fetchColumn();
        $allocated = self::totalAllocatedToOrder($orderId);
        return $total - $allocated;
    }
}
