<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="container mt-4">
    <h2>➕ Nouvelle facture société</h2>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= e($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST" action="?route=company_invoices/store" enctype="multipart/form-data">
        <?= csrf_field(); ?>
        <div class="mb-3">
            <label class="form-label">Société</label>
            <select name="company_id" id="company_id" class="form-select" required>
                <option value="">-- Choisir --</option>
                <?php foreach ($companies as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (!empty($selectedCompanyId) && (int)$selectedCompanyId === (int)$c['id']) ? 'selected' : '' ?>>
                        <?= e($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Date facture</label>
            <input type="date" name="invoice_date" class="form-control" required value="<?= date('Y-m-d') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Montant total</label>
            <input type="number" step="0.01" name="amount_due" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2"></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Justificatif (PDF/image)</label>
            <input type="file" name="proof_file" class="form-control" accept="image/*,application/pdf">
        </div>

        <hr>
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <h5 class="mb-1">Produits vendus (par variantes)</h5>
            <span id="qty-counter" class="small text-muted">0 ligne(s) saisie(s)</span>
        </div>
        <div id="items-container">
            <div class="alert alert-info">Choisissez une société pour charger le stock.</div>
        </div>

        <button type="submit" class="btn btn-success mt-3" id="submit-invoice">Enregistrer</button>
        <a href="?route=company_invoices" class="btn btn-secondary mt-3">Annuler</a>
    </form>
</div>

<script>
function updateQtyCounter() {
    const inputs = document.querySelectorAll('#items-container input[name*="[quantity_sold]"]');
    let count = 0;
    inputs.forEach(i => {
        if (parseInt(i.value || '0', 10) > 0) count++;
    });
    const counter = document.getElementById('qty-counter');
    if (counter) counter.textContent = `${count} ligne(s) saisie(s)`;
    return count;
}

document.getElementById('company_id').addEventListener('change', async function () {
    const companyId = this.value;
    const container = document.getElementById('items-container');
    if (!companyId) {
        container.innerHTML = '<div class="alert alert-info">Choisissez une société pour charger le stock.</div>';
        return;
    }
    container.innerHTML = 'Chargement...';
    const res = await fetch(`?route=company_invoices/variants&company_id=${companyId}`);
    const html = await res.text();
    container.innerHTML = html;
    updateQtyCounter();
    container.addEventListener('input', function (e) {
        if (e.target && e.target.name && e.target.name.includes('[quantity_sold]')) {
            updateQtyCounter();
        }
    });
});

window.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('company_id');
    if (select.value) {
        select.dispatchEvent(new Event('change'));
    }
});

document.getElementById('submit-invoice').addEventListener('click', function (e) {
    const count = updateQtyCounter();
    if (count === 0) {
        e.preventDefault();
        alert('Veuillez saisir au moins une quantité vendue.');
    }
});
</script>

<?php include 'views/layout/footer.php'; ?>
