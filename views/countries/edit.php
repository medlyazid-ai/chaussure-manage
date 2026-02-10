<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="container mt-4">
    <h2>✏️ Modifier le pays</h2>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= e($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST" action="?route=countries/update/<?= $country['id'] ?>" class="mt-3">
        <?= csrf_field(); ?>
        <div class="mb-3">
            <label class="form-label">Nom</label>
            <input type="text" name="name" class="form-control" value="<?= e($country['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Drapeau (emoji)</label>
            <input type="text" name="flag" class="form-control" value="<?= e($country['flag']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Code</label>
            <input type="text" name="code" class="form-control" value="<?= e($country['code']) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
        <a href="?route=countries" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php include 'views/layout/footer.php'; ?>
