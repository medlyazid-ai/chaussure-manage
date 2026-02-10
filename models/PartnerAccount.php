<?php

class PartnerAccount
{
    public static function all()
    {
        global $pdo;
        $stmt = $pdo->query("
            SELECT pa.*, p.name AS partner_name
            FROM partner_accounts pa
            JOIN partners p ON pa.partner_id = p.id
            ORDER BY p.name, pa.account_label
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function byPartner($partnerId)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM partner_accounts WHERE partner_id = ? ORDER BY account_label");
        $stmt->execute([$partnerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find($id)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM partner_accounts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($partnerId, $bankName, $accountLabel, $accountNumber = '')
    {
        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO partner_accounts (partner_id, bank_name, account_label, account_number)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$partnerId, $bankName, $accountLabel, $accountNumber]);
        return $pdo->lastInsertId();
    }

    public static function update($id, $partnerId, $bankName, $accountLabel, $accountNumber = '')
    {
        global $pdo;
        $stmt = $pdo->prepare("
            UPDATE partner_accounts
            SET partner_id = ?, bank_name = ?, account_label = ?, account_number = ?
            WHERE id = ?
        ");
        return $stmt->execute([$partnerId, $bankName, $accountLabel, $accountNumber, $id]);
    }

    public static function delete($id)
    {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM partner_accounts WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
