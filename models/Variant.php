<?php

class Variant
{
    public static function findByProduct($product_id)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM variants WHERE product_id = ?");
        $stmt->execute([$product_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
