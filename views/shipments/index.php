<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📦 Suivi des envois</h2>

        <form method="GET" onsubmit="return redirectToCreateShipment();">
            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <select name="order_id" id="order_id" class="form-select" required>
                        <option value="">-- Choisir une commande --</option>
                        <?php foreach ($availableOrders as $order): ?>
                        <option value="<?= $order['id'] ?>">
                            #<?= $order['id'] ?> – <?= htmlspecialchars($order['product_name']) ?> |
                            <?= htmlspecialchars($order['supplier_name']) ?> |
                            <?= $order['country_flag'] . ' ' . htmlspecialchars($order['destination_country']) ?>
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

    <form method="GET" class="row g-2 mb-3">
        <input type="hidden" name="route" value="shipments">
        <div class="col-md-3">
            <label class="form-label">Fournisseur</label>
            <select name="supplier_id" class="form-select">
                <option value="">-- Tous --</option>
                <?php foreach ($suppliers as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= (isset($_GET['supplier_id']) && $_GET['supplier_id'] == $s['id']) ? 'selected' : '' ?>>
                        <?= e($s['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Produit</label>
            <select name="product_id" class="form-select">
                <option value="">-- Tous --</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= (isset($_GET['product_id']) && $_GET['product_id'] == $p['id']) ? 'selected' : '' ?>>
                        <?= e($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Pays</label>
            <select name="country_id" class="form-select">
                <option value="">-- Tous --</option>
                <?php foreach ($countries as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (isset($_GET['country_id']) && $_GET['country_id'] == $c['id']) ? 'selected' : '' ?>>
                        <?= e($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Société</label>
            <select name="company_id" class="form-select">
                <option value="">-- Toutes --</option>
                <?php foreach ($companies as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (isset($_GET['company_id']) && $_GET['company_id'] == $c['id']) ? 'selected' : '' ?>>
                        <?= e($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-1">
            <label class="form-label">Du</label>
            <input type="date" name="date_from" class="form-control" value="<?= isset($_GET['date_from']) ? e($_GET['date_from']) : '' ?>">
        </div>
        <div class="col-md-1">
            <label class="form-label">Au</label>
            <input type="date" name="date_to" class="form-control" value="<?= isset($_GET['date_to']) ? e($_GET['date_to']) : '' ?>">
        </div>
        <div class="col-md-2 d-grid">
            <label class="form-label">&nbsp;</label>
            <button type="submit" class="btn btn-outline-secondary">🔍 Filtrer</button>
        </div>
        <div class="col-md-2 d-grid">
            <label class="form-label">&nbsp;</label>
            <?php
                $base = '?route=shipments';
                $qs = $_GET;
                $qs['view'] = ($view ?? 'grouped') === 'detail' ? 'grouped' : 'detail';
                $toggleLabel = ($view ?? 'grouped') === 'detail' ? 'Vue groupée' : 'Vue détaillée';
            ?>
            <a href="<?= $base . '&' . http_build_query($qs) ?>" class="btn btn-outline-primary"><?= $toggleLabel ?></a>
        </div>
    </form>

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
        <?php if (($view ?? 'grouped') === 'detail'): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Fournisseur</th>
                            <th>Produit</th>
                            <th>Pays</th>
                            <th>Société</th>
                            <th>Statut</th>
                            <th>Transport</th>
                            <th>Reçu</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rawShipments as $s): ?>
                        <tr>
                            <td>#<?= $s['id'] ?></td>
                            <td><?= date('d/m/Y', strtotime($s['shipment_date'])) ?></td>
                            <td><?= e($s['supplier_name']) ?></td>
                            <td><?= e($s['product_name']) ?></td>
                            <td><?= e($s['country_flag']) . ' ' . e($s['destination_country']) ?></td>
                            <td><?= !empty($s['company_name']) ? e($s['company_name']) : '<span class="text-muted">—</span>' ?></td>
                            <td><?= e($s['status']) ?></td>
                            <td><?= !empty($s['transport_name']) ? e($s['transport_name']) : '<span class="text-muted">—</span>' ?></td>
                            <td>
                                <?php if (!empty($s['receipt_path'])): ?>
                                    <a href="<?= e($s['receipt_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">📎 Voir</a>
                                <?php else: ?>
                                    <span class="text-muted">Aucun</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap">
                                <a href="?route=shipments/show/<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary">Voir</a>
                                <button class="btn btn-sm btn-outline-secondary" onclick="toggleDetails(<?= $s['id'] ?>)" id="btn-toggle-<?= $s['id'] ?>">🔽 Détails</button>
                                <form method="POST" action="?route=shipments/delete/<?= $s['id'] ?>" class="d-inline">
                                    <?= csrf_field(); ?>
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet envoi ?')">🗑️</button>
                                </form>
                            </td>
                        </tr>
                        <?php $items = Shipment::itemsWithDetails($s['id']); ?>
                        <tr id="details-<?= $s['id'] ?>" style="display: none; background-color: #f8f9fa;">
                            <td colspan="10">
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
                                                <?php $totalSent = Shipment::getTotalSentByOrderItem($v['order_item_id']); ?>
                                                <?php $reste = $v['quantity_ordered'] - $totalSent; ?>
                                                <tr>
                                                    <td><?= e($v['size']) ?></td>
                                                    <td><?= e($v['color']) ?></td>
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
        <?php else: ?>
        <?php foreach ($shipments as $supplier => $orders): ?>
            <div class="mb-5">
                <h4 class="bg-primary text-white p-2 rounded"><i class="bi bi-truck"></i> Fournisseur : <?= htmlspecialchars($supplier) ?></h4>

                <?php foreach ($orders as $orderLabel => $envois): ?>
                    <?php $country = $envois[0]['destination_country'] ?? ''; ?>
                    <?php $flag = $flags[$country] ?? ''; ?>
                    <?php $company = $envois[0]['company_name'] ?? ''; ?>
                    <?php $productName = $envois[0]['product_name'] ?? ''; ?>
                    <?php $orderId = $envois[0]['order_id'] ?? null; ?>
                    <?php $progress = $orderId ? Shipment::getOrderProgress($orderId) : ['ordered'=>0,'sent'=>0,'remaining'=>0,'percent'=>0,'status'=>'N/A']; ?>

                    <div class="card mb-3 shadow-sm border-0">
                        <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                🧾 <?= htmlspecialchars($orderLabel) ?> - <?= $flag . ' ' . htmlspecialchars($country) ?>
                                <?php if ($company): ?> | 🏢 <?= e($company) ?><?php endif; ?>
                                <?php if ($productName): ?> | 👟 <?= e($productName) ?><?php endif; ?>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge <?= $progress['remaining'] === 0 ? 'bg-success' : ($progress['sent'] > 0 ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                                    <?= e($progress['status']) ?>
                                </span>
                                <span class="text-muted small">
                                    <?= $progress['sent'] ?>/<?= $progress['ordered'] ?> envoyés
                                </span>
                            </div>
                        </div>
                        <div class="card-body border-bottom">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-9">
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar <?= $progress['remaining'] === 0 ? 'bg-success' : 'bg-info' ?>" role="progressbar" style="width: <?= $progress['percent'] ?>%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between small text-muted mt-1">
                                        <span>Progression: <?= $progress['percent'] ?>%</span>
                                        <span>Reste: <?= $progress['remaining'] ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3 text-md-end">
                                    <a href="?route=orders/show/<?= $orderId ?>" class="btn btn-sm btn-outline-primary">Voir commande</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead class="table-secondary">
                                    <tr>
                                        <th>#</th>
                                        <th>Image</th>
                                        <th>Date d'envoi</th>
                                        <th>Statut</th>
                                        <th>Transport</th>
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
                                                <span class="badge <?= $s['status'] === 'Livré à destination' ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= e($s['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= !empty($s['transport_name']) ? e($s['transport_name']) : '<span class="text-muted">—</span>' ?></td>
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
                                                            <?= csrf_field(); ?>
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
                                            <td colspan="7">
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
    <?php endif; ?>

    <?= render_pagination($page ?? 1, $totalPages ?? 1, array_merge($_GET, ['route' => 'shipments'])) ?>
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
