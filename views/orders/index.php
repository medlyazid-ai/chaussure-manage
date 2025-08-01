<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>
<?php require_once 'models/Order.php'; ?>
<?php require_once 'models/Payment.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>📦 Liste des commandes</h2>
    <a href="?route=orders/create" class="btn btn-success">➕ Créer une commande</a>
</div>

<form method="GET" class="row mb-4">
    <input type="hidden" name="route" value="orders">
    
    <div class="col-md-4">
        <label class="form-label">👤 Fournisseur</label>
        <select name="supplier_id" class="form-select">
            <option value="">-- Tous --</option>
            <?php foreach ($suppliers as $s): ?>
                <option value="<?= $s['id'] ?>" <?= (isset($_GET['supplier_id']) && $_GET['supplier_id'] == $s['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">📌 Statut</label>
        <select name="status" class="form-select">
            <option value="">-- Tous --</option>
            <?php foreach (['Initial', 'Validé et en cours de production', 'Envoi partiel', 'Envoi complet', 'Livré à la destination'] as $status): ?>
                <option value="<?= $status ?>" <?= (isset($_GET['status']) && $_GET['status'] === $status) ? 'selected' : '' ?>>
                    <?= $status ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-4 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100">🔍 Filtrer</button>
    </div>
</form>


<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (empty($orders)): ?>
    <div class="alert alert-info">Aucune commande enregistrée pour l’instant.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle shadow-sm">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Fournisseur</th>
                    <th>Pays</th>
                    <th>Quantité</th>
                    <th>Total</th>
                    <th>Payé</th>
                    <th>Statut</th>
                    <th>Créée le</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $flags = [
                        "Guinée" => "🇬🇳",
                        "Côte d'Ivoire" => "🇨🇮",
                        "Mali" => "🇲🇱"
                    ];
                ?>
                <?php
                    $totalAll = 0;
                    $totalPaidAll = 0;
                    $totalQtyAll = 0;
                    ?>

                    <?php foreach ($orders as $order): ?>
                        <?php
                            $total = Order::getTotalAmount($order['id']);
                            $paid = Payment::totalAllocatedToOrder($order['id']);
                            $totalAll += $total;
                            $totalPaidAll += $paid;
                            $totalQtyAll += $order['total_quantity'];

                            $flag = $flags[$order['destination_country']] ?? '';
                            $badge = match ($order['status']) {
                                'Initial' => 'secondary',
                                'Validé et en cours de production' => 'warning',
                                'Envoi partiel' => 'info',
                                'Envoi complet' => 'primary',
                                'Livré à la destination' => 'success',
                                default => 'dark'
                            };
                            $variants = Order::orderItems($order['id']);
                        ?>

                    <?php
                        $total = Order::getTotalAmount($order['id']);
                        $paid = Payment::totalAllocatedToOrder($order['id']);
                        $flag = $flags[$order['destination_country']] ?? '';
                        $badge = match ($order['status']) {
                            'Initial' => 'secondary',
                            'Validé et en cours de production' => 'warning',
                            'Envoi partiel' => 'info',
                            'Envoi complet' => 'primary',
                            'Livré à la destination' => 'success',
                            default => 'dark'
                        };
                        $variants = Order::orderItems($order['id']);
                    ?>
                    <tr>
                        <td>#<?= $order['id'] ?></td>
                        <td><?= htmlspecialchars($order['supplier_name']) ?></td>
                        <td><?= $flag . ' ' . htmlspecialchars($order['destination_country']) ?></td>
                        <td><?= $order['total_quantity'] ?></td>
                        <td><?= number_format($total, 2) ?> MAD</td>
                        <td><?= number_format($paid, 2) ?> MAD</td>
                        <td><span class="badge bg-<?= $badge ?>">
                            <button class="btn btn-sm btn-<?= $badge ?>" data-bs-toggle="modal" data-bs-target="#modalStatus<?= $order['id'] ?>" title="Changer statut">
                                <?= htmlspecialchars($order['status']) ?>
                                <i class="bi bi-arrow-repeat">✏️</i>
                            </button></span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                        <td class="text-nowrap">
                            <a href="?route=orders/show/<?= $order['id'] ?>" class="btn btn-sm btn-primary">👁️</a>
                                <a href="?route=orders/edit/<?= $order['id'] ?>" class="btn btn-sm btn-warning" title="Modifier">
                                    <i class="bi bi-pencil">✏️</i>
                                </a>
                            <button class="btn btn-sm btn-info" onclick="toggleDetails(<?= $order['id'] ?>)">🧩</button>
                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalDelete<?= $order['id'] ?>">🗑️</button>



                            <div class="modal fade" id="modalStatus<?= $order['id'] ?>" tabindex="-1" aria-labelledby="statusLabel<?= $order['id'] ?>" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                  <form method="POST" action="?route=orders/update-status/<?= $order['id'] ?>">
                                    <div class="modal-header bg-dark text-white">
                                      <h5 class="modal-title" id="statusLabel<?= $order['id'] ?>">Modifier le statut de la commande #<?= $order['id'] ?></h5>
                                      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                      <label for="statusSelect<?= $order['id'] ?>" class="form-label">Nouveau statut</label>
                                      <select name="status" id="statusSelect<?= $order['id'] ?>" class="form-select" required>
                                        <?php foreach (['Initial', 'Validé et en cours de production', 'Envoi partiel', 'Envoi complet', 'Livré à la destination'] as $status): ?>
                                          <option value="<?= $status ?>" <?= $status === $order['status'] ? 'selected' : '' ?>><?= $status ?></option>
                                        <?php endforeach; ?>
                                      </select>
                                    </div>
                                    <div class="modal-footer">
                                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                      <button type="submit" class="btn btn-dark">Mettre à jour</button>
                                    </div>
                                  </form>
                                </div>
                              </div>
                            </div>


                            <!-- Modal suppression -->
                            <div class="modal fade" id="modalDelete<?= $order['id'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $order['id'] ?>" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-danger">
                                  <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title" id="modalLabel<?= $order['id'] ?>">Confirmation de suppression</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                  </div>
                                  <div class="modal-body">
                                    Supprimer la commande <strong>#<?= $order['id'] ?></strong> ?
                                  </div>
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                    <form method="POST" action="?route=orders/delete/<?= $order['id'] ?>" class="d-inline">
                                      <button type="submit" class="btn btn-danger">Oui</button>
                                    </form>
                                  </div>
                                </div>
                              </div>
                            </div>
                        </td>
                    </tr>

                    <!-- Détails des variantes -->
                    <tr id="details-<?= $order['id'] ?>" style="display: none; background: #f9f9f9;">
                        <td colspan="9">
                            <strong>📦 Détails des variantes :</strong>
                            <table class="table table-sm table-bordered mt-2">
                                <thead>
                                    <tr class="table-light">
                                        <th>Taille</th>
                                        <th>Couleur</th>
                                        <th>Quantité</th>
                                        <th>Prix unitaire</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($variants as $v): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($v['size']) ?></td>
                                            <td><?= htmlspecialchars($v['color']) ?></td>
                                            <td><?= $v['quantity_ordered'] ?></td>
                                            <td><?= number_format($v['unit_price'], 2) ?> MAD</td>
                                            <td><?= number_format($v['quantity_ordered'] * $v['unit_price'], 2) ?> MAD</td>
                                        </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                <?php endforeach ?>
                <tr class="table-warning fw-bold">
                    <td colspan="3" class="text-end">TOTAL GÉNÉRAL :</td>
                    <td><?= $totalQtyAll ?></td>
                    <td><?= number_format($totalAll, 2) ?> MAD</td>
                    <td><?= number_format($totalPaidAll, 2) ?> MAD</td>
                    <td colspan="3">
                        Reste à payer : <?= number_format($totalAll - $totalPaidAll, 2) ?> MAD
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
<?php endif; ?>

<script>
function toggleDetails(id) {
    const row = document.getElementById("details-" + id);
    row.style.display = row.style.display === "none" ? "table-row" : "none";
}
</script>

<?php include 'views/layout/footer.php'; ?>
