<?php

class Country {
    public static function all() {
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

}

