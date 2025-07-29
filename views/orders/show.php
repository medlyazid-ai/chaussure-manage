<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="container mt-4">
    <h2>📦 Détail de la commande #<?= $order['id'] ?></h2>

    <a href="?route=shipments/create&order_id=<?= $order['id'] ?>" class="btn btn-sm btn-success">➕ Créer un envoi</a>

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>Fournisseur :</strong> <?= htmlspecialchars($order['supplier_name'] ?? $order['supplier_id']) ?></p>
            <p><strong>Pays de destination :</strong> <?= htmlspecialchars($order['destination_country']) ?></p>
            <p><strong>Statut :</strong> <?= htmlspecialchars($order['status']) ?></p>
            <p><strong>Quantité totale :</strong> <?= $order['total_quantity'] ?></p>
            <p><strong>Date de création :</strong> <?= $order['created_at'] ?></p>
        </div>
    </div>

    <h4>🧾 Produits commandés</h4>
    <?php if (!empty($orderItems) && is_array($orderItems)): ?>
        <ul class="list-group mb-4">
            <?php foreach ($orderItems as $item): ?>
                <li class="list-group-item">
                    Taille : <?= htmlspecialchars($item['size']) ?>, 
                    Couleur : <?= htmlspecialchars($item['color']) ?>, 
                    Quantité : <?= $item['quantity_ordered'] ?>, 
                    Prix unitaire : <?= number_format($item['unit_price'], 2) ?> DH
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="text-muted">Aucun produit trouvé dans cette commande.</p>
    <?php endif; ?>

    <h4>💰 Paiements liés</h4>
    <?php if (empty($payments)): ?>
        <p class="text-muted">Aucun paiement enregistré.</p>
    <?php else: ?>
        <ul class="list-group mb-4">
            <?php foreach ($payments as $pay): ?>
                <li class="list-group-item">
                    Montant : <?= number_format($pay['amount'], 2) ?> DH, 
                    Méthode : <?= htmlspecialchars($pay['payment_method']) ?>, 
                    Date : <?= htmlspecialchars($pay['payment_date']) ?>
                    <?php if (!empty($pay['receipt_path'])): ?>
                        - <a href="<?= htmlspecialchars($pay['receipt_path']) ?>" target="_blank">📄 Voir reçu</a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>


    <h4>🚚 Envois partiels</h4>
    <?php if (empty($partialShipments)): ?>
        <p class="text-muted">Aucun envoi partiel enregistré.</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($partialShipments as $shipment): ?>
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong>Envoi du <?= htmlspecialchars($shipment['shipment_date']) ?></strong>
                        </div>
                        <div class="card-body">
                            <p><strong>Remarques :</strong> <?= nl2br(htmlspecialchars($shipment['notes'])) ?></p>
                            <?php if (!empty($shipment['receipt_path'])): ?>
                                <p><strong>Reçu :</strong><br>
                                <a href="<?= $shipment['receipt_path'] ?>" target="_blank">
                                    <img src="<?= $shipment['receipt_path'] ?>" alt="Reçu" class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                                </a></p>
                            <?php endif; ?>
                            <!-- Optionnel : afficher ici les variantes envoyées si besoin -->
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'views/layout/footer.php'; ?>
