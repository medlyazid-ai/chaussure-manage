<?php include 'views/layout/header.php'; ?>

<div class="container mt-4">
    <a href="?route=stocks" class="btn btn-secondary mb-3">⬅️ Retour à la vue d'ensemble</a>

    <?php if (!empty($stocks)): ?>
        <h2>
            <?= !empty($stocks[0]['flag']) ? '<img src="uploads/flags/' . htmlspecialchars($stocks[0]['flag']) . '" alt="" style="height: 25px;" class="me-2">' : '' ?>
            Stock détaillé - <?= htmlspecialchars($stocks[0]['country_name']) ?>
        </h2>
    <?php else: ?>
        <h2>Stock non disponible pour ce pays</h2>
    <?php endif; ?>

    <?php
    // Regrouper par produit
    $groupedProducts = [];
foreach ($stocks as $stock) {
    $product = $stock['product_name'];
    $variantKey = "{$stock['size']}|{$stock['color']}";
    $groupedProducts[$product]['variants'][$variantKey] = $stock;
}
?>

    <?php foreach ($groupedProducts as $productName => $productData): ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                👟 <?= htmlspecialchars($productName) ?>
            </div>
            <div class="card-body p-2">
                <table class="table table-sm table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Taille</th>
                            <th>Couleur</th>
                            <th class="text-center">Reçu</th>
                            <th class="text-center">Ajusté</th>
                            <th class="text-center">Vendu</th>
                            <th class="text-center">Stock</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productData['variants'] as $variantKey => $stock): ?>
                            <tr>
                                <td><?= htmlspecialchars($stock['size']) ?></td>
                                <td><?= htmlspecialchars($stock['color']) ?></td>
                                <td class="text-center text-success"><?= $stock['total_received'] ?></td>
                                <td class="text-center text-warning">
                                    <?= $stock['manual_adjustment'] ?>
                                    <?php if (!empty($stock['adjustments'])): ?>
                                        <button class="btn btn-link btn-sm p-0 text-decoration-none" onclick="toggleAdjustments('adj-<?= $stock['country_id'] ?>-<?= $stock['variant_id'] ?>')">📝</button>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center text-info"><?= $stock['total_sold'] ?></td>
                                <td class="text-center fw-bold">
                                    <?= $stock['current_stock'] ?>
                                    <?php if ($stock['current_stock'] <= 5): ?>
                                        <span class="badge bg-danger ms-1">⚠️</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="?route=stocks/adjust" class="d-flex flex-wrap align-items-center gap-1 justify-content-center">
                                        <input type="hidden" name="country_id" value="<?= $stock['country_id'] ?>">
                                        <input type="hidden" name="variant_id" value="<?= $stock['variant_id'] ?>">
                                        <input type="number" name="adjusted_quantity" class="form-control form-control-sm" placeholder="ex: -2" required style="width: 80px;">
                                        <input type="text" name="reason" class="form-control form-control-sm" placeholder="Raison" required style="width: 140px;">
                                        <button class="btn btn-warning btn-sm" type="submit">✔</button>
                                    </form>
                                </td>
                            </tr>

                            <?php if (!empty($stock['adjustments'])): ?>
                                <tr id="adj-<?= $stock['country_id'] ?>-<?= $stock['variant_id'] ?>" class="bg-light" style="display: none;">
                                    <td colspan="7">
                                        <div class="p-2 border rounded bg-white">
                                            <h6 class="text-primary">📋 Détails des ajustements :</h6>
                                            <div class="list-group list-group-flush small">
                                                <?php foreach ($stock['adjustments'] as $adj): ?>
                                                    <div class="list-group-item d-flex justify-content-between align-items-start flex-wrap">
                                                        <div class="me-2">
                                                            <strong><?= htmlspecialchars($adj['adjusted_quantity']) ?></strong>
                                                            (<?= htmlspecialchars($adj['reason']) ?>)<br>
                                                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($adj['created_at'])) ?></small>
                                                        </div>
                                                        <form method="POST" action="?route=stocks/delete-adjustment" onsubmit="return confirm('Confirmer la suppression ?')">
                                                            <input type="hidden" name="adjustment_id" value="<?= $adj['id'] ?>">
                                                            <button class="btn btn-sm btn-outline-danger">🗑️</button>
                                                        </form>
                                                    </div>
                                                <?php endforeach ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif ?>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach ?>
</div>

<script>
function toggleAdjustments(id) {
    const row = document.getElementById(id);
    if (row) {
        row.style.display = row.style.display === "none" ? "table-row" : "none";
    }
}
</script>

<?php include 'views/layout/footer.php'; ?>
