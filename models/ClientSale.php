<?php

class ClientSale
{
    public static function create($saleDate, $countryId, $customerName, $notes, $proofPath)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO client_sales (sale_date, country_id, customer_name, notes, proof_file)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$saleDate, $countryId, $customerName, $notes, $proofPath]);
        return $pdo->lastInsertId();
    }

    // New method for transport-based sales
    public static function createWithTransport($saleDate, $transportId, $customerName, $notes, $proofPath)
    {
        global $pdo;
        
        // Check if transport_id column exists
        if (!self::hasTransportColumn()) {
            // Fallback to country-based creation - this shouldn't happen in practice
            // but provides safety
            throw new Exception("Transport-based sales not supported in this database version");
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO client_sales (sale_date, transport_id, customer_name, notes, proof_file)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$saleDate, $transportId, $customerName, $notes, $proofPath]);
        return $pdo->lastInsertId();
    }

    public static function getAllWithCountry()
    {
        global $pdo;
        
        // Build query conditionally based on column existence
        $hasTransport = self::hasTransportColumn();
        
        if ($hasTransport) {
            $sql = "
                SELECT cs.*, 
                       COALESCE(t.name, c.name) AS destination_name,
                       COALESCE(t.transport_type, c.flag) AS destination_type,
                       c.name AS country_name, 
                       c.flag,
                       t.name AS transport_name
                FROM client_sales cs
                LEFT JOIN countries c ON cs.country_id = c.id
                LEFT JOIN transports t ON cs.transport_id = t.id
                ORDER BY cs.sale_date DESC, cs.id DESC
            ";
        } else {
            $sql = "
                SELECT cs.*, 
                       c.name AS country_name, 
                       c.flag
                FROM client_sales cs
                JOIN countries c ON cs.country_id = c.id
                ORDER BY cs.sale_date DESC, cs.id DESC
            ";
        }
        
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findWithCountry($id)
    {
        global $pdo;
        
        // Build query conditionally based on column existence
        $hasTransport = self::hasTransportColumn();
        
        if ($hasTransport) {
            $sql = "
                SELECT cs.*, 
                       COALESCE(t.name, c.name) AS destination_name,
                       COALESCE(t.transport_type, c.flag) AS destination_type,
                       c.name AS country_name, 
                       c.flag,
                       t.name AS transport_name
                FROM client_sales cs
                LEFT JOIN countries c ON cs.country_id = c.id
                LEFT JOIN transports t ON cs.transport_id = t.id
                WHERE cs.id = ?
            ";
        } else {
            $sql = "
                SELECT cs.*, 
                       c.name AS country_name, 
                       c.flag
                FROM client_sales cs
                JOIN countries c ON cs.country_id = c.id
                WHERE cs.id = ?
            ";
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function update($id, $saleDate, $countryId, $customerName, $notes, $proofPath = null)
    {
        global $pdo;

        if ($proofPath) {
            $stmt = $pdo->prepare("
	            UPDATE client_sales
	            SET sale_date = ?, country_id = ?, customer_name = ?, notes = ?, proof_file = ?, updated_at = NOW()
	            WHERE id = ?
	        ");
            $stmt->execute([$saleDate, $countryId, $customerName, $notes, $proofPath, $id]);
        } else {
            $stmt = $pdo->prepare("
	            UPDATE client_sales
	            SET sale_date = ?, country_id = ?, customer_name = ?, notes = ?, updated_at = NOW()
	            WHERE id = ?
	        ");
            $stmt->execute([$saleDate, $countryId, $customerName, $notes, $id]);
        }
    }

    public static function delete($id)
    {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM client_sales WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Helper method to check if transport_id column exists in client_sales table
    private static function hasTransportColumn()
    {
        static $hasColumn = null;
        
        if ($hasColumn !== null) {
            return $hasColumn;
        }
        
        global $pdo;
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM information_schema.columns 
                WHERE table_schema = DATABASE() 
                AND table_name = 'client_sales'
                AND column_name = 'transport_id'
            ");
            $stmt->execute();
            $hasColumn = $stmt->fetchColumn() > 0;
            return $hasColumn;
        } catch (Exception $e) {
            $hasColumn = false;
            return false;
        }
    }


}
