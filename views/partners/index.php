<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>🤝 Partenaires</h2>
    <a href="?route=partners/create" class="btn btn-primary">➕ Ajouter un partenaire</a>
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
                <th>Nom</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($partners as $p): ?>
                <tr>
                    <td>#<?= $p['id'] ?></td>
                    <td><?= e($p['name']) ?></td>
                    <td class="text-nowrap">
                        <a href="?route=partners/dashboard&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">📊</a>
                        <a href="?route=partners/edit/<?= $p['id'] ?>" class="btn btn-sm btn-warning">✏️</a>
                        <form method="POST" action="?route=partners/delete/<?= $p['id'] ?>" class="d-inline">
                            <?= csrf_field(); ?>
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce partenaire ?')">🗑️</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'views/layout/footer.php'; ?>
