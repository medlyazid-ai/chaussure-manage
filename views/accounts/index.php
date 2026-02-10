<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>🏦 Comptes partenaires</h2>
    <a href="?route=accounts/create" class="btn btn-primary">➕ Ajouter un compte</a>
</div>

<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= e($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= e($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Partenaire</th>
                <th>Banque</th>
                <th>Libellé</th>
                <th>Compte</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($accounts as $a): ?>
                <tr>
                    <td>#<?= $a['id'] ?></td>
                    <td><?= e($a['partner_name']) ?></td>
                    <td><?= e($a['bank_name']) ?></td>
                    <td><?= e($a['account_label']) ?></td>
                    <td><?= e($a['account_number']) ?></td>
                    <td class="text-nowrap">
                        <a href="?route=accounts/edit/<?= $a['id'] ?>" class="btn btn-sm btn-warning">✏️</a>
                        <form method="POST" action="?route=accounts/delete/<?= $a['id'] ?>" class="d-inline">
                            <?= csrf_field(); ?>
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce compte ?')">🗑️</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'views/layout/footer.php'; ?>
