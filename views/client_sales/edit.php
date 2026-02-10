<?php include 'views/layout/header.php'; ?>

<div class="container mt-4">
    <h2>✏️ Modifier la facture client #<?= $sale['id'] ?> - <?= htmlspecialchars($sale['country_name']) ?></h2>

    <form action="?route=client_sales/update/<?= $sale['id'] ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field(); ?>

        <input type="hidden" name="country_id" value="<?= $sale['country_id'] ?>">

        <div class="mb-3">
            <label for="company_id" class="form-label">🏢 Société</label>
            <select name="company_id" id="company_id" class="form-select">
                <option value="">-- Choisir une société --</option>
                <?php foreach ($companies as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($sale['company_id'] == $c['id']) ? 'selected' : '' ?>>
                        <?= e($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Optionnel si pas de société.</div>
        </div>

        <div class="mb-3">
            <label for="sale_date" class="form-label">📅 Date de la vente</label>
            <input type="date" name="sale_date" id="sale_date" class="form-control" value="<?= $sale['sale_date'] ?>" required>
        </div>

        <div class="mb-3">
            <label for="partner_id" class="form-label">🤝 Partenaire qui a encaissé</label>
            <select name="partner_id" id="partner_id" class="form-select" required>
                <option value="">-- Choisir un partenaire --</option>
                <?php foreach ($partners as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= ($sale['partner_id'] == $p['id']) ? 'selected' : '' ?>>
                        <?= e($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="account_id" class="form-label">🏦 Compte du partenaire</label>
            <select name="account_id" id="account_id" class="form-select" required>
                <option value="">-- Choisir un compte --</option>
                <?php foreach ($accountsForPartner as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= ($sale['account_id'] == $a['id']) ? 'selected' : '' ?>>
                        <?= e($a['account_label']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="row g-2">
            <div class="col-md-4">
                <label for="amount_received" class="form-label">💵 Montant reçu</label>
                <input type="number" step="0.01" min="0" name="amount_received" id="amount_received" class="form-control" value="<?= e($sale['amount_received']) ?>" required>
            </div>
            <div class="col-md-2">
                <label for="currency" class="form-label">💱 Devise</label>
                <input type="text" name="currency" id="currency" class="form-control" value="<?= e($sale['currency']) ?>" maxlength="10" required>
            </div>
            <div class="col-md-3">
                <label for="received_date" class="form-label">📥 Date d'encaissement</label>
                <input type="date" name="received_date" id="received_date" class="form-control" value="<?= e($sale['received_date']) ?>">
            </div>
            <div class="col-md-3">
                <label for="payment_method" class="form-label">💳 Méthode</label>
                <select name="payment_method" id="payment_method" class="form-select">
                    <option value="">-- Choisir --</option>
                    <?php $pm = $sale['payment_method'] ?? ''; ?>
                    <option value="Cash" <?= $pm === 'Cash' ? 'selected' : '' ?>>💵 Cash</option>
                    <option value="Virement" <?= $pm === 'Virement' ? 'selected' : '' ?>>🏦 Virement</option>
                    <option value="Binance" <?= $pm === 'Binance' ? 'selected' : '' ?>>🟡 Binance</option>
                    <option value="Western Union" <?= $pm === 'Western Union' ? 'selected' : '' ?>>🌍 Western Union</option>
                    <option value="Chèque" <?= $pm === 'Chèque' ? 'selected' : '' ?>>✍️ Chèque</option>
                    <option value="Autre" <?= $pm === 'Autre' ? 'selected' : '' ?>>📁 Autre</option>
                </select>
            </div>
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
    async function loadAccountsForPartner(partnerId, selectedId = '') {
        const accountSelect = document.getElementById('account_id');
        if (!partnerId) {
            accountSelect.innerHTML = '<option value="">-- Choisir un compte --</option>';
            return;
        }
        const res = await fetch(`?route=accounts/by_partner&partner_id=${partnerId}`);
        const html = await res.text();
        accountSelect.innerHTML = html;
        if (selectedId) {
            accountSelect.value = selectedId;
        }
    }

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

    document.getElementById('partner_id').addEventListener('change', function () {
        loadAccountsForPartner(this.value);
    });

    window.addEventListener('DOMContentLoaded', () => {
        const partnerSelect = document.getElementById('partner_id');
        if (partnerSelect.value) {
            loadAccountsForPartner(partnerSelect.value, '<?= e($sale['account_id']) ?>');
        }
    });
</script>

<?php include 'views/layout/footer.php'; ?>
