<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<?php
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
    'Arrivé à destination' => 'success',
    'En livraison locale' => 'primary',
    'Livré partiellement' => 'info',
    'Reçu' => 'success',
    'Retourné au fournisseur' => 'danger',
    'Annulé' => 'danger',
];
?>

<div class="container mt-4">
    <h2>📦 Détails de l’envoi partiel #<?= $shipment['id'] ?> 
        <span class="badge bg-<?= $statusColorClass[$shipment['status']] ?? 'secondary' ?>">
            <?= htmlspecialchars($shipment['status']) ?>
        </span>
    </h2>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <p><strong>Commande liée :</strong> #<?= $shipment['order_id'] ?></p>
            <p><strong>Fournisseur :</strong> <?= htmlspecialchars($order['supplier_name']) ?></p>
            <p><strong>Pays de destination :</strong> <?= $flag . ' ' . htmlspecialchars($order['destination_country']) ?></p>
            <p><strong>Date d’envoi :</strong> <?= htmlspecialchars($shipment['shipment_date']) ?></p>
            <p><strong>Statut :</strong> 
                <form method="POST" action="?route=shipments/update_status/<?= $shipment['id'] ?>" class="d-inline">
                    <select name="status" onchange="this.form.submit()" class="form-select d-inline w-auto">
                        <?php foreach ($statusOptions as $s): ?>
                            <option value="<?= $s ?>" <?= $shipment['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </p>
            <p><strong>Remarques :</strong><br><?= nl2br(htmlspecialchars($shipment['notes'])) ?></p>
            <?php if (!empty($shipment['receipt_path'])): ?>
                <p><strong>Reçu :</strong><br>
                    <a href="<?= htmlspecialchars($shipment['receipt_path']) ?>" target="_blank">📄 Voir le reçu</a>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <h4>📌 Variantes envoyées</h4>
    <?php if (empty($items)): ?>
        <p class="text-muted">Aucune variante enregistrée pour cet envoi.</p>
    <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Taille</th>
                    <th>Couleur</th>
                    <th>Quantité commandée</th>
                    <th>Total déjà envoyé</th>
                    <th>Envoyée dans cet envoi</th>
                    <th>Quantité restante</th>
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
                        <td><?= $item['quantity_sent'] ?></td>
                        <td><?= $rest ?></td>
                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    <?php endif; ?>

    <a href="?route=shipments" class="btn btn-secondary mt-3">← Retour aux envois</a>
</div>

<?php include 'views/layout/footer.php'; ?>
