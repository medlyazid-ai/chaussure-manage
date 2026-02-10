<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="container mt-4">
    <h2>📊 Dashboard du fournisseur : <?= htmlspecialchars($supplier['name']) ?></h2>

    <div class="mb-4">
        <a href="?route=payments/create&supplier_id=<?= $supplier['id'] ?>" class="btn btn-success">➕ Nouveau paiement</a>
        <a href="?route=suppliers" class="btn btn-secondary">← Retour à la liste</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted">Commandes</div>
                    <div class="h5 mb-0"><?= $totalOrders ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted">Total commandes</div>
                    <div class="h5 mb-0"><?= number_format($totalAmount, 2) ?> MAD</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted">Payé</div>
                    <div class="h5 mb-0"><?= number_format($totalPaid, 2) ?> MAD</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted">Reste à payer</div>
                    <div class="h5 mb-0"><?= number_format($totalRemaining, 2) ?> MAD</div>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <h5>📌 État des commandes</h5>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($statusCounts as $status => $count): ?>
                <span class="badge bg-secondary"><?= e($status) ?> : <?= $count ?></span>
            <?php endforeach; ?>
        </div>
    </div>

    <h4>🧾 Commandes du fournisseur</h4>
    <div class="table-responsive mb-5">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Pays destination</th>
                    <th>Société</th>
                    <th>Montant total</th>
                    <th>Montant payé</th>
                    <th>Reste à payer</th>
                    <th>Statut</th>
                    <th>Avancement</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $grandTotal = 0;
$grandPaid = 0;
$grandRemaining = 0;
foreach ($orders as $order):
    // Calcul montant total par commande
    $orderItems = Order::orderItems($order['id']);
    $total = 0;
    foreach ($orderItems as $item) {
        $total += $item['unit_price'] * $item['quantity_ordered'];
    }

    $paid = Payment::totalAllocatedToOrder($order['id']);
    $remaining = $total - $paid;

    // Cumuls
    $grandTotal += $total;
    $grandPaid += $paid;
    $grandRemaining += $remaining;
    ?>
                <?php $progress = Shipment::getOrderProgress($order['id']); ?>
                <tr>
                    <td>#<?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['destination_country']) ?></td>
                    <td><?= !empty($order['company_name']) ? e($order['company_name']) : '<span class="text-muted">—</span>' ?></td>
                    <td><?= number_format($total, 2) ?> MAD</td>
                    <td><?= number_format($paid, 2) ?> MAD</td>
                    <td class="<?= $remaining > 0 ? 'text-danger' : 'text-success' ?>">
                        <?= number_format($remaining, 2) ?> MAD
                    </td>
                    <td><?= htmlspecialchars($order['status']) ?></td>
                    <td>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar <?= $progress['remaining'] === 0 ? 'bg-success' : 'bg-info' ?>" style="width: <?= $progress['percent'] ?>%"></div>
                        </div>
                        <small class="text-muted"><?= $progress['sent'] ?>/<?= $progress['ordered'] ?></small>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="table-secondary fw-bold">
                    <td colspan="3" class="text-end">Totaux :</td>
                    <td><?= number_format($grandTotal, 2) ?> MAD</td>
                    <td><?= number_format($grandPaid, 2) ?> MAD</td>
                    <td><?= number_format($grandRemaining, 2) ?> MAD</td>
                    <td></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <h4>📦 Envois affectés</h4>
    <div class="table-responsive mb-5">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Commande</th>
                    <th>Produit</th>
                    <th>Pays</th>
                    <th>Société</th>
                    <th>Date</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($shipments)): ?>
                    <tr><td colspan="7" class="text-muted">Aucun envoi.</td></tr>
                <?php else: ?>
                    <?php foreach ($shipments as $s): ?>
                        <tr>
                            <td>#<?= $s['id'] ?></td>
                            <td>#<?= $s['order_id'] ?></td>
                            <td><?= e($s['product_name']) ?></td>
                            <td><?= e($s['country_name']) ?></td>
                            <td><?= !empty($s['company_name']) ? e($s['company_name']) : '<span class="text-muted">—</span>' ?></td>
                            <td><?= e($s['shipment_date']) ?></td>
                            <td><?= e($s['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <h4>💰 Paiements effectués</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Montant</th>
                    <th>Méthode</th>
                    <th>Preuve</th>
                    <th>Notes</th>
                    <th>Affectations</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['payment_date']) ?></td>
                        <td><?= number_format($p['amount'] ?? 0, 2) ?> MAD</td>
                        <td><?= htmlspecialchars($p['payment_method']) ?></td>
                        <td>
                            <?php if (!empty($p['proof_file'])): ?>
                                <a href="<?= htmlspecialchars($p['proof_file']) ?>" target="_blank">📎 Voir</a>
                            <?php else: ?>
                                <span class="text-muted">Aucune</span>
                            <?php endif; ?>
                        </td>
                        <td><?= nl2br(htmlspecialchars($p['notes'])) ?></td>
                        <td>
                            <?php
                    $allocs = Payment::allocationsByPayment($p['id']);
                    foreach ($allocs as $a) {
                        echo "Commande #" . $a['order_id'] . " : " . number_format($a['amount_allocated'], 2) . " MAD<br>";
                    }
                    ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>
