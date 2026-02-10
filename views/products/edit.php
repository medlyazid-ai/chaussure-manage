<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="container py-4">
    <h2 class="mb-4">Modifier le produit</h2>

    <form action="?route=products/update/<?= $product['id'] ?>" method="POST">
        <?= csrf_field(); ?>
        <div class="mb-3">
            <label class="form-label">Nom</label>
            <input name="name" value="<?= htmlspecialchars($product['name']) ?>" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Catégorie</label>
            <input name="category" value="<?= htmlspecialchars($product['category']) ?>" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control"><?= htmlspecialchars($product['description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Variantes</label>
            <div id="variants">
                <?php foreach ($variants as $i => $v): ?>
                    <div class="row variant-group mb-2">
                        <div class="col-md-3">
                            <input name="variants[<?= $i ?>][size]" class="form-control" value="<?= htmlspecialchars($v['size']) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <input name="variants[<?= $i ?>][color]" class="form-control" value="<?= htmlspecialchars($v['color']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <input name="variants[<?= $i ?>][sku]" class="form-control" value="<?= htmlspecialchars($v['sku']) ?>" required>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="button" class="btn btn-danger" onclick="this.parentElement.parentElement.remove()">🗑️</button>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
            <button type="button" onclick="addVariantField()" class="btn btn-outline-primary btn-sm mt-2">➕ Ajouter une variante</button>
        </div>

        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-success">✅ Mettre à jour</button>
        </div>
    </form>

    <div class="mt-3">
        <a href="?route=products" class="btn btn-secondary">← Retour à la liste</a>
    </div>
</div>

<script>
    function addVariantField() {
        const index = document.querySelectorAll('.variant-group').length;
        const container = document.getElementById('variants');

        const row = document.createElement('div');
        row.className = 'row variant-group mb-2';

        row.innerHTML = `
            <div class="col-md-3">
                <input name="variants[${index}][size]" class="form-control" placeholder="Taille" required>
            </div>
            <div class="col-md-3">
                <input name="variants[${index}][color]" class="form-control" placeholder="Couleur" required>
            </div>
            <div class="col-md-4">
                <input name="variants[${index}][sku]" class="form-control" placeholder="SKU" required>
            </div>
            <div class="col-md-2 d-grid">
                <button type="button" class="btn btn-danger" onclick="this.parentElement.parentElement.remove()">🗑️</button>
            </div>
        `;

        container.appendChild(row);
    }
</script>

<?php include 'views/layout/footer.php'; ?>
