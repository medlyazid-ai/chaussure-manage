<?php include 'views/layout/header.php'; ?>

<div class="container mt-4">
    <h2>🧾 Nouvelle facture client - <?= htmlspecialchars($selectedCountry['name']) ?></h2>
    <p class="text-muted">Seules les variantes disponibles en stock dans ce pays sont proposées ci-dessous.</p>

    <form action="?route=client_sales/store" method="POST" enctype="multipart/form-data">
        <?= csrf_field(); ?>

        <input type="hidden" name="country_id" value="<?= $selectedCountry['id'] ?>">

        <div class="mb-3">
            <label for="company_id" class="form-label">🏢 Société</label>
            <select name="company_id" id="company_id" class="form-select" required>
                <option value="">-- Choisir une société --</option>
                <?php foreach ($companies as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Les produits affichés dépendront de la société.</div>
        </div>

        <div class="mb-3">
            <label for="sale_date" class="form-label">📅 Date de la vente</label>
            <input type="date" name="sale_date" id="sale_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="mb-3">
            <label for="partner_id" class="form-label">🤝 Partenaire qui a encaissé</label>
            <select name="partner_id" id="partner_id" class="form-select" required>
                <option value="">-- Choisir un partenaire --</option>
                <?php foreach ($partners as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= e($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="account_id" class="form-label">🏦 Compte du partenaire</label>
            <select name="account_id" id="account_id" class="form-select" required>
                <option value="">-- Choisir un compte --</option>
            </select>
            <div class="form-text">Les comptes se chargent selon le partenaire.</div>
        </div>

        <div class="row g-2">
            <div class="col-md-4">
                <label for="amount_received" class="form-label">💵 Montant reçu</label>
                <input type="number" step="0.01" min="0" name="amount_received" id="amount_received" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label for="currency" class="form-label">💱 Devise</label>
                <input type="text" name="currency" id="currency" class="form-control" value="USD" maxlength="10" required>
            </div>
            <div class="col-md-3">
                <label for="received_date" class="form-label">📥 Date d'encaissement</label>
                <input type="date" name="received_date" id="received_date" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="payment_method" class="form-label">💳 Méthode</label>
                <select name="payment_method" id="payment_method" class="form-select">
                    <option value="">-- Choisir --</option>
                    <option value="Cash">💵 Cash</option>
                    <option value="Virement">🏦 Virement</option>
                    <option value="Binance">🟡 Binance</option>
                    <option value="Western Union">🌍 Western Union</option>
                    <option value="Chèque">✍️ Chèque</option>
                    <option value="Autre">📁 Autre</option>
                </select>
            </div>
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
                    <select name="variant_id[]" class="form-select variant-select" required>
                        <option value="">-- Choisir une variante --</option>
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
    async function loadAccountsForPartner(partnerId) {
        const accountSelect = document.getElementById('account_id');
        if (!partnerId) {
            accountSelect.innerHTML = '<option value="">-- Choisir un compte --</option>';
            return;
        }
        const res = await fetch(`?route=accounts/by_partner&partner_id=${partnerId}`);
        const html = await res.text();
        accountSelect.innerHTML = html;
    }

    async function loadVariantsForCompany(companyId) {
        const selects = document.querySelectorAll('.variant-select');
        if (!companyId) {
            selects.forEach(s => s.innerHTML = '<option value="">-- Choisir une variante --</option>');
            return;
        }
        const res = await fetch(`?route=client_sales/variants_by_company&company_id=${companyId}`);
        const html = await res.text();
        selects.forEach(s => s.innerHTML = html);
    }

    document.getElementById('company_id').addEventListener('change', function () {
        loadVariantsForCompany(this.value);
    });

    document.getElementById('partner_id').addEventListener('change', function () {
        loadAccountsForPartner(this.value);
    });

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

    // auto-load if one company
    window.addEventListener('DOMContentLoaded', () => {
        const companySelect = document.getElementById('company_id');
        if (companySelect.value) {
            loadVariantsForCompany(companySelect.value);
        }
        const saleDate = document.getElementById('sale_date');
        const receivedDate = document.getElementById('received_date');
        if (saleDate && receivedDate && !receivedDate.value && saleDate.value) {
            receivedDate.value = saleDate.value;
        }
    });

    document.getElementById('sale_date').addEventListener('change', function () {
        const receivedDate = document.getElementById('received_date');
        if (receivedDate && !receivedDate.value) {
            receivedDate.value = this.value;
        }
    });
</script>

<?php include 'views/layout/footer.php'; ?>
