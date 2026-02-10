<?php

class Supplier
{
    public static function all()
    {
        global $pdo;
        $stmt = $pdo->query("SELECT * FROM suppliers ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function allPaged($limit, $offset)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM suppliers ORDER BY name LIMIT ? OFFSET ?");
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countAll()
    {
        global $pdo;
        $stmt = $pdo->query("SELECT COUNT(*) FROM suppliers");
        return (int)$stmt->fetchColumn();
    }

    public static function find($id)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data)
    {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact_name, phone, email, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['name'],
            $data['contact_name'],
            $data['phone'],
            $data['email'],
            $data['address']
        ]);
    }

    public static function update($id, $data)
    {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE suppliers SET name = ?, contact_name = ?, phone = ?, email = ?, address = ? WHERE id = ?");
        $stmt->execute([
            $data['name'],
            $data['contact_name'],
            $data['phone'],
            $data['email'],
            $data['address'],
            $id
        ]);
    }


    public static function delete($id)
    {
        global $pdo;

        /*
                // Vérifie si des produits sont liés à ce fournisseur
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE supplier_id = ?");
                $stmt->execute([$id]);
                $productCount = (int)$stmt->fetchColumn();

                // Vérifie si des paiements sont liés à ce fournisseur
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM payments WHERE supplier_id = ?");
                try {
                    $stmt->execute([$id]);
                    $paymentCount = (int)$stmt->fetchColumn();
                } catch (PDOException $e) {
                    // Cas où la colonne supplier_id n'existe pas ou autre erreur SQL
                    throw new Exception("Erreur lors de la vérification des paiements : " . $e->getMessage());
                }

                // Protection : empêcher suppression si des relations existent
                if ($productCount > 0 || $paymentCount > 0) {
                    throw new Exception("❌ Impossible de supprimer ce fournisseur : il est lié à $productCount produit(s) et $paymentCount paiement(s).");
                }

        */
        // Suppression autorisée
        $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
        return $stmt->execute([$id]);
    }





}
