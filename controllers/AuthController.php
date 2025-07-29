<?php
require_once 'models/User.php';

function loginForm() {
    include 'views/auth/login.php';
}

function registerForm() {
    include 'views/auth/register.php';
}

function login() {
    session_start();
    $user = User::findByEmail($_POST['email']);
    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user'] = $user;
        header('Location: /dashboard');
    } else {
        echo "Identifiants invalides.";
    }
}

function register() {
    User::create($_POST['name'], $_POST['email'], $_POST['password']);
    header('Location: /login');
}

function logout() {
    session_start();
    session_destroy();
    header('Location: /login');
}
