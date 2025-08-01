<?php include 'views/layout/header.php'; ?>

<div class="container mt-4">
    <h2>🧾 Nouvelle facture client - <?= htmlspecialchars($selectedCountry['name']) ?></h2>
    <p class="text-muted">Seules les variantes disponibles en stock dans ce pays sont proposées ci-dessous.</p>

    <form action="?route=client_sales/store" method="POST" enctype="multipart/form-data">

        <input type="hidden" name="country_id" value="<?= $selectedCountry['id'] ?>">

        <div class="mb-3">
            <label for="sale_date" class="form-label">📅 Date de la vente</label>
            <input type="date" name="sale_date" id="sale_date" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="customer_name" class="form-label">👤 Client</label>
            <input type="text" name="customer_name" id="customer_name" class="form-control">
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">📝 Notes</label>
            <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
        </div>

        <div class="mb-3">
            <label for="proof_file" class="form-label">📎 Justificatif (image ou PDF)</label>
            <input type="file" name="proof_file" id="proof_file" class="form-control" accept="image/*,.pdf">
        </div>

        <hr>

        <h5 class="mb-3">🧾 Produits vendus</h5>

        <div id="sale-lines">
            <div class="row mb-2 sale-line">
                <div class="col-8">
                    <select name="variant_id[]" class="form-select" required>
                        <option value="">-- Choisir une variante --</option>
                        <?php foreach ($variants as $variant): ?>
                            <option value="<?= $variant['variant_id'] ?>">
                                <?= htmlspecialchars($variant['product_name'] . ' - ' . $variant['size'] . ' / ' . $variant['color']) ?>
                                (Stock : <?= $variant['current_stock'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-3">
                    <input type="number" name="quantity_sold[]" class="form-control" placeholder="Qté" required>
                </div>
                <div class="col-1 d-flex align-items-center">
                    <button type="button" class="btn btn-danger btn-sm remove-line">✖</button>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <button type="button" id="add-line" class="btn btn-secondary btn-sm">➕ Ajouter une ligne</button>
        </div>

        <button type="submit" class="btn btn-success">💾 Enregistrer la facture</button>
    </form>
</div>

<script>
    // Ajouter une nouvelle ligne de produit
    document.getElementById('add-line').addEventListener('click', function () {
        const original = document.querySelector('.sale-line');
        const clone = original.cloneNode(true);

        // Réinitialiser les champs
        clone.querySelector('select').selectedIndex = 0;
        clone.querySelector('input').value = "";

        document.getElementById('sale-lines').appendChild(clone);
    });

    // Supprimer une ligne
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-line')) {
            const lines = document.querySelectorAll('.sale-line');
            if (lines.length > 1) {
                e.target.closest('.sale-line').remove();
            }
        }
    });
</script>

<?php include 'views/layout/footer.php'; ?>
