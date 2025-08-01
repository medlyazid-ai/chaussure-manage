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

    public static function getAllWithCountry() {
	    global $pdo;
	    $stmt = $pdo->query("
	        SELECT cs.*, c.name AS country_name, c.flag
	        FROM client_sales cs
	        JOIN countries c ON cs.country_id = c.id
	        ORDER BY cs.sale_date DESC, cs.id DESC
	    ");
	    return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public static function findWithCountry($id)
	{
	    global $pdo;
	    $stmt = $pdo->prepare("
	        SELECT cs.*, c.name AS country_name, c.flag
	        FROM client_sales cs
	        JOIN countries c ON cs.country_id = c.id
	        WHERE cs.id = ?
	    ");
	    $stmt->execute([$id]);
	    return $stmt->fetch(PDO::FETCH_ASSOC);
	}


}
