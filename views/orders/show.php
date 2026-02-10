<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="container mt-4">
    <?php
        require_once 'models/Order.php';
        require_once 'models/Payment.php';
        require_once 'models/Shipment.php';
        $totalAmount = Order::getTotalAmount($order['id']);
        $totalPaid = Payment::totalAllocatedToOrder($order['id']);
        $remaining = max($totalAmount - $totalPaid, 0);
        $progress = Shipment::getOrderProgress($order['id']);
        $statusBadge = $progress['remaining'] === 0 ? 'bg-success' : ($progress['sent'] > 0 ? 'bg-warning text-dark' : 'bg-secondary');
    ?>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h2 class="mb-0">🧾 Commande #<?= $order['id'] ?></h2>
        <div class="d-flex gap-2">
            <a href="?route=shipments/create&order_id=<?= $order['id'] ?>" class="btn btn-sm btn-success">➕ Créer un envoi</a>
            <a href="?route=orders" class="btn btn-sm btn-secondary">← Retour</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted">Total commande</div>
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
                    <div class="h5 mb-0"><?= number_format($remaining, 2) ?> MAD</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted">Avancement envoi</div>
                    <div class="h5 mb-1"><?= $progress['sent'] ?>/<?= $progress['ordered'] ?></div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar <?= $progress['remaining'] === 0 ? 'bg-success' : 'bg-info' ?>" role="progressbar" style="width: <?= $progress['percent'] ?>%"></div>
                    </div>
                    <small class="text-muted"><?= $progress['percent'] ?>% | Reste: <?= $progress['remaining'] ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-semibold">Produit</div>
                <div class="card-body">
                    <?php if (!empty($product['image_path'])): ?>
                        <img src="<?= htmlspecialchars($product['image_path']) ?>" class="img-fluid rounded border shadow-sm mb-3" alt="Produit">
                    <?php else: ?>
                        <div class="text-muted mb-3">Aucune image disponible</div>
                    <?php endif; ?>
                    <h5 class="mb-1"><?= htmlspecialchars($product['name']) ?></h5>
                    <div class="text-muted mb-2"><?= htmlspecialchars($product['category']) ?></div>
                    <div><?= nl2br(htmlspecialchars($product['description'])) ?></div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-semibold">Informations commande</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6"><strong>Fournisseur :</strong> <?= htmlspecialchars($order['supplier_name'] ?? $order['supplier_id']) ?></div>
                        <div class="col-md-6"><strong>Pays :</strong> <?= htmlspecialchars($order['destination_country']) ?></div>
                        <div class="col-md-6"><strong>Société :</strong> <?= !empty($order['company_name']) ? e($order['company_name']) : '<span class="text-muted">—</span>' ?></div>
                        <div class="col-md-6"><strong>Statut :</strong> <span class="badge <?= $statusBadge ?>"><?= e($progress['status']) ?></span></div>
                        <div class="col-md-6"><strong>Quantité totale :</strong> <?= $order['total_quantity'] ?></div>
                        <div class="col-md-6"><strong>Date création :</strong> <?= $order['created_at'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ℹ️ Infos commande -->
    <div class="mt-4">
    <h4>🧾 Variantes commandées</h4>
    <?php if (!empty($orderItems) && is_array($orderItems)): ?>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Taille</th>
                        <th>Couleur</th>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['size']) ?></td>
                            <td><?= htmlspecialchars($item['color']) ?></td>
                            <td><?= $item['quantity_ordered'] ?></td>
                            <td><?= number_format($item['unit_price'], 2) ?> MAD</td>
                            <td><?= number_format($item['quantity_ordered'] * $item['unit_price'], 2) ?> MAD</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">Aucun produit trouvé dans cette commande.</p>
    <?php endif; ?>
    </div>

    <div class="row g-3 mt-2">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-semibold">💰 Paiements liés</div>
                <div class="card-body">
                    <?php if (empty($payments)): ?>
                        <p class="text-muted">Aucun paiement enregistré.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Montant affecté</th>
                                        <th>Méthode</th>
                                        <th>Partenaire</th>
                                        <th>Preuve</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $pay): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($pay['payment_date']) ?></td>
                                            <td><?= number_format($pay['amount_allocated'], 2) ?> MAD</td>
                                            <td><?= htmlspecialchars($pay['payment_method']) ?></td>
                                            <td><?= !empty($pay['partner_name']) ? e($pay['partner_name']) : '<span class="text-muted">—</span>' ?></td>
                                            <td>
                                                <?php if (!empty($pay['proof_file'])): ?>
                                                    <a href="<?= htmlspecialchars($pay['proof_file']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">📄 Reçu</a>
                                                <?php else: ?>
                                                    <span class="text-muted">Aucun</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-light fw-semibold">
                                        <td class="text-end">Total :</td>
                                        <td><?= number_format(array_sum(array_column($payments, 'amount_allocated')), 2) ?> MAD</td>
                                        <td colspan="3"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-semibold">🚚 Envois</div>
                <div class="card-body">
                    <?php if (empty($partialShipments)): ?>
                        <p class="text-muted">Aucun envoi enregistré.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Statut</th>
                                        <th>Qté envoyée</th>
                                        <th>Reçu</th>
                                        <th>Détail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($partialShipments as $shipment): ?>
                                        <?php
                                            $variants = Shipment::getVariants($shipment['id']);
                                            $sentQty = array_sum(array_column($variants, 'quantity_sent'));
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($shipment['shipment_date']) ?></td>
                                            <td><span class="badge <?= $shipment['status'] === 'Livré à destination' ? 'bg-success' : 'bg-secondary' ?>"><?= e($shipment['status']) ?></span></td>
                                            <td><?= $sentQty ?></td>
                                            <td>
                                                <?php if (!empty($shipment['receipt_path'])): ?>
                                                    <a href="<?= $shipment['receipt_path'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">📎 Reçu</a>
                                                <?php else: ?>
                                                    <span class="text-muted">Aucun</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="?route=shipments/show/<?= $shipment['id'] ?>" class="btn btn-sm btn-outline-secondary">Voir</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <?php
                                        $totalSent = 0;
                                        foreach ($partialShipments as $s) {
                                            $v = Shipment::getVariants($s['id']);
                                            $totalSent += array_sum(array_column($v, 'quantity_sent'));
                                        }
                                    ?>
                                    <tr class="table-light fw-semibold">
                                        <td class="text-end">Total :</td>
                                        <td></td>
                                        <td><?= $totalSent ?></td>
                                        <td colspan="2">
                                            Reste à envoyer : <?= max(Shipment::getOrderTotals($order['id']) - Shipment::getOrderTotalSent($order['id']), 0) ?>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>
