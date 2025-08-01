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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>📦 Envoi partiel #<?= $shipment['id'] ?></h2>
        <span class="badge bg-<?= $statusColorClass[$shipment['status']] ?? 'secondary' ?>">
            <?= htmlspecialchars($shipment['status']) ?>
        </span>
    </div>

    <div class="card mb-4 shadow">
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-6"><strong>Commande associée :</strong> #<?= $shipment['order_id'] ?></div>
                <div class="col-md-6"><strong>Date d’envoi :</strong> <?= htmlspecialchars($shipment['shipment_date']) ?></div>
            </div>
            <div class="row mb-2">
                <div class="col-md-6"><strong>Fournisseur :</strong> <?= htmlspecialchars($order['supplier_name']) ?></div>
                <div class="col-md-6"><strong>Pays de destination :</strong> <?= $flag ?> <?= htmlspecialchars($order['destination_country']) ?></div>
            </div>
            <div class="mb-3">
                <strong>Remarques :</strong><br>
                <div class="bg-light p-2 rounded"><?= nl2br(htmlspecialchars($shipment['notes'])) ?: "<em>Aucune remarque</em>" ?></div>
            </div>

            <?php if (!empty($shipment['receipt_path'])): ?>
                <div class="mb-3">
                    <strong>📄 Reçu :</strong> 
                    <a href="<?= htmlspecialchars($shipment['receipt_path']) ?>" class="btn btn-sm btn-outline-primary ms-2" target="_blank">Voir le reçu</a>
                </div>
            <?php endif; ?>

            <form method="POST" action="?route=shipments/update_status/<?= $shipment['id'] ?>" class="mt-3">
                <label for="status" class="form-label"><strong>Changer le statut :</strong></label>
                <div class="input-group">
                    <select name="status" id="status" class="form-select">
                        <?php foreach ($statusOptions as $s): ?>
                            <option value="<?= $s ?>" <?= $shipment['status'] === $s ? 'selected' : '' ?>>
                                <?= $s ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-success">✅ Mettre à jour</button>
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
                        <td class="<?= $rest > 0 ? 'text-danger' : 'text-muted' ?>"><?= $rest ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="?route=shipments" class="btn btn-secondary mt-3">← Retour à la liste des envois</a>
</div>

<?php include 'views/layout/footer.php'; ?>
