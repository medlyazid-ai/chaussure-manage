<?php include 'views/layout/header.php'; ?>

<div class="container mt-4">
    <h2>✏️ Modifier la facture client #<?= $sale['id'] ?> - <?= htmlspecialchars($sale['country_name']) ?></h2>

    <form action="?route=client_sales/update/<?= $sale['id'] ?>" method="POST" enctype="multipart/form-data">

        <input type="hidden" name="country_id" value="<?= $sale['country_id'] ?>">

        <div class="mb-3">
            <label for="sale_date" class="form-label">📅 Date de la vente</label>
            <input type="date" name="sale_date" id="sale_date" class="form-control" value="<?= $sale['sale_date'] ?>" required>
        </div>

        <div class="mb-3">
            <label for="customer_name" class="form-label">👤 Client</label>
            <input type="text" name="customer_name" id="customer_name" class="form-control" value="<?= htmlspecialchars($sale['customer_name']) ?>">
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">📝 Notes</label>
            <textarea name="notes" id="notes" class="form-control" rows="2"><?= htmlspecialchars($sale['notes']) ?></textarea>
        </div>

        <div class="mb-3">
            <label for="proof_file" class="form-label">📎 Justificatif (image ou PDF)</label><br>
            <?php if ($sale['proof_file']): ?>
                <a href="<?= htmlspecialchars($sale['proof_file']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">📂 Voir l'actuel</a><br>
            <?php endif; ?>
            <input type="file" name="proof_file" id="proof_file" class="form-control mt-2" accept="image/*,.pdf">
        </div>

        <hr>

        <h5 class="mb-3">🧾 Produits vendus</h5>

        <div id="sale-lines">
            <?php foreach ($items as $item): ?>
                <div class="row mb-2 sale-line">
                    <div class="col-8">
                        <select name="variant_id[]" class="form-select" required>
                            <option value="">-- Choisir une variante --</option>
                            <?php foreach ($variants as $variant): ?>
                                <option value="<?= $variant['variant_id'] ?>"
                                    <?= $variant['variant_id'] == $item['variant_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($variant['product_name'] . ' - ' . $variant['size'] . ' / ' . $variant['color']) ?>
                                    (Stock : <?= $variant['current_stock'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-3">
                        <input type="number" name="quantity_sold[]" class="form-control" value="<?= $item['quantity_sold'] ?>" required>
                    </div>
                    <div class="col-1 d-flex align-items-center">
                        <button type="button" class="btn btn-danger btn-sm remove-line">✖</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mb-3">
            <button type="button" id="add-line" class="btn btn-secondary btn-sm">➕ Ajouter une ligne</button>
        </div>

        <button type="submit" class="btn btn-primary">💾 Mettre à jour la facture</button>
        <a href="?route=client_sales" class="btn btn-secondary">↩️ Retour</a>
    </form>
</div>

<script>
    document.getElementById('add-line').addEventListener('click', function () {
        const original = document.querySelector('.sale-line');
        const clone = original.cloneNode(true);
        clone.querySelector('select').selectedIndex = 0;
        clone.querySelector('input').value = "";
        document.getElementById('sale-lines').appendChild(clone);
    });

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
