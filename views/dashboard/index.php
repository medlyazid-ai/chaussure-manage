<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="container mt-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div class="alert alert-success rounded-4 shadow-sm mb-0">
            👋 Bonjour <strong><?= e($_SESSION['user']['name']) ?></strong>, bienvenue sur votre espace personnel.
        </div>
        <form method="GET" class="d-flex align-items-center gap-2">
            <input type="hidden" name="route" value="dashboard">
            <label class="form-label mb-0">Période</label>
            <select name="range" class="form-select" onchange="this.form.submit()">
                <?php
                    $range = $_GET['range'] ?? '30';
                    $options = [
                        '7' => '7 jours',
                        '30' => '30 jours',
                        '90' => '90 jours',
                        '365' => '12 mois',
                        'all' => 'Tout'
                    ];
                    foreach ($options as $val => $label) {
                        $selected = ($range === $val) ? 'selected' : '';
                        echo "<option value=\"$val\" $selected>$label</option>";
                    }
                ?>
            </select>
        </form>
    </div>

    <div class="row g-3">
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted">Commandes</div>
                    <div class="h4 mb-0"><?= number_format($stats['orders_count']) ?></div>
                    <small class="text-muted"><?= number_format($stats['orders_amount'], 2) ?> MAD</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted">Paiements</div>
                    <div class="h4 mb-0"><?= number_format($stats['payments_amount'], 2) ?> MAD</div>
                    <small class="text-muted">Alloué: <?= number_format($stats['allocations_amount'], 2) ?> MAD</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted">Reste à payer</div>
                    <div class="h4 mb-0"><?= number_format($stats['unpaid_amount'], 2) ?> MAD</div>
                    <small class="text-muted">Basé sur allocations</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted">Envois</div>
                    <div class="h4 mb-0"><?= number_format($stats['shipments_pending']) ?> en cours</div>
                    <small class="text-muted"><?= number_format($stats['shipments_delivered']) ?> livrés</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted">Factures clients</div>
                    <div class="h4 mb-0"><?= number_format($stats['sales_count']) ?></div>
                    <small class="text-muted">Sur la période</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-9">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex flex-wrap gap-2">
                    <a href="?route=orders" class="btn btn-outline-primary">📦 Commandes</a>
                    <a href="?route=shipments" class="btn btn-outline-secondary">📦 Envois</a>
                    <a href="?route=products" class="btn btn-outline-success">👟 Produits</a>
                    <a href="?route=payments" class="btn btn-outline-warning">💰 Paiements</a>
                    <a href="?route=client_sales" class="btn btn-outline-dark">🧾 Ventes clients</a>
                    <a href="?route=register" class="btn btn-outline-info">➕ Ajouter un compte</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-semibold">Dernières commandes</div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Fournisseur</th>
                                <th>Pays</th>
                                <th>Total</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($recentOrders)): ?>
                            <tr><td colspan="5" class="text-muted">Aucune commande</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentOrders as $o): ?>
                                <tr>
                                    <td>#<?= $o['id'] ?></td>
                                    <td><?= e($o['supplier_name']) ?></td>
                                    <td><?= e($o['country_name']) ?></td>
                                    <td><?= number_format($o['total_amount'], 2) ?> MAD</td>
                                    <td><?= e($o['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-semibold">Derniers paiements</div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Fournisseur</th>
                                <th>Montant</th>
                                <th>Méthode</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($recentPayments)): ?>
                            <tr><td colspan="5" class="text-muted">Aucun paiement</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentPayments as $p): ?>
                                <tr>
                                    <td>#<?= $p['id'] ?></td>
                                    <td><?= e($p['supplier_name']) ?></td>
                                    <td><?= number_format($p['amount'], 2) ?> MAD</td>
                                    <td><?= e($p['payment_method']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($p['payment_date'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-semibold">Derniers envois</div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Commande</th>
                                <th>Fournisseur</th>
                                <th>Statut</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($recentShipments)): ?>
                            <tr><td colspan="5" class="text-muted">Aucun envoi</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentShipments as $s): ?>
                                <tr>
                                    <td>#<?= $s['id'] ?></td>
                                    <td>#<?= $s['order_id'] ?></td>
                                    <td><?= e($s['supplier_name']) ?></td>
                                    <td><?= e($s['status']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($s['shipment_date'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>
