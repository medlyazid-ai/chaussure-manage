<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>🌍 Pays</h2>
    <a href="?route=countries/create" class="btn btn-primary">➕ Ajouter un pays</a>
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
                <th>Pays</th>
                <th>Code</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($countries as $c): ?>
                <tr>
                    <td>#<?= $c['id'] ?></td>
                    <td><?= e($c['flag']) ?> <?= e($c['name']) ?></td>
                    <td><?= e($c['code']) ?></td>
                    <td class="text-nowrap">
                        <a href="?route=countries/edit/<?= $c['id'] ?>" class="btn btn-sm btn-warning">✏️</a>
                        <form method="POST" action="?route=countries/delete/<?= $c['id'] ?>" class="d-inline">
                            <?= csrf_field(); ?>
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce pays ?')">🗑️</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'views/layout/footer.php'; ?>
