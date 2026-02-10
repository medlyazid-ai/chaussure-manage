<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="container mt-4">
    <h2>📝 Nouvelle commande</h2>
    <form method="POST" action="?route=orders/store" enctype="multipart/form-data" class="mt-4">
        <?= csrf_field(); ?>

        <!-- Choix du fournisseur -->
        <div class="mb-3">
            <label for="supplier_id" class="form-label">Fournisseur</label>
            <select name="supplier_id" id="supplier_id" class="form-select" required>
                <option value="">-- Choisir --</option>
                <?php foreach ($suppliers as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="country_id" class="form-label">📍 Pays de destination</label>
            <select name="country_id" id="country_id" class="form-select" required>
                <option value="">-- Choisir un pays --</option>
                <?php foreach ($countries as $country): ?>
                    <option value="<?= $country['id'] ?>">
                        <?= $country['flag'] . ' ' . $country['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="company_id" class="form-label">🏢 Société (destination)</label>
            <select name="company_id" id="company_id" class="form-select">
                <option value="">-- Choisir une société --</option>
            </select>
            <div class="form-text">Optionnel si pas de société.</div>
        </div>



        <!-- Choix du produit -->
        <div class="mb-3">
            <label for="product_id" class="form-label">Produit</label>
            <select name="product_id" id="product_id" class="form-select" required>
                <option value="">-- Choisir --</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= $product['id'] ?>">
                        <?= htmlspecialchars($product['name']) ?> - <?= $product['category'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="unit_price" class="form-label">Prix unitaire (MAD)</label>
            <input type="number" step="0.01" class="form-control" name="unit_price" id="unit_price" required>
        </div>

        <hr>
        <h5>🧩 Variantes (du produit)</h5>
        <div id="variantItems" class="mb-3">
            <div class="alert alert-info">Choisissez un produit pour charger ses variantes.</div>
        </div>

        <hr>
        <button type="submit" class="btn btn-success">✅ Enregistrer la commande</button>
        <a href="?route=orders" class="btn btn-secondary">← Annuler</a>
    </form>
</div>

<!-- JavaScript dynamique pour variantes -->
<script>
document.getElementById('product_id').addEventListener('change', async function () {
    const productId = this.value;
    const container = document.getElementById('variantItems');
    if (!productId) {
        container.innerHTML = '<div class="alert alert-info">Choisissez un produit pour charger ses variantes.</div>';
        return;
    }
    container.innerHTML = 'Chargement...';
    const res = await fetch(`?route=orders/variants&product_id=${productId}`);
    const html = await res.text();
    container.innerHTML = html;
});

document.getElementById('country_id').addEventListener('change', async function () {
    const countryId = this.value;
    const select = document.getElementById('company_id');
    select.innerHTML = '<option value="">Chargement...</option>';
    if (!countryId) {
        select.innerHTML = '<option value="">-- Choisir une société --</option>';
        return;
    }
    const res = await fetch(`?route=companies/by_country&country_id=${countryId}`);
    const html = await res.text();
    select.innerHTML = html;
});
</script>

<?php include 'views/layout/footer.php'; ?>
