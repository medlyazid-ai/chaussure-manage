<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>🏢 Dashboard sociétés</h2>
        <form method="GET" class="row g-2">
            <input type="hidden" name="route" value="reports/company_dashboard">
            <div class="col-auto">
                <input type="date" name="date_from" class="form-control" value="<?= isset($_GET['date_from']) ? e($_GET['date_from']) : '' ?>">
            </div>
            <div class="col-auto">
                <input type="date" name="date_to" class="form-control" value="<?= isset($_GET['date_to']) ? e($_GET['date_to']) : '' ?>">
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-secondary">Filtrer</button>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Société</th>
                    <th>Pays</th>
                    <th>Total facturé</th>
                    <th>Total encaissé</th>
                    <th>Reste à encaisser</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <?php $remaining = max($r['total_invoiced'] - $r['total_paid'], 0); ?>
                    <tr>
                        <td><?= e($r['company_name']) ?></td>
                        <td><?= e($r['country_name']) ?></td>
                        <td><?= number_format($r['total_invoiced'], 2) ?> MAD</td>
                        <td><?= number_format($r['total_paid'], 2) ?> MAD</td>
                        <td>
                            <span class="badge <?= $remaining > 0 ? 'bg-warning text-dark' : 'bg-success' ?>">
                                <?= number_format($remaining, 2) ?> MAD
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>
