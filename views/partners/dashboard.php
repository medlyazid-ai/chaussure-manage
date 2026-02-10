<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>📊 Dashboard partenaire — <?= e($partner['name']) ?></h2>
        <a href="?route=partners" class="btn btn-secondary">← Retour</a>
    </div>

    <form method="GET" class="row g-2 mb-4">
        <input type="hidden" name="route" value="partners/dashboard">
        <input type="hidden" name="id" value="<?= (int)$partner['id'] ?>">
        <div class="col-md-3">
            <label class="form-label">Du</label>
            <input type="date" name="date_from" class="form-control" value="<?= e($_GET['date_from'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Au</label>
            <input type="date" name="date_to" class="form-control" value="<?= e($_GET['date_to'] ?? '') ?>">
        </div>
        <div class="col-md-2 d-grid align-items-end">
            <label class="form-label">&nbsp;</label>
            <button class="btn btn-outline-secondary">Filtrer</button>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Encaissements</div>
                    <div class="fw-semibold">MAD: <?= number_format($totals['received']['MAD'] ?? 0, 2) ?></div>
                    <div class="fw-semibold">USD: <?= number_format($totals['received']['USD'] ?? 0, 2) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Charges (fournisseurs + charges)</div>
                    <div class="fw-semibold">MAD: <?= number_format($totals['charges']['MAD'] ?? 0, 2) ?></div>
                    <div class="fw-semibold">USD: <?= number_format($totals['charges']['USD'] ?? 0, 2) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Solde net</div>
                    <div class="fw-semibold">MAD: <?= number_format(($totals['received']['MAD'] ?? 0) - ($totals['charges']['MAD'] ?? 0), 2) ?></div>
                    <div class="fw-semibold">USD: <?= number_format(($totals['received']['USD'] ?? 0) - ($totals['charges']['USD'] ?? 0), 2) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <h5>💰 Encaissements sociétés</h5>
            <?php if (empty($companyPayments)): ?>
                <div class="alert alert-info">Aucun encaissement.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Société</th>
                                <th>Facture</th>
                                <th>Compte</th>
                                <th>Montant</th>
                                <th>Méthode</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($companyPayments as $cp): ?>
                                <tr>
                                    <td><?= e($cp['payment_date']) ?></td>
                                    <td><?= e($cp['company_name']) ?></td>
                                    <td>#<?= (int)$cp['invoice_id'] ?></td>
                                    <td><?= e($cp['account_label']) ?></td>
                                    <td><?= number_format($cp['amount'], 2) ?> <?= e($cp['currency'] ?? 'MAD') ?></td>
                                    <td><?= e($cp['method']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-6">
            <h5>💳 Paiements fournisseurs</h5>
            <?php if (empty($supplierPayments)): ?>
                <div class="alert alert-info">Aucun paiement fournisseur.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Fournisseur</th>
                                <th>Montant</th>
                                <th>Méthode</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($supplierPayments as $sp): ?>
                                <tr>
                                    <td><?= e($sp['payment_date']) ?></td>
                                    <td><?= e($sp['supplier_name']) ?></td>
                                    <td><?= number_format($sp['amount'], 2) ?> <?= e($sp['currency'] ?? 'MAD') ?></td>
                                    <td><?= e($sp['payment_method']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-12">
            <h5>💸 Charges internes</h5>
            <?php if (empty($expenses)): ?>
                <div class="alert alert-info">Aucune charge.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Compte</th>
                                <th>Catégorie</th>
                                <th>Montant</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expenses as $ex): ?>
                                <tr>
                                    <td><?= e($ex['expense_date']) ?></td>
                                    <td><?= e($ex['account_label']) ?></td>
                                    <td><?= e($ex['category']) ?></td>
                                    <td><?= number_format($ex['amount'], 2) ?> <?= e($ex['currency'] ?? 'MAD') ?></td>
                                    <td><?= e($ex['notes']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>
