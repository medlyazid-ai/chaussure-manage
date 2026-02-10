<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>✏️ Modifier le fournisseur</h2>
    <a href="?route=suppliers" class="btn btn-outline-secondary">← Retour à la liste</a>
</div>

<form method="POST" action="?route=suppliers/update/<?= $supplier['id'] ?>" class="row g-3">
    <?= csrf_field(); ?>

    <div class="col-md-6">
        <label for="name" class="form-label">Nom du fournisseur</label>
        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($supplier['name']) ?>" required>
    </div>

    <div class="col-md-6">
        <label for="contact_name" class="form-label">Nom du contact</label>
        <input type="text" class="form-control" name="contact_name" value="<?= htmlspecialchars($supplier['contact_name']) ?>">
    </div>

    <div class="col-md-6">
        <label for="phone" class="form-label">Téléphone</label>
        <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($supplier['phone']) ?>">
    </div>

    <div class="col-md-6">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($supplier['email']) ?>">
    </div>

    <div class="col-12">
        <label for="address" class="form-label">Adresse</label>
        <textarea class="form-control" name="address" rows="3"><?= htmlspecialchars($supplier['address']) ?></textarea>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="?route=suppliers" class="btn btn-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
    </div>

</form>

<?php include 'views/layout/footer.php'; ?>
