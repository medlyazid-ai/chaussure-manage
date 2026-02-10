<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="container mt-4">
    <h2>✏️ Modifier la société</h2>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= e($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST" action="?route=companies/update/<?= $company['id'] ?>" class="mt-3">
        <?= csrf_field(); ?>
        <div class="mb-3">
            <label class="form-label">Pays</label>
            <select name="country_id" class="form-select" required>
                <option value="">-- Choisir --</option>
                <?php foreach ($countries as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $c['id'] == $company['country_id'] ? 'selected' : '' ?>>
                        <?= e($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Nom</label>
            <input type="text" name="name" class="form-control" value="<?= e($company['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Contact</label>
            <input type="text" name="contact" class="form-control" value="<?= e($company['contact']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Adresse</label>
            <input type="text" name="address" class="form-control" value="<?= e($company['address']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2"><?= e($company['notes']) ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
        <a href="?route=companies" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php include 'views/layout/footer.php'; ?>
