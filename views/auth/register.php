<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow rounded-4 p-4">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">Créer un compte</h3>
                        <form method="POST" action="?route=register">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom</label>
                                <input name="name" type="text" class="form-control" id="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Adresse e-mail</label>
                                <input name="email" type="email" class="form-control" id="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <input name="password" type="password" class="form-control" id="password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">S’inscrire</button>
                            </div>
                        </form>
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>

                        <div class="text-center mt-3">
                            <a href="?route=login">Déjà inscrit ? Se connecter</a>
                        </div>
                    </div>
                </div>
                <p class="text-center mt-4 text-muted small">© 2025 ChaussuresApp</p>
            </div>
        </div>
    </div>
</body>
</html>
