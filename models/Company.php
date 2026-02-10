<?php

class Company
{
    public static function all()
    {
        global $pdo;
        $stmt = $pdo->query("
            SELECT cc.*, c.name AS country_name, c.flag
            FROM country_companies cc
            JOIN countries c ON cc.country_id = c.id
            ORDER BY c.name, cc.name
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function byCountry($countryId)
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT * FROM country_companies
            WHERE country_id = ?
            ORDER BY name
        ");
        $stmt->execute([$countryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find($id)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM country_companies WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($countryId, $name, $contact = '', $address = '', $notes = '')
    {
        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO country_companies (country_id, name, contact, address, notes)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$countryId, $name, $contact, $address, $notes]);
        return $pdo->lastInsertId();
    }

    public static function update($id, $countryId, $name, $contact = '', $address = '', $notes = '')
    {
        global $pdo;
        $stmt = $pdo->prepare("
            UPDATE country_companies
            SET country_id = ?, name = ?, contact = ?, address = ?, notes = ?
            WHERE id = ?
        ");
        return $stmt->execute([$countryId, $name, $contact, $address, $notes, $id]);
    }

    public static function delete($id)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE company_id = ?");
        $stmt->execute([$id]);
        $usedOrders = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM client_sales WHERE company_id = ?");
        $stmt->execute([$id]);
        $usedSales = (int)$stmt->fetchColumn();

        if ($usedOrders || $usedSales) {
            throw new Exception("Cette société est utilisée. Suppression impossible.");
        }

        $stmt = $pdo->prepare("DELETE FROM country_companies WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
