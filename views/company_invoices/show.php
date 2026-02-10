<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>🧾 Facture société #<?= $invoice['id'] ?></h2>
        <a href="?route=company_invoices" class="btn btn-secondary">← Retour</a>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= e($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= e($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php $totalPaid = CompanyPayment::totalPaid($invoice['id']); ?>
    <?php $remaining = max($invoice['amount_due'] - $totalPaid, 0); ?>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Société</div>
                    <div class="fw-semibold"><?= e($invoice['company_name']) ?></div>
                    <small class="text-muted"><?= e($invoice['country_name']) ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Montant</div>
                    <div class="fw-semibold"><?= number_format($invoice['amount_due'], 2) ?> MAD</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Payé</div>
                    <div class="fw-semibold"><?= number_format($totalPaid, 2) ?> MAD</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Reste</div>
                    <div class="fw-semibold"><?= number_format($remaining, 2) ?> MAD</div>
                </div>
            </div>
        </div>
    </div>

    <h5>📦 Produits vendus</h5>
    <?php if (empty($items)): ?>
        <div class="alert alert-info">Aucun item.</div>
    <?php else: ?>
        <div class="table-responsive mb-4">
            <table class="table table-sm table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Produit</th>
                        <th>Variante</th>
                        <th>Quantité</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td><?= e($it['product_name']) ?></td>
                            <td><?= e($it['size']) ?> / <?= e($it['color']) ?></td>
                            <td><?= (int)$it['quantity_sold'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-lg-6">
            <h5>💸 Encaissements</h5>
            <?php if (empty($payments)): ?>
                <div class="alert alert-info">Aucun encaissement.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Partenaire</th>
                                <th>Compte</th>
                                <th>Montant</th>
                                <th>Méthode</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $p): ?>
                                <tr>
                                    <td><?= e($p['payment_date']) ?></td>
                                    <td><?= e($p['partner_name']) ?></td>
                                    <td><?= e($p['account_label']) ?></td>
                                    <td><?= number_format($p['amount'], 2) ?> <?= e($p['currency'] ?? 'MAD') ?></td>
                                    <td><?= e($p['method']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-lg-6">
            <h5>➕ Ajouter un encaissement</h5>
            <form method="POST" action="?route=company_invoices/pay" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <input type="hidden" name="invoice_id" value="<?= $invoice['id'] ?>">
                <div class="mb-2">
                    <label class="form-label">Partenaire</label>
                    <select name="partner_id" id="partner_id" class="form-select" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach ($partners as $pt): ?>
                            <option value="<?= $pt['id'] ?>"><?= e($pt['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Compte bancaire</label>
                    <select name="account_id" id="account_id" class="form-select">
                        <option value="">-- Choisir un compte --</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Montant</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Devise</label>
                    <select name="currency" class="form-select" required>
                        <option value="MAD" selected>MAD</option>
                        <option value="USD">USD</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Date</label>
                    <input type="date" name="payment_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="mb-2">
                    <label class="form-label">Méthode</label>
                    <select name="method" class="form-select" required>
                        <option value="">-- Choisir --</option>
                        <option value="Cash">Cash</option>
                        <option value="Virement">Virement</option>
                        <option value="Western Union">Western Union</option>
                        <option value="Chèque">Chèque</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Justificatif</label>
                    <input type="file" name="proof_file" class="form-control" accept="image/*,application/pdf">
                </div>
                <button type="submit" class="btn btn-success">Enregistrer</button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('partner_id').addEventListener('change', async function () {
    const partnerId = this.value;
    const select = document.getElementById('account_id');
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
