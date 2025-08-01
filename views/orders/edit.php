<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="container mt-4">
    <h2>✏️ Modifier la commande #<?= $order['id'] ?></h2>

    <form method="POST" action="?route=orders/update/<?= $order['id'] ?>" enctype="multipart/form-data" class="mt-4">

        <!-- Choix du fournisseur -->
        <div class="mb-3">
            <label for="supplier_id" class="form-label">Fournisseur</label>
            <select name="supplier_id" id="supplier_id" class="form-select" required>
                <option value="">-- Choisir --</option>
                <?php foreach ($suppliers as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $s['id'] == $order['supplier_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Choix du pays de destination -->
        <div class="mb-3">
            <label for="country_id" class="form-label">📍 Pays de destination</label>
            <select name="country_id" id="country_id" class="form-select" required>
                <option value="">-- Choisir un pays --</option>
                <?php foreach ($countries as $country): ?>
                    <option value="<?= $country['id'] ?>" <?= ($order['country_id'] == $country['id']) ? 'selected' : '' ?>>
                        <?= $country['flag'] . ' ' . $country['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>


        <!-- Choix du produit -->
        <div class="mb-3">
            <label for="product_id" class="form-label">Produit</label>
            <select name="product_id" id="product_id" class="form-select" required>
                <option value="">-- Choisir --</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= $product['id'] ?>" <?= $product['id'] == $order['product_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($product['name']) ?> - <?= $product['category'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Variantes -->
        <hr>
        <h5>🧩 Variantes commandées</h5>
        <div id="variantItems">
            <?php $variantIndex = 0; ?>
            <?php foreach ($variants as $variant): ?>
                <div class="variant-item border rounded p-3 mb-3 position-relative bg-light">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-variant-item" aria-label="Supprimer"></button>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Taille</label>
                            <input type="text" class="form-control" name="variants[<?= $variantIndex ?>][size]" value="<?= htmlspecialchars($variant['size']) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Couleur</label>
                            <input type="text" class="form-control" name="variants[<?= $variantIndex ?>][color]" value="<?= htmlspecialchars($variant['color']) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Quantité</label>
                            <input type="number" class="form-control" name="variants[<?= $variantIndex ?>][quantity_ordered]" value="<?= $variant['quantity_ordered'] ?>" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Prix unitaire (MAD)</label>
                            <input type="number" step="0.01" class="form-control" name="variants[<?= $variantIndex ?>][unit_price]" value="<?= $variant['unit_price'] ?>" required>
                        </div>
                    </div>
                </div>
                <?php $variantIndex++; ?>
            <?php endforeach; ?>
        </div>
        <button type="button" class="btn btn-outline-primary my-3" id="addVariantItem">➕ Ajouter une variante</button>

        <hr>
        <button type="submit" class="btn btn-success">💾 Enregistrer les modifications</button>
        <a href="?route=orders" class="btn btn-secondary">← Retour</a>
    </form>
</div>

<!-- JS pour ajouter/supprimer des variantes dynamiquement -->
<script>
let variantIndex = <?= $variantIndex ?>;

document.getElementById('addVariantItem').addEventListener('click', () => {
    const container = document.getElementById('variantItems');
    const html = `
        <div class="variant-item border rounded p-3 mb-3 position-relative bg-light">
            <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-variant-item" aria-label="Supprimer"></button>
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Taille</label>
                    <input type="text" class="form-control" name="variants[\${variantIndex}][size]" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Couleur</label>
                    <input type="text" class="form-control" name="variants[\${variantIndex}][color]" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quantité</label>
                    <input type="number" class="form-control" name="variants[\${variantIndex}][quantity_ordered]" min="1" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Prix unitaire (MAD)</label>
                    <input type="number" step="0.01" class="form-control" name="variants[\${variantIndex}][unit_price]" required>
                </div>
            </div>
        </div>`;
    container.insertAdjacentHTML('beforeend', html);
    variantIndex++;
});

document.addEventListener('click', function (e) {
    if (e.target && e.target.classList.contains('remove-variant-item')) {
        e.target.closest('.variant-item').remove();
    }
});
</script>

<?php include 'views/layout/footer.php'; ?>
