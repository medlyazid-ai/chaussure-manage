<?php

class CompanyPayment
{
    public static function create($invoiceId, $partnerId, $accountId, $amount, $currency, $paymentDate, $method, $notes = '', $proofFile = null)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO company_payments (invoice_id, partner_id, account_id, amount, currency, payment_date, method, notes, proof_file)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$invoiceId, $partnerId, $accountId, $amount, $currency, $paymentDate, $method, $notes, $proofFile]);
        return $pdo->lastInsertId();
    }

    public static function byInvoice($invoiceId)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT cp.*, p.name AS partner_name, pa.account_label
            FROM company_payments cp
            JOIN partners p ON cp.partner_id = p.id
            LEFT JOIN partner_accounts pa ON cp.account_id = pa.id
            WHERE cp.invoice_id = ?
            ORDER BY cp.payment_date DESC
        ");
        $stmt->execute([$invoiceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function totalPaid($invoiceId)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM company_payments WHERE invoice_id = ?");
        $stmt->execute([$invoiceId]);
        return (float)$stmt->fetchColumn();
    }
}
