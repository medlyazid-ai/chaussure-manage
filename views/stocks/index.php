<?php include 'views/layout/header.php'; ?>

<div class="container mt-4">
    <h2>📦 État du stock réel par pays, produit et variante</h2>
    <p class="text-muted">Vue dynamique basée sur les envois livrés. Vous pouvez corriger les écarts (produits cassés, perdus...)</p>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php
    $grouped = [];
foreach ($stocks as $stock) {
    $grouped[$stock['country_name']][] = $stock;
}
?>

    <div class="row">
        <?php foreach ($groupedStocks as $country => $data): ?>
            <div class="col-12 col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex align-items-center">
                        <?php if (!empty($data['flag'])): ?>
                            <img src="uploads/flags/<?= $data['flag'] ?>" alt="<?= $country ?>" style="height: 20px;" class="me-2">
                        <?php endif; ?>
                        <h5 class="mb-0"><?= htmlspecialchars($country) ?></h5>
                    </div>
                    <div class="card-body p-2">
                        <?php foreach ($data['products'] as $productName => $productData): ?>
                            <h6 class="text-muted mt-3">👟 <?= htmlspecialchars($productName) ?></h6>
                            <table class="table table-sm table-bordered align-middle mb-4">
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
                                            <td class="text-center text-info"><?= $stock['total_sold'] ?? 0 ?></td>
                                            <td class="text-center fw-bold">
                                                <?= $stock['current_stock'] ?>
                                                <?php if ($stock['current_stock'] <= 5): ?>
                                                    <span class="badge bg-danger ms-1">⚠️</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="POST" action="?route=stocks/adjust" class="d-flex flex-wrap align-items-center justify-content-center gap-1">
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
                                                        <h6 class="text-primary mb-2">📋 Détails des ajustements :</h6>
                                                        <div class="list-group list-group-flush small">
                                                            <?php foreach ($stock['adjustments'] as $adj): ?>
                                                                <div class="list-group-item d-flex justify-content-between align-items-start flex-wrap">
                                                                    <div class="me-2">
                                                                        <strong><?= htmlspecialchars($adj['adjusted_quantity']) ?></strong>
                                                                        (<?= htmlspecialchars($adj['reason']) ?>)<br>
                                                                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($adj['created_at'])) ?></small>
                                                                    </div>
                                                                    <div>
                                                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteAdj<?= $adj['id'] ?>">🗑️</button>
                                                                    </div>
                                                                    <!-- Modal ici -->
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
                        <?php endforeach ?>
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
