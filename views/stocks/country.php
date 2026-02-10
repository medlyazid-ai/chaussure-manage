<?php include 'views/layout/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="?route=stocks" class="btn btn-secondary">⬅️ Retour à la vue d'ensemble</a>
        <?php if (!empty($stocks)): ?>
            <a href="?route=company_invoices/create&company_id=<?= $stocks[0]['company_id'] ?>" class="btn btn-success">
                ➕ Ajouter une facture pour cette société
            </a>
        <?php endif; ?>
    </div>

    <?php if (!empty($stocks)): ?>
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
            <h2 class="mb-2">
                <?= !empty($stocks[0]['flag']) ? '<img src="uploads/flags/' . htmlspecialchars($stocks[0]['flag']) . '" alt="" style="height: 25px;" class="me-2">' : '' ?>
                Stock société — <?= htmlspecialchars($stocks[0]['company_name']) ?>
                <small class="text-muted ms-2">(<?= htmlspecialchars($stocks[0]['country_name']) ?>)</small>
            </h2>
        </div>
    <?php else: ?>
        <h2>Stock non disponible pour cette société</h2>
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

    <div class="accordion" id="productStocks">
        <?php $i = 0; foreach ($groupedProducts as $productName => $productData): $i++; ?>
            <?php
                $totReceived = 0;
                $totSold = 0;
                $totAdj = 0;
                $totStock = 0;
                foreach ($productData['variants'] as $v) {
                    $totReceived += (int)$v['total_received'];
                    $totSold += (int)$v['total_sold'];
                    $totAdj += (int)$v['manual_adjustment'];
                    $totStock += (int)$v['current_stock'];
                }
            ?>
            <div class="accordion-item mb-3 border-0 shadow-sm">
                <h2 class="accordion-header" id="heading<?= $i ?>">
                    <button class="accordion-button <?= $i === 1 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $i ?>" aria-expanded="<?= $i === 1 ? 'true' : 'false' ?>">
                        <div class="d-flex flex-wrap align-items-center justify-content-between w-100">
                            <div class="fw-semibold">👟 <?= htmlspecialchars($productName) ?></div>
                            <div class="d-flex flex-wrap gap-3 small text-muted">
                                <span>Reçu: <strong class="text-success"><?= $totReceived ?></strong></span>
                                <span>Vendu: <strong class="text-info"><?= $totSold ?></strong></span>
                                <span>Ajusté: <strong class="text-warning"><?= $totAdj ?></strong></span>
                                <span>Stock: <strong class="text-primary"><?= $totStock ?></strong></span>
                            </div>
                        </div>
                    </button>
                </h2>
                <div id="collapse<?= $i ?>" class="accordion-collapse collapse <?= $i === 1 ? 'show' : '' ?>" data-bs-parent="#productStocks">
                    <div class="accordion-body p-2">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 10%">Taille</th>
                                    <th style="width: 15%">Couleur</th>
                                    <th class="text-center" style="width: 10%">Reçu</th>
                                    <th class="text-center" style="width: 10%">Ajusté</th>
                                    <th class="text-center" style="width: 10%">Vendu</th>
                                    <th class="text-center" style="width: 10%">Stock</th>
                                    <th class="text-center" style="width: 35%">Action</th>
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
                                                <button class="btn btn-link btn-sm p-0 text-decoration-none" onclick="toggleAdjustments('adj-<?= $stock['company_id'] ?>-<?= $stock['variant_id'] ?>')">📝</button>
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
                                                <?= csrf_field(); ?>
                                                <input type="hidden" name="company_id" value="<?= $stock['company_id'] ?>">
                                                <input type="hidden" name="variant_id" value="<?= $stock['variant_id'] ?>">
                                                <input type="number" name="adjusted_quantity" class="form-control form-control-sm" placeholder="ex: -2" required style="width: 80px;">
                                                <input type="text" name="reason" class="form-control form-control-sm" placeholder="Raison" required style="width: 140px;">
                                                <button class="btn btn-warning btn-sm" type="submit">✔</button>
                                            </form>
                                        </td>
                                    </tr>

                                    <?php if (!empty($stock['adjustments'])): ?>
                                        <tr id="adj-<?= $stock['company_id'] ?>-<?= $stock['variant_id'] ?>" class="bg-light" style="display: none;">
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
                                                                    <?= csrf_field(); ?>
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
            </div>
        <?php endforeach ?>
    </div>
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
