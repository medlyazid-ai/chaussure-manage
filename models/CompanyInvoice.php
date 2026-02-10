<?php

class CompanyInvoice
{
    public static function create($companyId, $invoiceDate, $amountDue, $notes = '', $proofFile = null)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO company_invoices (company_id, invoice_date, amount_due, notes, proof_file)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$companyId, $invoiceDate, $amountDue, $notes, $proofFile]);
        return $pdo->lastInsertId();
    }

    public static function allWithCompany($limit = null, $offset = null)
    {
        global $pdo;
        $sql = "
            SELECT ci.*, cc.name AS company_name, c.name AS country_name,
                   (SELECT COALESCE(SUM(cp.amount), 0) FROM company_payments cp WHERE cp.invoice_id = ci.id) AS total_paid
            FROM company_invoices ci
            JOIN country_companies cc ON ci.company_id = cc.id
            JOIN countries c ON cc.country_id = c.id
            ORDER BY ci.invoice_date DESC, ci.id DESC
        ";
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countAll()
    {
        global $pdo;
        $stmt = $pdo->query("SELECT COUNT(*) FROM company_invoices");
        return (int)$stmt->fetchColumn();
    }

    public static function findWithCompany($id)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT ci.*, cc.name AS company_name, c.name AS country_name
            FROM company_invoices ci
            JOIN country_companies cc ON ci.company_id = cc.id
            JOIN countries c ON cc.country_id = c.id
            WHERE ci.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function delete($id)
    {
        global $pdo;
        $pdo->prepare("DELETE FROM company_invoice_items WHERE invoice_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM company_payments WHERE invoice_id = ?")->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM company_invoices WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
