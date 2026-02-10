<?php

class ClientSale
{
    public static function create($saleDate, $countryId, $companyId, $partnerId, $accountId, $amountReceived, $currency, $receivedDate, $paymentMethod, $proofPath)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO client_sales (sale_date, country_id, company_id, partner_id, account_id, amount_received, currency, received_date, payment_method, proof_file)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$saleDate, $countryId, $companyId, $partnerId, $accountId, $amountReceived, $currency, $receivedDate, $paymentMethod, $proofPath]);
        return $pdo->lastInsertId();
    }

    public static function getAllWithCountry()
    {
        global $pdo;
        $stmt = $pdo->query("
	        SELECT cs.*, c.name AS country_name, c.flag, cc.name AS company_name,
                   p.name AS partner_name, pa.account_label
	        FROM client_sales cs
	        JOIN countries c ON cs.country_id = c.id
            LEFT JOIN country_companies cc ON cs.company_id = cc.id
            LEFT JOIN partners p ON cs.partner_id = p.id
            LEFT JOIN partner_accounts pa ON cs.account_id = pa.id
	        ORDER BY cs.sale_date DESC, cs.id DESC
	    ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function filter($countryId = null, $dateFrom = null, $dateTo = null, $companyId = null, $limit = null, $offset = null)
    {
        global $pdo;
        $sql = "
            SELECT cs.*, c.name AS country_name, c.flag, cc.name AS company_name,
                   p.name AS partner_name, pa.account_label
            FROM client_sales cs
            JOIN countries c ON cs.country_id = c.id
            LEFT JOIN country_companies cc ON cs.company_id = cc.id
            LEFT JOIN partners p ON cs.partner_id = p.id
            LEFT JOIN partner_accounts pa ON cs.account_id = pa.id
            WHERE 1=1
        ";
        $params = [];

        if ($countryId) {
            $sql .= " AND cs.country_id = ?";
            $params[] = $countryId;
        }

        if ($dateFrom) {
            $sql .= " AND DATE(cs.sale_date) >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo) {
            $sql .= " AND DATE(cs.sale_date) <= ?";
            $params[] = $dateTo;
        }

        if ($companyId) {
            $sql .= " AND cs.company_id = ?";
            $params[] = $companyId;
        }

        $sql .= " ORDER BY cs.sale_date DESC, cs.id DESC";
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countFiltered($countryId = null, $dateFrom = null, $dateTo = null, $companyId = null)
    {
        global $pdo;
        $sql = "
            SELECT COUNT(*)
            FROM client_sales cs
            WHERE 1=1
        ";
        $params = [];

        if ($countryId) {
            $sql .= " AND cs.country_id = ?";
            $params[] = $countryId;
        }
        if ($dateFrom) {
            $sql .= " AND DATE(cs.sale_date) >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo) {
            $sql .= " AND DATE(cs.sale_date) <= ?";
            $params[] = $dateTo;
        }
        if ($companyId) {
            $sql .= " AND cs.company_id = ?";
            $params[] = $companyId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public static function findWithCountry($id)
    {
        global $pdo;
        $stmt = $pdo->prepare("
	        SELECT cs.*, c.name AS country_name, c.flag, cc.name AS company_name,
                   p.name AS partner_name, pa.account_label
	        FROM client_sales cs
	        JOIN countries c ON cs.country_id = c.id
            LEFT JOIN country_companies cc ON cs.company_id = cc.id
            LEFT JOIN partners p ON cs.partner_id = p.id
            LEFT JOIN partner_accounts pa ON cs.account_id = pa.id
	        WHERE cs.id = ?
	    ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function update($id, $saleDate, $countryId, $companyId, $partnerId, $accountId, $amountReceived, $currency, $receivedDate, $paymentMethod, $proofPath = null)
    {
        global $pdo;

        if ($proofPath) {
            $stmt = $pdo->prepare("
	            UPDATE client_sales
	            SET sale_date = ?, country_id = ?, company_id = ?, partner_id = ?, account_id = ?, amount_received = ?, currency = ?, received_date = ?, payment_method = ?, proof_file = ?, updated_at = NOW()
	            WHERE id = ?
	        ");
            $stmt->execute([$saleDate, $countryId, $companyId, $partnerId, $accountId, $amountReceived, $currency, $receivedDate, $paymentMethod, $proofPath, $id]);
        } else {
            $stmt = $pdo->prepare("
	            UPDATE client_sales
	            SET sale_date = ?, country_id = ?, company_id = ?, partner_id = ?, account_id = ?, amount_received = ?, currency = ?, received_date = ?, payment_method = ?, updated_at = NOW()
	            WHERE id = ?
	        ");
            $stmt->execute([$saleDate, $countryId, $companyId, $partnerId, $accountId, $amountReceived, $currency, $receivedDate, $paymentMethod, $id]);
        }
    }

    public static function delete($id)
    {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM client_sales WHERE id = ?");
        return $stmt->execute([$id]);
    }



}
