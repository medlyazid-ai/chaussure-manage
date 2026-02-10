<?php

class Partner
{
    public static function all()
    {
        global $pdo;
        $stmt = $pdo->query("SELECT * FROM partners ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find($id)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM partners WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($name)
    {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO partners (name) VALUES (?)");
        $stmt->execute([$name]);
        return $pdo->lastInsertId();
    }

    public static function update($id, $name)
    {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE partners SET name = ? WHERE id = ?");
        return $stmt->execute([$name, $id]);
    }

    public static function delete($id)
    {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM partners WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
