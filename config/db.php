<?php

class Database
{
    public static function getInstance()
    {
        static $pdo = null;

        if ($pdo === null) {
            
            $host = '127.0.0.1';
            $db   = 'quwaejeq_chaussure_manage_db';
            $user = 'quwaejeq_admin';
            $pass = 'DJq[*:q5Ia';
            $port = 3306;
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
