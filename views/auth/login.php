<?php
require_once 'utils.php';
start_session_if_needed();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow rounded-4 p-4">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">Connexion</h3>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error'];
                            unset($_SESSION['error']); ?></div>
                        <?php endif; ?>

                        <?php if (isset($_GET['expired'])): ?>
                            <div class="alert alert-warning">⏳ Session expirée. Veuillez vous reconnecter.</div>
                        <?php endif; ?>

                        <form method="POST" action="?route=login">
                            <div class="mb-3">
                                <label for="email" class="form-label">Adresse e-mail</label>
                                <input name="email" type="email" class="form-control" id="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <input name="password" type="password" class="form-control" id="password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Se connecter</button>
                            </div>
                        </form>
                    </div>
                </div>
                <p class="text-center mt-4 text-muted small">© 2025 ChaussuresApp</p>
            </div>
        </div>
    </div>
</body>
</html>
