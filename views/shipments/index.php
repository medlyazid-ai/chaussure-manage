<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>
<?php require_once 'models/Shipment.php'; ?>
<?php require_once 'models/Order.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>🚚 Liste des envois partiels</h2>

    <form method="GET" class="mb-4" onsubmit="return redirectToCreateShipment();">
        <div class="row g-2 align-items-end">
            <div class="col-md-6">
                <select name="order_id" id="order_id" class="form-select" required>
                    <option value="">-- Choisir une commande --</option>
                    <?php foreach (Order::allWithSupplier() as $order): ?>
                        <option value="<?= $order['id'] ?>">
                            #<?= $order['id'] ?> - <?= htmlspecialchars($order['supplier_name']) ?> (<?= htmlspecialchars($order['destination_country']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-success">➕ Créer un envoi</button>
            </div>
        </div>
    </form>
</div>

<?php if (empty($shipments)): ?>
    <div class="alert alert-info">Aucun envoi enregistré.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle shadow-sm">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Commande</th>
                    <th>Fournisseur</th>
                    <th>Pays</th>
                    <th>Date d’envoi</th>
                    <th>Nombre de variantes</th>
                    <th>Reçu</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $totalSent = 0;
                    $flags = [
                        "Guinée" => "🇬🇳",
                        "Côte d'Ivoire" => "🇨🇮",
                        "Mali" => "🇲🇱"
                    ];
                ?>
                <?php foreach ($shipments as $s): ?>
                    <?php
                        $variants = Shipment::getVariants($s['id']);
                        $total = array_sum(array_column($variants, 'quantity_sent'));
                        $totalSent += $total;

                        $flag = $flags[$s['destination_country']] ?? '';
                    ?>
                    <tr>
                        <td>#<?= $s['id'] ?></td>
                        <td>#<?= $s['order_id'] ?></td>
                        <td><?= htmlspecialchars($s['supplier_name']) ?></td>
                        <td><?= $flag . ' ' . htmlspecialchars($s['destination_country']) ?></td>
                        <td><?= date('d/m/Y', strtotime($s['shipment_date'])) ?></td>
                        <td><?= $total ?> variantes</td>
                        <td>
                            <?php if (!empty($s['receipt_path'])): ?>
                                <a href="<?= htmlspecialchars($s['receipt_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">📎 Voir</a>
                            <?php else: ?>
                                <span class="text-muted">Aucun</span>
                            <?php endif ?>
                        </td>
                        <td class="text-nowrap">
                            <a href="?route=shipments/show/<?= $s['id'] ?>" class="btn btn-sm btn-primary">👁️</a>
                            <button class="btn btn-sm btn-info" onclick="toggleDetails(<?= $s['id'] ?>)">🔽 Détails</button>
                        </td>
                    </tr>

                    <!-- Détails variantes -->
                    <?php $items = Shipment::itemsWithDetails($s['id']); ?>
                        <tr id="details-<?= $s['id'] ?>" style="display: none; background-color: #f8f9fa;">
                            <td colspan="8">
                                <strong>📦 Variantes envoyées :</strong>
                                <table class="table table-sm table-bordered mt-2 mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Taille</th>
                                            <th>Couleur</th>
                                            <th>Qté envoyée</th>
                                            <th>Qté commandée</th>
                                            <th>Qté restante</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items as $v): ?>
                                            <?php
                                                $totalSent = Shipment::getTotalSentByOrderItem($v['order_item_id']);
                                                $reste = $v['quantity_ordered'] - $totalSent;

                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($v['size']) ?></td>
                                                <td><?= htmlspecialchars($v['color']) ?></td>
                                                <td><?= $v['quantity_sent'] ?></td>
                                                <td><?= $v['quantity_ordered'] ?></td>
                                                <td class="<?= $reste > 0 ? 'text-danger fw-bold' : 'text-success' ?>">
                                                    <?= $reste ?>
                                                </td>
                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>

                <?php endforeach; ?>

                <!-- Total général -->
                <tr class="table-warning fw-bold">
                    <td colspan="5" class="text-end">TOTAL GÉNÉRAL :</td>
                    <td><?= $totalSent ?> variantes</td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<script>
function redirectToCreateShipment() {
    const select = document.getElementById('order_id');
    const orderId = select.value;
    if (!orderId) return false;
    window.location.href = '?route=shipments/create&order_id=' + orderId;
    return false;
}
</script>
<script>
function toggleDetails(id) {
    const row = document.getElementById("details-" + id);
    row.style.display = (row.style.display === "none") ? "table-row" : "none";
}
</script>

<?php include 'views/layout/footer.php'; ?>
