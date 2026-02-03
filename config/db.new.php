<?php

/**
 * Database Connection Configuration
 * 
 * This file provides a singleton PDO instance for database connections.
 * Credentials are loaded from .env file for security.
 * 
 * Setup Instructions:
 * 1. Copy .env.example to .env
 * 2. Edit .env with your database credentials
 * 3. Never commit .env to version control
 */

class Database
{
    public static function getInstance()
    {
        static $pdo = null;

        if ($pdo === null) {
            // Load environment variables from .env file
            $envFile = __DIR__ . '/../.env';
            
            if (file_exists($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    // Skip comments
                    if (strpos(trim($line), '#') === 0) {
                        continue;
                    }
                    
                    // Parse key=value pairs
                    if (strpos($line, '=') !== false) {
                        list($name, $value) = explode('=', $line, 2);
                        $_ENV[trim($name)] = trim($value);
                    }
                }
            } else {
                // Fallback to default values if .env doesn't exist
                // This allows the old config to still work temporarily
                error_log("Warning: .env file not found. Using default configuration.");
            }
            
            // Get database configuration from environment variables
            // Fallback to defaults if not set (for backward compatibility)
            $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
            $db   = $_ENV['DB_NAME'] ?? 'quwaejeq_chaussure_manage_db';
            $user = $_ENV['DB_USER'] ?? 'quwaejeq_admin';
            $pass = $_ENV['DB_PASSWORD'] ?? 'DJq[*:q5Ia';
            $port = $_ENV['DB_PORT'] ?? 3306;
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                $pdo = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Log the error (in production, don't show details to users)
                error_log("Database connection failed: " . $e->getMessage());
                
                // Show user-friendly error
                if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
                    die("❌ Database connection error: " . $e->getMessage());
                } else {
                    die("❌ Unable to connect to the database. Please contact the administrator.");
                }
            }
        }

        return $pdo;
    }
}
