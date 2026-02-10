<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="container mt-4">
    <h2>📦 Ajouter un envoi partiel</h2>

    <div class="alert alert-info">
        Commande <strong>#<?= $order['id'] ?></strong> pour le fournisseur <strong><?= htmlspecialchars($order['supplier_name']) ?></strong>
        <br>Destination : <strong><?= htmlspecialchars($order['destination_country']) ?></strong>
        <br>Statut actuel : <strong><?= htmlspecialchars($order['status']) ?></strong>
    </div>

    <form method="POST" action="?route=shipments/store" enctype="multipart/form-data">
        <?= csrf_field(); ?>

        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

        <div class="mb-3">
            <label for="shipment_date" class="form-label">📅 Date d'envoi</label>
                <input type="date" class="form-control" name="shipment_date" value="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="mb-3">
            <label for="transport_id" class="form-label">🚚 Transporteur</label>
            <select name="transport_id" id="transport_id" class="form-select" required>
                <option value="">-- Choisir un transporteur --</option>
                <?php foreach ($transports as $t): ?>
                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?> (<?= $t['transport_type'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>


        <div class="mb-3">
            <label for="notes" class="form-label">📝 Notes (optionnel)</label>
            <textarea class="form-control" name="notes" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label for="receipt" class="form-label">🧾 Reçu (image ou PDF)</label>
            <input type="file" class="form-control" name="receipt" accept="image/*,application/pdf" >
        </div>

        <div class="mb-3">
            <label for="tracking_code" class="form-label">🔖 Code colis (tracking)</label>
            <input type="text" class="form-control" name="tracking_code" placeholder="Ex: TRK123456">
        </div>

        <div class="mb-3">
            <label for="package_weight" class="form-label">⚖️ Poids du colis (kg)</label>
            <input type="number" step="0.01" class="form-control" name="package_weight" placeholder="Ex: 2.5">
        </div>

        <div class="mb-3">
            <label for="transport_fee" class="form-label">💰 Frais de transport (Dhs ou €)</label>
            <input type="number" step="0.01" class="form-control" name="transport_fee" placeholder="Ex: 150.00">
        </div>

        <div class="mb-3">
            <label for="package_image" class="form-label">📷 Image du colis</label>
            <input type="file" class="form-control" name="package_image" accept="image/*">
        </div>


        <h5 class="mt-4">📌 Variantes à envoyer</h5>

        <?php if (empty($orderItems)): ?>
            <div class="alert alert-warning">Aucune variante trouvée pour cette commande.</div>
        <?php else: ?>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Taille</th>
                        <th>Couleur</th>
                        <th>Quantité commandée</th>
                        <th>Quantité déjà expédiée</th>
                        <th>Quantité à envoyer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                        <?php
                            $alreadySent = $item['quantity_sent'] ?? 0;
                        $remaining = $item['quantity_ordered'] - $alreadySent;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($item['size']) ?></td>
                            <td><?= htmlspecialchars($item['color']) ?></td>
                            <td><?= $item['quantity_ordered'] ?></td>
                            <td><?= $alreadySent ?></td>
                            <td>
                                <?php if ($remaining > 0): ?>
                                    <input type="number" name="shipment_items[<?= $item['id'] ?>][quantity_sent]"
                                           class="form-control" min="0" max="<?= $remaining ?>" value="0">
                                    <input type="hidden" name="shipment_items[<?= $item['id'] ?>][order_item_id]"
                                           value="<?= $item['id'] ?>">
                                <?php else: ?>
                                    <span class="text-success">✅ Déjà entièrement expédié</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>


        <a href="?route=orders/show/<?= $order['id'] ?>" class="btn btn-secondary">← Retour</a>
        <button type="submit" class="btn btn-success">✅ Créer l'envoi partiel</button>
    </form>
</div>

<?php include 'views/layout/footer.php'; ?>
