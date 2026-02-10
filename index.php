<?php
require 'utils.php';
start_session_if_needed();
require 'config/db.php';

$pdo = Database::getInstance(); // ← instanciation nécessaire pour tous les modèles

// Redirection intelligente si pas de route définie
if (!isset($_GET['route']) || $_GET['route'] === '') {
    if (!isset($_SESSION['user'])) {
        header('Location: ?route=login');
    } else {
        header('Location: ?route=dashboard');
    }
    exit;
}

require 'routes.php';
