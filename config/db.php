<?php

class Database
{
    public static function getInstance()
    {
        static $pdo = null;

        if ($pdo === null) {
            $host = '127.0.0.1';
            $db   = 'chaussures_db_v2';
            $user = 'root';
            $pass = 'root';
            $port = 8889;
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];

            try {
                $pdo = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                die("❌ Erreur de connexion MySQL : " . $e->getMessage());
            }
        }

        return $pdo;
    }
}
