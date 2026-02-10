<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>🔖 Variantes</h2>
</div>

<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= e($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= e($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <h5 class="mb-3">➕ Ajouter une variante</h5>
        <form method="POST" action="?route=variants/store" class="row g-2">
            <?= csrf_field(); ?>
            <div class="col-md-4">
                <select name="product_id" class="form-select" required>
                    <option value="">-- Produit --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= e($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="text" name="size" class="form-control" placeholder="Taille" required>
            </div>
            <div class="col-md-3">
                <input type="text" name="color" class="form-control" placeholder="Couleur" required>
            </div>
            <div class="col-md-2">
                <input type="text" name="sku" class="form-control" placeholder="SKU (optionnel)">
            </div>
            <div class="col-md-1 d-grid">
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<form method="GET" class="row g-2 mb-3">
    <input type="hidden" name="route" value="variants">
    <div class="col-md-4">
        <select name="product_id" class="form-select">
            <option value="">-- Tous les produits --</option>
            <?php foreach ($products as $p): ?>
                <option value="<?= $p['id'] ?>" <?= (isset($_GET['product_id']) && $_GET['product_id'] == $p['id']) ? 'selected' : '' ?>>
                    <?= e($p['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <input type="text" name="search" class="form-control" placeholder="Produit ou SKU" value="<?= isset($_GET['search']) ? e($_GET['search']) : '' ?>">
    </div>
    <div class="col-md-2">
        <input type="text" name="size" class="form-control" placeholder="Taille" value="<?= isset($_GET['size']) ? e($_GET['size']) : '' ?>">
    </div>
    <div class="col-md-2">
        <input type="text" name="color" class="form-control" placeholder="Couleur" value="<?= isset($_GET['color']) ? e($_GET['color']) : '' ?>">
    </div>
    <div class="col-md-1 d-grid">
        <button type="submit" class="btn btn-outline-secondary">🔍</button>
    </div>
</form>

<?php if (empty($variants)): ?>
    <div class="alert alert-info">Aucune variante.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Produit</th>
                    <th>Taille</th>
                    <th>Couleur</th>
                    <th>SKU</th>
                    <th>Stock total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($variants as $v): ?>
                <tr>
                    <td><?= e($v['product_name']) ?></td>
                    <td>
                        <form method="POST" action="?route=variants/update/<?= $v['id'] ?>" class="d-flex gap-2 align-items-center">
                            <?= csrf_field(); ?>
                            <input type="text" name="size" class="form-control form-control-sm" value="<?= e($v['size']) ?>" required>
                    </td>
                    <td>
                            <input type="text" name="color" class="form-control form-control-sm" value="<?= e($v['color']) ?>" required>
                    </td>
                    <td>
                            <input type="text" name="sku" class="form-control form-control-sm" value="<?= e($v['sku']) ?>">
                    </td>
                    <td>
                        <span class="badge bg-secondary"><?= (int)$v['stock_total'] ?></span>
                        <button type="button" class="btn btn-sm btn-outline-info ms-2" onclick="openStockModal(<?= $v['id'] ?>)">Détails</button>
                    </td>
                    <td class="text-nowrap">
                            <button type="submit" class="btn btn-sm btn-success">💾</button>
                        </form>
                        <form method="POST" action="?route=variants/delete/<?= $v['id'] ?>" class="d-inline">
                            <?= csrf_field(); ?>
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette variante ?')">🗑️</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Modal Stock -->
<div class="modal fade" id="stockModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Stock par pays</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="stockModalBody">
        Chargement...
      </div>
    </div>
  </div>
</div>

<script>
async function openStockModal(variantId) {
    const modalEl = document.getElementById('stockModal');
    const body = document.getElementById('stockModalBody');
    body.innerHTML = 'Chargement...';
    const res = await fetch(`?route=variants/stock&variant_id=${variantId}`);
    const html = await res.text();
    body.innerHTML = html;
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}
</script>

<?php include 'views/layout/footer.php'; ?>
