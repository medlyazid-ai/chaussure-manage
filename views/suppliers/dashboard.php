<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="container mt-4">
    <h2>📊 Dashboard du fournisseur : <?= htmlspecialchars($supplier['name']) ?></h2>

    <div class="mb-4">
        <a href="?route=payments/create" class="btn btn-success">➕ Nouveau paiement</a>
        <a href="?route=suppliers" class="btn btn-secondary">← Retour à la liste</a>
    </div>

    <h4>🧾 Commandes du fournisseur</h4>
    <div class="table-responsive mb-5">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Pays destination</th>
                    <th>Montant total</th>
                    <th>Montant payé</th>
                    <th>Reste à payer</th>
                    <th>Statut</th>
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
                <tr>
                    <td>#<?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['destination_country']) ?></td>
                    <td><?= number_format($total, 2) ?> MAD</td>
                    <td><?= number_format($paid, 2) ?> MAD</td>
                    <td class="<?= $remaining > 0 ? 'text-danger' : 'text-success' ?>">
                        <?= number_format($remaining, 2) ?> MAD
                    </td>
                    <td><?= htmlspecialchars($order['status']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="table-secondary fw-bold">
                    <td colspan="2" class="text-end">Totaux :</td>
                    <td><?= number_format($grandTotal, 2) ?> MAD</td>
                    <td><?= number_format($grandPaid, 2) ?> MAD</td>
                    <td><?= number_format($grandRemaining, 2) ?> MAD</td>
                    <td></td>
                </tr>
            </tfoot>
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
