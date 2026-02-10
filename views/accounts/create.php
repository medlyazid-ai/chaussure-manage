<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="container mt-4">
    <h2>➕ Ajouter un compte</h2>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= e($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST" action="?route=accounts/store" class="mt-3">
        <?= csrf_field(); ?>
        <div class="mb-3">
            <label class="form-label">Partenaire</label>
            <select name="partner_id" class="form-select" required>
                <option value="">-- Choisir --</option>
                <?php foreach ($partners as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= e($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Banque</label>
            <input type="text" name="bank_name" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Libellé compte</label>
            <input type="text" name="account_label" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Numéro de compte</label>
            <input type="text" name="account_number" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="?route=accounts" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php include 'views/layout/footer.php'; ?>
