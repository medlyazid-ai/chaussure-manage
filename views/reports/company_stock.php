<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>📦 Stock par société</h2>
        <form method="GET" class="row g-2">
            <input type="hidden" name="route" value="reports/company_stock">
            <div class="col-auto">
                <select name="company_id" class="form-select">
                    <option value="">-- Toutes les sociétés --</option>
                    <?php foreach ($companies as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= (isset($_GET['company_id']) && $_GET['company_id'] == $c['id']) ? 'selected' : '' ?>>
                            <?= e($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-secondary">Filtrer</button>
            </div>
        </form>
    </div>

    <?php if (empty($stocks)): ?>
        <div class="alert alert-info">Aucun stock trouvé.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Société</th>
                        <th>Pays</th>
                        <th>Produit</th>
                        <th>Taille</th>
                        <th>Couleur</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stocks as $s): ?>
                        <tr>
                            <td><?= e($s['company_name']) ?></td>
                            <td><?= e($s['country_name']) ?></td>
                            <td><?= e($s['product_name']) ?></td>
                            <td><?= e($s['size']) ?></td>
                            <td><?= e($s['color']) ?></td>
                            <td><span class="badge bg-secondary"><?= (int)$s['current_stock'] ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'views/layout/footer.php'; ?>
