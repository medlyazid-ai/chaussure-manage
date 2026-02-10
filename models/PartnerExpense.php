<?php

class PartnerExpense
{
    public static function all()
    {
        global $pdo;
        $stmt = $pdo->query("
            SELECT pe.*, p.name AS partner_name, pa.account_label
            FROM partner_expenses pe
            JOIN partners p ON pe.partner_id = p.id
            LEFT JOIN partner_accounts pa ON pe.account_id = pa.id
            ORDER BY pe.expense_date DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($partnerId, $accountId, $amount, $currency, $expenseDate, $category = '', $notes = '', $proofFile = null)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO partner_expenses (partner_id, account_id, amount, currency, expense_date, category, notes, proof_file)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$partnerId, $accountId, $amount, $currency, $expenseDate, $category, $notes, $proofFile]);
        return $pdo->lastInsertId();
    }
}
