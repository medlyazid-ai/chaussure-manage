<?php include 'views/layout/header.php'; ?>

<div class="container mt-4">
    <h2>🧾 Détail de la facture #<?= $sale['id'] ?></h2>

    <div class="mb-3">
        <strong>Date de vente :</strong> <?= $sale['sale_date'] ?><br>
        <strong>Pays :</strong>
        <?php if ($sale['flag']): ?>
            <img src="uploads/flags/<?= $sale['flag'] ?>" alt="<?= $sale['country_name'] ?>" style="height: 16px;">
        <?php endif; ?>
        <?= htmlspecialchars($sale['country_name']) ?><br>
        <strong>Client :</strong> <?= htmlspecialchars($sale['customer_name']) ?><br>
        <strong>Notes :</strong> <?= nl2br(htmlspecialchars($sale['notes'])) ?><br>
        <strong>Justificatif :</strong>
        <?php if ($sale['proof_file']): ?>
            <a href="<?= $sale['proof_file'] ?>" target="_blank" class="btn btn-outline-secondary btn-sm">📎 Voir le fichier</a>
        <?php else: ?>
            <span class="text-muted">Aucun</span>
        <?php endif; ?>
    </div>

    <hr>

    <h5>📦 Produits vendus</h5>

    <?php if (empty($items)): ?>
        <div class="alert alert-warning">Aucune ligne de vente pour cette facture.</div>
    <?php else: ?>
        <table class="table table-bordered table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th>Produit</th>
                    <th>Variante</th>
                    <th>Quantité vendue</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><?= htmlspecialchars($item['size'] . ' / ' . $item['color']) ?> (SKU : <?= $item['sku'] ?>)</td>
                        <td><?= $item['quantity_sold'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="?route=client_sales" class="btn btn-secondary mt-3">⬅️ Retour</a>
</div>

<?php include 'views/layout/footer.php'; ?>
