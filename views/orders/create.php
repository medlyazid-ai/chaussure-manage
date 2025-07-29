<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="container mt-4">
    <h2>📝 Nouvelle commande</h2>
    <form method="POST" action="?route=orders/store" enctype="multipart/form-data" class="mt-4">

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

        <!-- Choix du pays de destination -->
        <div class="mb-3">
            <label for="destination_country" class="form-label">📍 Pays de destination</label>
            <select name="destination_country" id="destination_country" class="form-select" required>
                <option value="">-- Choisir un pays --</option>
                <option value="Guinée">🇬🇳 Guinée</option>
                <option value="Côte d'Ivoire">🇨🇮 Côte d'Ivoire</option>
                <option value="Mali">🇲🇱 Mali</option>
            </select>
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

        <hr>
        <h5>🧩 Variantes à commander</h5>
        <div id="variantItems"></div>
        <button type="button" class="btn btn-outline-primary my-3" id="addVariantItem">➕ Ajouter une variante</button>

        <hr>
        <button type="submit" class="btn btn-success">✅ Enregistrer la commande</button>
        <a href="?route=orders" class="btn btn-secondary">← Annuler</a>
    </form>
</div>

<!-- JavaScript dynamique pour variantes -->
<script>
let variantIndex = 0;

document.getElementById('addVariantItem').addEventListener('click', () => {
    const container = document.getElementById('variantItems');
    const html = `
        <div class="variant-item border rounded p-3 mb-3 position-relative bg-light">
            <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-variant-item" aria-label="Supprimer"></button>
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Taille</label>
                    <input type="text" class="form-control" name="variants[${variantIndex}][size]" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Couleur</label>
                    <input type="text" class="form-control" name="variants[${variantIndex}][color]" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quantité</label>
                    <input type="number" class="form-control" name="variants[${variantIndex}][quantity_ordered]" min="1" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Prix unitaire (MAD)</label>
                    <input type="number" step="0.01" class="form-control" name="variants[${variantIndex}][unit_price]" required>
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
