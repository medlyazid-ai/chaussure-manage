<?php
session_start();
require 'db.php';

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
