<?php
require_once 'config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$timeout = 3600;

if (!isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "login");
    exit;
}

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: " . BASE_URL . "login&expired=1");
    exit;
}

$_SESSION['LAST_ACTIVITY'] = time();
