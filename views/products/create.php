<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="container py-4">
    <h2 class="mb-4">Ajouter un produit</h2>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= e($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <form action="?route=products/store" method="POST" enctype="multipart/form-data">
        <?= csrf_field(); ?>
        <div class="mb-3">
            <label for="name" class="form-label">Nom du produit</label>
            <input name="name" id="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="category" class="form-label">Catégorie</label>
            <input name="category" id="category" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label for="image" class="form-label">🖼️ Image du produit</label>
            <input type="file" class="form-control" name="image" accept="image/*">
        </div>

        <div class="mb-3">
            <label class="form-label">Variantes</label>
            <div class="border rounded p-3 mb-3 bg-light">
                <div class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Tailles (séparées par virgule)</label>
                        <input type="text" id="quick-sizes" class="form-control" placeholder="40, 41, 42, 43, 44, 45">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Couleur</label>
                        <input type="text" id="quick-color" class="form-control" placeholder="Noir">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="button" class="btn btn-outline-primary" onclick="generateVariants()">Générer</button>
                    </div>
                </div>
                <div class="form-text mt-2">Le SKU sera généré automatiquement si vide.</div>
            </div>
            <div id="variants">
                <!-- Variante 0 ajoutée par défaut -->
                <div class="row variant-group mb-2">
                    <div class="col-md-3">
                        <input name="variants[0][size]" class="form-control" placeholder="Taille" required>
                    </div>
                    <div class="col-md-3">
                        <input name="variants[0][color]" class="form-control" placeholder="Couleur" required>
                    </div>
                    <div class="col-md-4">
                        <input name="variants[0][sku]" class="form-control" placeholder="SKU (auto si vide)">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="button" class="btn btn-danger" onclick="this.parentElement.parentElement.remove()">🗑️</button>
                    </div>
                </div>
            </div>
            <button type="button" onclick="addVariantField()" class="btn btn-outline-primary btn-sm mt-2">➕ Ajouter une variante</button>
        </div>

        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-success">✅ Enregistrer le produit</button>
        </div>
    </form>

    <div class="mt-3">
        <a href="?route=products" class="btn btn-secondary">← Retour à la liste des produits</a>
    </div>
</div>

<script>
    // Ajoute dynamiquement un groupe de champs variante
    function addVariantField(size = '', color = '', sku = '') {
        const index = document.querySelectorAll('.variant-group').length;
        const container = document.getElementById('variants');

        const row = document.createElement('div');
        row.className = 'row variant-group mb-2';

        row.innerHTML = `
            <div class="col-md-3">
                <input name="variants[${index}][size]" class="form-control" placeholder="Taille" value="${size}" required>
            </div>
            <div class="col-md-3">
                <input name="variants[${index}][color]" class="form-control" placeholder="Couleur" value="${color}" required>
            </div>
            <div class="col-md-4">
                <input name="variants[${index}][sku]" class="form-control" placeholder="SKU (auto si vide)" value="${sku}">
            </div>
            <div class="col-md-2 d-grid">
                <button type="button" class="btn btn-danger" onclick="this.parentElement.parentElement.remove()">🗑️</button>
            </div>
        `;

        container.appendChild(row);
    }

    function generateVariants() {
        const sizesRaw = document.getElementById('quick-sizes').value.trim();
        const color = document.getElementById('quick-color').value.trim();
        if (!sizesRaw || !color) return;
        const sizes = sizesRaw.split(',').map(s => s.trim()).filter(Boolean);
        sizes.forEach(sz => addVariantField(sz, color, ''));
    }
</script>

<?php include 'views/layout/footer.php'; ?>
