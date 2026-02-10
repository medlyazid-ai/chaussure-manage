<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>📊 Rapport partenaires</h2>
        <form method="GET" class="row g-2">
            <input type="hidden" name="route" value="reports/partners">
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
                    <th>Partenaire</th>
                    <th>Encaissements MAD</th>
                    <th>Encaissements USD</th>
                    <th>Charges MAD</th>
                    <th>Charges USD</th>
                    <th>Solde MAD</th>
                    <th>Solde USD</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $total = [
                        'received' => ['MAD' => 0, 'USD' => 0],
                        'charges' => ['MAD' => 0, 'USD' => 0],
                    ];
                ?>
                <?php foreach ($rows as $r): ?>
                    <?php
                        $recMad = $r['received']['MAD'] ?? 0;
                        $recUsd = $r['received']['USD'] ?? 0;
                        $chgMad = $r['charges']['MAD'] ?? 0;
                        $chgUsd = $r['charges']['USD'] ?? 0;
                        $netMad = $recMad - $chgMad;
                        $netUsd = $recUsd - $chgUsd;
                        $total['received']['MAD'] += $recMad;
                        $total['received']['USD'] += $recUsd;
                        $total['charges']['MAD'] += $chgMad;
                        $total['charges']['USD'] += $chgUsd;
                    ?>
                    <tr>
                        <td><?= e($r['name']) ?></td>
                        <td><?= number_format($recMad, 2) ?></td>
                        <td><?= number_format($recUsd, 2) ?></td>
                        <td><?= number_format($chgMad, 2) ?></td>
                        <td><?= number_format($chgUsd, 2) ?></td>
                        <td><span class="badge <?= $netMad >= 0 ? 'bg-success' : 'bg-danger' ?>"><?= number_format($netMad, 2) ?></span></td>
                        <td><span class="badge <?= $netUsd >= 0 ? 'bg-success' : 'bg-danger' ?>"><?= number_format($netUsd, 2) ?></span></td>
                        <td>
                            <a href="?route=partners/dashboard&id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">📊</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th>Total</th>
                    <th><?= number_format($total['received']['MAD'], 2) ?></th>
                    <th><?= number_format($total['received']['USD'], 2) ?></th>
                    <th><?= number_format($total['charges']['MAD'], 2) ?></th>
                    <th><?= number_format($total['charges']['USD'], 2) ?></th>
                    <th><?= number_format($total['received']['MAD'] - $total['charges']['MAD'], 2) ?></th>
                    <th><?= number_format($total['received']['USD'] - $total['charges']['USD'], 2) ?></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>
