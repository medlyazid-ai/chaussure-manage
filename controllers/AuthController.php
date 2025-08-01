<?php
require_once 'models/User.php';
require_once 'config/config.php';

// Affiche le formulaire de connexion
function loginForm() {
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (isset($_SESSION['user'])) {
        header("Location: " . BASE_URL . "dashboard");
        exit;
    }
    
    include 'views/auth/login.php';
}

// Affiche le formulaire d’inscription
function registerForm() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    include 'views/auth/register.php';
}

// Connexion utilisateur
function login() {
    if (session_status() === PHP_SESSION_NONE) session_start();

    $user = User::findByEmail($_POST['email']);

    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user'] = $user;
        $_SESSION['LAST_ACTIVITY'] = time(); // Pour timeout automatique

        header('Location: ' . BASE_URL . 'dashboard');
        exit;
    } else {
        $_SESSION['error'] = "Identifiants invalides.";
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
}

// Inscription utilisateur
function register() {
    if (session_status() === PHP_SESSION_NONE) session_start();

    User::create($_POST['name'], $_POST['email'], $_POST['password']);
    $_SESSION['success'] = "Compte créé avec succès. Vous pouvez vous connecter.";
    header('Location: ' . BASE_URL . 'login');
    exit;
}

// Déconnexion
function logout() {
    if (session_status() === PHP_SESSION_NONE) session_start();

    session_unset();
    session_destroy();
    header("Location: " . BASE_URL . "login");
    exit;
}
