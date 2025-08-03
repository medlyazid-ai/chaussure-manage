<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📦 Suivi des envois par fournisseur</h2>

        <form method="GET" onsubmit="return redirectToCreateShipment();">
            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <select name="order_id" id="order_id" class="form-select" required>
                        <option value="">-- Choisir une commande --</option>
                        <?php foreach ($availableOrders as $order): ?>
<option value="<?= $order['id'] ?>">
    #<?= $order['id'] ?> - <?= htmlspecialchars($order['supplier_name']) ?>
    (<?= $order['country_flag'] . ' ' . htmlspecialchars($order['destination_country']) ?>)
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
        <?php
            $flags = [
                "Guinée" => "🇬🇳",
                "Côte d'Ivoire" => "🇨🇮",
                "Mali" => "🇲🇱"
            ];
        ?>
        <?php foreach ($shipments as $supplier => $orders): ?>
            <div class="mb-5">
                <h4 class="bg-primary text-white p-2 rounded"><i class="bi bi-truck"></i> Fournisseur : <?= htmlspecialchars($supplier) ?></h4>

                <?php foreach ($orders as $orderLabel => $envois): ?>
                    <?php $country = $envois[0]['destination_country'] ?? ''; ?>
                    <?php $flag = $flags[$country] ?? ''; ?>

                    <div class="card mb-3 shadow-sm">
                        <div class="card-header bg-light fw-bold">
                            🧾 <?= htmlspecialchars($orderLabel) ?> - <?= $flag . ' ' . htmlspecialchars($country) ?>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead class="table-secondary">
                                    <tr>
                                        <th>#</th>
                                        <th>Image</th>
                                        <th>Date d'envoi</th>
                                        <th>Reçu</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($envois as $s): ?>
                                        <?php $variants = Shipment::getVariants($s['id']); ?>
                                        <?php $total = array_sum(array_column($variants, 'quantity_sent')); ?>
                                        <?php $productImage = Shipment::getProductImage($s['id']); ?>
                                        <tr>
                                            <td>#<?= $s['id'] ?></td>
                                            <td>
                                                <?php if ($productImage): ?>
                                                    <img src="<?= htmlspecialchars($productImage) ?>" alt="Produit" style="height: 50px; border-radius: 5px;">
                                                <?php else: ?>
                                                    <span class="text-muted">Aucune</span>
                                                <?php endif; ?>
                                            </td>

                                            <td><?= date('d/m/Y', strtotime($s['shipment_date'])) ?></td>
                                            <td>
                                                <?php if (!empty($s['receipt_path'])): ?>
                                                    <a href="<?= htmlspecialchars($s['receipt_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">📎 Voir</a>
                                                <?php else: ?>
                                                    <span class="text-muted">Aucun</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-nowrap">
                                                <a href="?route=shipments/show/<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> Voir
                                                </a>

                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteShipment<?= $s['id'] ?>">
                                                    <i class="bi bi-trash"></i>🗑️ Supprimer
                                                </button>

                                                <button class="btn btn-sm btn-outline-secondary" onclick="toggleDetails(<?= $s['id'] ?>)" id="btn-toggle-<?= $s['id'] ?>">
                                                    🔽 Détails
                                                </button>

                                                <!-- Modal de suppression -->
                                                <div class="modal fade" id="modalDeleteShipment<?= $s['id'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $s['id'] ?>" aria-hidden="true">
                                                  <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                      <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title" id="modalLabel<?= $s['id'] ?>">Supprimer l'envoi #<?= $s['id'] ?></h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                      </div>
                                                      <div class="modal-body">
                                                        Supprimer l'envoi partiel #<?= $s['id'] ?> pour la commande <strong>#<?= $s['order_id'] ?></strong> ?
                                                      </div>
                                                      <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                        <form method="POST" action="?route=shipments/delete/<?= $s['id'] ?>">
                                                            <button type="submit" class="btn btn-danger">Oui, supprimer</button>
                                                        </form>
                                                      </div>
                                                    </div>
                                                  </div>
                                                </div>

                                            </td>
                                        </tr>

                                        <!-- Détails -->
                                        <?php $items = Shipment::itemsWithDetails($s['id']); ?>
                                        <tr id="details-<?= $s['id'] ?>" style="display: none; background-color: #f8f9fa;">
                                            <td colspan="4">
                                                <div class="p-3 border rounded">
                                                    <strong class="d-block mb-2">📦 Variantes envoyées :</strong>
                                                    <table class="table table-sm table-bordered mb-0">
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
                                                                    <td>
                                                                        <span class="badge <?= $reste > 0 ? 'bg-danger' : 'bg-success' ?>">
                                                                            <?= $reste ?>
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- JS pour redirection et toggle -->
<script>
function redirectToCreateShipment() {
    const select = document.getElementById('order_id');
    const orderId = select.value;
    if (!orderId) return false;
    window.location.href = '?route=shipments/create&order_id=' + orderId;
    return false;
}

function toggleDetails(id) {
    const row = document.getElementById("details-" + id);
    const btn = document.getElementById("btn-toggle-" + id);
    const visible = row.style.display === "table-row";
    row.style.display = visible ? "none" : "table-row";
    btn.innerHTML = visible ? "🔽 Détails" : "🔼 Masquer";
}
</script>

<?php include 'views/layout/footer.php'; ?>
