<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un produit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    // Ajoute dynamiquement un groupe de champs variante
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
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="mb-4">Ajouter un produit</h2>
    <form action="?route=products/store" method="POST" enctype="multipart/form-data">
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
                        <input name="variants[0][sku]" class="form-control" placeholder="SKU" >
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
</body>
</html>
