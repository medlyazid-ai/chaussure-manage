<?php
require_once 'auth_check.php';
include 'views/layout/header.php';

$flags = [
    "Guinée" => "🇬🇳",
    "Côte d'Ivoire" => "🇨🇮",
    "Mali" => "🇲🇱"
];

$flag = $flags[$order['destination_country']] ?? '';
$statusOptions = [
    'En attente de validation',
    'Validé',
    'En préparation',
    'Expédié',
    'En transit',
    'Arrivé à destination',
    'Livré à la destination',
    'En livraison locale',
    'Livré partiellement',
    'Reçu',
    'Retourné au fournisseur',
    'Annulé'
];

$statusColorClass = [
    'En attente de validation' => 'secondary',
    'Validé' => 'primary',
    'En préparation' => 'info',
    'Expédié' => 'warning',
    'En transit' => 'dark',
    'Arrivé à destination' => 'info',
    'Livré à la destination' => 'success',
    'En livraison locale' => 'primary',
    'Livré partiellement' => 'info',
    'Reçu' => 'success',
    'Retourné au fournisseur' => 'danger',
    'Annulé' => 'danger',
];
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h2 class="mb-0">📦 Envoi #<?= $shipment['id'] ?></h2>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-<?= $statusColorClass[$shipment['status']] ?? 'secondary' ?>">
                <?= htmlspecialchars($shipment['status']) ?>
            </span>
            <a href="?route=orders/show/<?= $shipment['order_id'] ?>" class="btn btn-sm btn-outline-primary">Voir commande</a>
            <a href="?route=suppliers/dashboard&id=<?= $order['supplier_id'] ?>" class="btn btn-sm btn-outline-secondary">Voir fournisseur</a>
            <a href="?route=shipments" class="btn btn-sm btn-secondary">← Retour</a>
        </div>
    </div>

    <?php
        $variants = Shipment::getVariants($shipment['id']);
        $sentQty = array_sum(array_column($variants, 'quantity_sent'));
        $orderTotal = Shipment::getOrderTotals($shipment['order_id']);
        $orderSent = Shipment::getOrderTotalSent($shipment['order_id']);
        $orderRemaining = max($orderTotal - $orderSent, 0);
        $percent = $orderTotal > 0 ? (int)round(($orderSent / $orderTotal) * 100) : 0;
        $productImage = Shipment::getProductImage($shipment['id']);
    ?>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted">Commande</div>
                    <div class="h5 mb-0">#<?= $shipment['order_id'] ?></div>
                    <small class="text-muted"><?= htmlspecialchars($shipment['shipment_date']) ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted">Envoyé (cet envoi)</div>
                    <div class="h5 mb-0"><?= $sentQty ?></div>
                    <small class="text-muted">pièces</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted">Avancement commande</div>
                    <div class="h5 mb-1"><?= $orderSent ?>/<?= $orderTotal ?></div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar <?= $orderRemaining === 0 ? 'bg-success' : 'bg-info' ?>" style="width: <?= $percent ?>%"></div>
                    </div>
                    <small class="text-muted"><?= $percent ?>% | reste <?= $orderRemaining ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted">Destination</div>
                    <div class="h6 mb-0"><?= $flag ?> <?= htmlspecialchars($order['destination_country']) ?></div>
                    <small class="text-muted"><?= htmlspecialchars($order['supplier_name']) ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white fw-semibold">🧭 Avancement (statuts)</div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($statusOptions as $s): ?>
                    <span class="badge <?= $shipment['status'] === $s ? 'bg-success' : 'bg-light text-dark' ?>">
                        <?= e($s) ?>
                    </span>
                <?php endforeach; ?>
            </div>
            <small class="text-muted d-block mt-2">Statut actuel mis en surbrillance.</small>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-semibold">🖼️ Produit envoyé</div>
                <div class="card-body">
                    <?php if ($productImage): ?>
                        <img src="<?= htmlspecialchars($productImage) ?>" alt="Produit envoyé" class="img-fluid rounded border">
                    <?php else: ?>
                        <div class="text-muted">Aucune image</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-semibold">ℹ️ Informations</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6"><strong>Fournisseur :</strong> <?= htmlspecialchars($order['supplier_name']) ?></div>
                        <div class="col-md-6"><strong>Pays :</strong> <?= $flag ?> <?= htmlspecialchars($order['destination_country']) ?></div>
                        <div class="col-md-6"><strong>Transport :</strong> <?= !empty($shipment['transport_name']) ? e($shipment['transport_name']) : '<span class="text-muted">—</span>' ?></div>
                        <div class="col-md-6"><strong>Type :</strong> <?= !empty($shipment['transport_type']) ? e($shipment['transport_type']) : '<span class="text-muted">—</span>' ?></div>
                        <div class="col-md-6"><strong>Contact :</strong> <?= !empty($shipment['contact_info']) ? e($shipment['contact_info']) : '<span class="text-muted">—</span>' ?></div>
                        <div class="col-md-6"><strong>Reçu :</strong>
                            <?php if (!empty($shipment['receipt_path'])): ?>
                                <a href="<?= htmlspecialchars($shipment['receipt_path']) ?>" target="_blank">Voir</a>
                            <?php else: ?>
                                <span class="text-muted">Aucun</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-3">
                        <strong>Remarques :</strong>
                        <div class="bg-light p-2 rounded"><?= nl2br(htmlspecialchars($shipment['notes'])) ?: "<em>Aucune remarque</em>" ?></div>
                    </div>

                    <?php if (!empty($shipment['delivery_comment'])): ?>
                        <div class="alert alert-warning mt-3">
                            <strong>📝 Commentaire lors de la livraison :</strong><br>
                            <?= nl2br(htmlspecialchars($shipment['delivery_comment'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($shipment['tracking_code']) || !empty($shipment['package_weight']) || !empty($shipment['transport_fee']) || !empty($shipment['package_image'])): ?>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-semibold">📦 Informations colis</div>
            <div class="card-body">
                <div class="row g-2">
                    <?php if (!empty($shipment['tracking_code'])): ?>
                        <div class="col-md-4"><strong>🔖 Code colis :</strong> <?= htmlspecialchars($shipment['tracking_code']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($shipment['package_weight'])): ?>
                        <div class="col-md-4"><strong>⚖️ Poids :</strong> <?= htmlspecialchars($shipment['package_weight']) ?> kg</div>
                    <?php endif; ?>
                    <?php if (!empty($shipment['transport_fee'])): ?>
                        <div class="col-md-4"><strong>💰 Frais :</strong> <?= htmlspecialchars($shipment['transport_fee']) ?> Dhs</div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($shipment['package_image'])): ?>
                    <div class="mt-3">
                        <strong>📷 Image du colis :</strong><br>
                        <img src="<?= htmlspecialchars($shipment['package_image']) ?>" alt="Colis" class="img-fluid rounded border" style="max-height: 260px;">
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white fw-semibold">🔁 Mise à jour du statut</div>
        <div class="card-body">
            <form method="POST" action="?route=shipments/update_status/<?= $shipment['id'] ?>" id="status-form">
                <?= csrf_field(); ?>
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <label for="status" class="form-label">Statut</label>
                        <select name="status" id="status" class="form-select" required onchange="toggleCommentField()">
                            <option value="">-- Sélectionner --</option>
                            <?php foreach ($statusOptions as $s): ?>
                                <option value="<?= $s ?>" <?= $shipment['status'] === $s ? 'selected' : '' ?>>
                                    <?= $s ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5" id="commentField" style="display: none;">
                        <label class="form-label">Commentaire</label>
                        <input type="text" name="delivery_comment" class="form-control" placeholder="Commentaire de livraison">
                    </div>
                    <div class="col-md-3 d-grid">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-success">✅ Mettre à jour</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <h4>📌 Détail des variantes envoyées</h4>
    <?php if (empty($items)): ?>
        <div class="alert alert-warning">Aucune variante envoyée dans cet envoi.</div>
    <?php else: ?>
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Taille</th>
                    <th>Couleur</th>
                    <th>Commandée</th>
                    <th>Total déjà envoyé</th>
                    <th>Envoyée ici</th>
                    <th>Restant</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <?php
                        $alreadySent = Shipment::getTotalSentForItem($item['order_item_id']);
                    $rest = $item['quantity_ordered'] - $alreadySent;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($item['size']) ?></td>
                        <td><?= htmlspecialchars($item['color']) ?></td>
                        <td><?= $item['quantity_ordered'] ?></td>
                        <td><?= $alreadySent ?></td>
                        <td class="fw-bold text-success"><?= $item['quantity_sent'] ?></td>
                        <td>
                            <span class="badge <?= $rest > 0 ? 'bg-danger' : 'bg-success' ?>">
                                <?= $rest ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="table-light fw-semibold">
                    <td colspan="2" class="text-end">Totaux :</td>
                    <td><?= array_sum(array_column($items, 'quantity_ordered')) ?></td>
                    <td><?= array_sum(array_map(fn($i) => Shipment::getTotalSentForItem($i['order_item_id']), $items)) ?></td>
                    <td><?= array_sum(array_column($items, 'quantity_sent')) ?></td>
                    <td><?= max(array_sum(array_column($items, 'quantity_ordered')) - array_sum(array_map(fn($i) => Shipment::getTotalSentForItem($i['order_item_id']), $items)), 0) ?></td>
                </tr>
            </tfoot>
        </table>
    <?php endif; ?>

    <a href="?route=shipments" class="btn btn-secondary mt-3">← Retour à la liste des envois</a>
</div>


<script>
    function toggleCommentField() {
        const status = document.getElementById('status').value;
        const commentField = document.getElementById('commentField');
        if (status === 'Arrivé à destination') {
            commentField.style.display = 'block';
        } else {
            commentField.style.display = 'none';
        }
    }
    window.onload = toggleCommentField;
</script>

<?php include 'views/layout/footer.php'; ?>
