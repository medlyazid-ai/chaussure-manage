<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>💸 Charges partenaires</h2>
</div>

<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= e($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= e($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-body">
        <form method="POST" action="?route=partner_expenses/store" enctype="multipart/form-data" class="row g-2">
            <?= csrf_field(); ?>
            <div class="col-md-3">
                <label class="form-label">Partenaire</label>
                <select name="partner_id" id="expense_partner_id" class="form-select" required>
                    <option value="">-- Choisir --</option>
                    <?php foreach ($partners as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= e($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Compte</label>
                <select name="account_id" id="expense_account_id" class="form-select">
                    <option value="">-- Choisir un compte --</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Montant</label>
                <input type="number" step="0.01" name="amount" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Devise</label>
                <select name="currency" class="form-select" required>
                    <option value="MAD" selected>MAD</option>
                    <option value="USD">USD</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Date</label>
                <input type="date" name="expense_date" class="form-control" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Catégorie</label>
                <input type="text" name="category" class="form-control" placeholder="Transport, ...">
            </div>
            <div class="col-md-12">
                <label class="form-label">Notes</label>
                <input type="text" name="notes" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Justificatif</label>
                <input type="file" name="proof_file" class="form-control" accept="image/*,application/pdf">
            </div>
            <div class="col-md-2 d-grid align-items-end">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-success">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Date</th>
                <th>Partenaire</th>
                <th>Compte</th>
                <th>Catégorie</th>
                <th>Montant</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($expenses as $e): ?>
                <tr>
                    <td><?= e($e['expense_date']) ?></td>
                    <td><?= e($e['partner_name']) ?></td>
                    <td><?= e($e['account_label']) ?></td>
                    <td><?= e($e['category']) ?></td>
                    <td><?= number_format($e['amount'], 2) ?> <?= e($e['currency'] ?? 'MAD') ?></td>
                    <td><?= e($e['notes']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
document.getElementById('expense_partner_id').addEventListener('change', async function () {
    const partnerId = this.value;
    const select = document.getElementById('expense_account_id');
    select.innerHTML = '<option value="">Chargement...</option>';
    if (!partnerId) {
        select.innerHTML = '<option value="">-- Choisir un compte --</option>';
        return;
    }
    const res = await fetch(`?route=accounts/by_partner&partner_id=${partnerId}`);
    const html = await res.text();
    select.innerHTML = html;
});
</script>

<?php include 'views/layout/footer.php'; ?>
