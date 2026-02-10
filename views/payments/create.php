<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="container mt-4">
    <h2>💰 Ajouter un paiement fournisseur</h2>

    <form action="?route=payments/store" method="POST" enctype="multipart/form-data">
        <?= csrf_field(); ?>
        <input type="hidden" id="prefill_order_id" value="<?= isset($prefillOrderId) ? e($prefillOrderId) : '' ?>">
        <div class="mb-3">
            <label for="supplier_id" class="form-label">👤 Fournisseur</label>
            <select name="supplier_id" id="supplier_id" class="form-select" required>
                <option value="">-- Choisir un fournisseur --</option>
                <?php foreach ($suppliers as $supplier): ?>
                    <option value="<?= $supplier['id'] ?>" <?= (isset($prefillSupplierId) && $prefillSupplierId == $supplier['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($supplier['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="payment_date" class="form-label">📅 Date de paiement</label>
            <input type="date" name="payment_date" id="payment_date" class="form-control" required
                   value="<?= date('Y-m-d') ?>">
        </div>


        <div class="mb-3">
            <label for="partner_id" class="form-label">🤝 Partenaire payeur</label>
            <select name="partner_id" id="partner_id" class="form-select" required>
                <option value="">-- Choisir --</option>
                <?php foreach ($partners as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= e($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="payment_method" class="form-label">💳 Méthode de paiement</label>
            <select name="payment_method" id="payment_method" class="form-select" required>
                <option value="">-- Choisir une méthode --</option>
                <option value="Cash">💵 Cash</option>
                <option value="Virement">🏦 Virement</option>
                <option value="Binance">🟡 Binance</option>
                <option value="Western Union">🌍 Western Union</option>
                <option value="Chèque">✍️ Chèque</option>
                <option value="Autre">📁 Autre</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="currency" class="form-label">💱 Devise</label>
            <select name="currency" id="currency" class="form-select" required>
                <option value="MAD" selected>MAD</option>
                <option value="USD">USD</option>
            </select>
        </div>


        <div class="mb-3">
            <label for="notes" class="form-label">📝 Notes</label>
            <textarea name="notes" id="notes" rows="3" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label for="proof" class="form-label">📁 Preuve de paiement</label>
            <input type="file" name="proof" id="proof" class="form-control" accept="image/*,application/pdf">
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="auto_allocate" id="auto_allocate">
            <label class="form-check-label" for="auto_allocate">
                Répartir automatiquement sur les commandes non soldées (du plus ancien au plus récent)
            </label>
        </div>

        <h5 class="mt-4">📌 Répartition manuelle du paiement par commande</h5>

        <!-- Ce bloc est remplacé dynamiquement après sélection du fournisseur -->
        <div id="orders-table">
            <div class="alert alert-info">Sélectionnez un fournisseur pour afficher ses commandes.</div>
        </div>

        <button type="submit" class="btn btn-success mt-3">✅ Enregistrer le paiement</button>
        <a href="?route=payments" class="btn btn-secondary mt-3">← Annuler</a>
    </form>
</div>


<script>
document.getElementById('supplier_id').addEventListener('change', function () {
    const supplierId = this.value;
    const ordersContainer = document.getElementById('orders-table');
    const submitBtn = document.querySelector('button[type="submit"]');
    const prefillOrderId = document.getElementById('prefill_order_id').value;

    if (!supplierId) {
        ordersContainer.innerHTML = '<div class="alert alert-warning">Aucun fournisseur sélectionné.</div>';
        submitBtn.disabled = true;
        return;
    }

    const extra = prefillOrderId ? `&order_id=${prefillOrderId}` : '';
    fetch(`?route=payments/fetch_orders_by_supplier&supplier_id=${supplierId}${extra}`)
        .then(res => res.text())
        .then(html => {
            ordersContainer.innerHTML = html;

            if (html.includes("Aucune commande impayée")) {
                submitBtn.disabled = true;
            } else {
                submitBtn.disabled = false;
            }
        })
        .catch(() => {
            ordersContainer.innerHTML = '<div class="alert alert-danger">Erreur lors du chargement des commandes.</div>';
            submitBtn.disabled = true;
        });
});

// auto-load if prefilled
window.addEventListener('DOMContentLoaded', () => {
    const supplierSelect = document.getElementById('supplier_id');
    if (supplierSelect.value) {
        supplierSelect.dispatchEvent(new Event('change'));
    }
});
</script>

<?php include 'views/layout/footer.php'; ?>
