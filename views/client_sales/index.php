<?php include 'views/layout/header.php'; ?>

<div class="container mt-4">
    <h2>📄 Liste des ventes client (factures)</h2>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (empty($sales)): ?>
        <div class="alert alert-warning">Aucune vente enregistrée pour le moment.</div>
    <?php else: ?>
        <table class="table table-bordered table-hover table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Pays</th>
                    <th>Client</th>
                    <th>Notes</th>
                    <th>Justificatif</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales as $sale): ?>
                    <tr>
                        <td><?= $sale['id'] ?></td>
                        <td><?= $sale['sale_date'] ?></td>
                        <td>
                            <?php if (!empty($sale['flag'])): ?>
                                <img src="uploads/flags/<?= $sale['flag'] ?>" alt="<?= $sale['country_name'] ?>" style="height: 16px;" class="me-1">
                            <?php endif; ?>
                            <?= htmlspecialchars($sale['country_name']) ?>
                        </td>
                        <td><?= htmlspecialchars($sale['customer_name']) ?></td>
                        <td><?= nl2br(htmlspecialchars($sale['notes'])) ?></td>
                        <td>
                            <?php if ($sale['proof_file']): ?>
                                <a href="<?= $sale['proof_file'] ?>" target="_blank" class="btn btn-outline-secondary btn-sm">📎 Voir</a>
                            <?php else: ?>
                                <span class="text-muted">Aucun</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?route=client_sales/show/<?= $sale['id'] ?>" class="btn btn-sm btn-primary">👁️ Détails</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'views/layout/footer.php'; ?>
