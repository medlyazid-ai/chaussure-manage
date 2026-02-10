<?php

class Country
{
    public static function all()
    {
        global $pdo;
        $stmt = $pdo->query("SELECT * FROM countries ORDER BY name");
        return $stmt->fetchAll();
    }

    public static function getById($id)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM countries WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($name, $flag = '', $code = '')
    {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO countries (name, flag, code) VALUES (?, ?, ?)");
        $stmt->execute([$name, $flag, $code]);
        return $pdo->lastInsertId();
    }

    public static function update($id, $name, $flag = '', $code = '')
    {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE countries SET name = ?, flag = ?, code = ? WHERE id = ?");
        return $stmt->execute([$name, $flag, $code, $id]);
    }

    public static function delete($id)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE country_id = ?");
        $stmt->execute([$id]);
        $usedOrders = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM client_sales WHERE country_id = ?");
        $stmt->execute([$id]);
        $usedSales = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM country_companies WHERE country_id = ?");
        $stmt->execute([$id]);
        $usedCompanies = (int)$stmt->fetchColumn();

        if ($usedOrders || $usedSales || $usedCompanies) {
            throw new Exception("Ce pays est utilisé. Suppression impossible.");
        }

        $stmt = $pdo->prepare("DELETE FROM countries WHERE id = ?");
        return $stmt->execute([$id]);
    }

}
