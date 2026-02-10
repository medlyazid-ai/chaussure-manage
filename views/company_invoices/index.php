<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>🧾 Factures sociétés</h2>
    <a href="?route=company_invoices/create" class="btn btn-primary">➕ Nouvelle facture</a>
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
                <th>Société</th>
                <th>Pays</th>
                <th>Date</th>
                <th>Montant</th>
                <th>Payé</th>
                <th>Reste</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($invoices as $inv): ?>
                <?php $remaining = max($inv['amount_due'] - $inv['total_paid'], 0); ?>
                <tr>
                    <td>#<?= $inv['id'] ?></td>
                    <td><?= e($inv['company_name']) ?></td>
                    <td><?= e($inv['country_name']) ?></td>
                    <td><?= e($inv['invoice_date']) ?></td>
                    <td><?= number_format($inv['amount_due'], 2) ?> MAD</td>
                    <td><?= number_format($inv['total_paid'], 2) ?> MAD</td>
                    <td><?= number_format($remaining, 2) ?> MAD</td>
                    <td class="text-nowrap">
                        <a href="?route=company_invoices/show/<?= $inv['id'] ?>" class="btn btn-sm btn-outline-primary">Voir</a>
                        <form method="POST" action="?route=company_invoices/delete/<?= $inv['id'] ?>" class="d-inline">
                            <?= csrf_field(); ?>
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette facture ?')">🗑️</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?= render_pagination($page ?? 1, $totalPages ?? 1, array_merge($_GET, ['route' => 'company_invoices'])) ?>

<?php include 'views/layout/footer.php'; ?>
