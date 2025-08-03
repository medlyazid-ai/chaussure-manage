<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>💰 Liste des paiements fournisseurs</h2>
    <a href="?route=payments/create" class="btn btn-success">➕ Nouveau paiement</a>
</div>

<form method="GET" class="mb-3">
    <input type="hidden" name="route" value="payments">
    <div class="input-group">
        <input type="text" name="search" class="form-control" placeholder="Rechercher un fournisseur..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        <button class="btn btn-outline-secondary" type="submit">🔍</button>
    </div>
</form>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (empty($payments)): ?>
    <div class="alert alert-info">Aucun paiement enregistré pour l’instant.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle shadow-sm">
            <thead class="table-dark">
                <tr>
                    <th>#ID</th>
                    <th>Fournisseur</th>
                    <th>Date</th>
                    <th>Montant payé</th>
                    <th>Méthode</th>
                    <th>Commandes concernées</th>
                    <th>Montant alloué</th>
                    <th>Preuve</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $p):
                    $allocations = Payment::allocationsByPayment($p['id']);
                    $allocatedTotal = 0;
                    $destinations = [];

                    foreach ($allocations as $a) {
                        $allocatedTotal += $a['amount_allocated'];
                        $destinations[] = $a['destination_country'];
                    }

                    $uniqueDestinations = implode(', ', array_unique($destinations));
                    ?>
                    <tr>
                        <td>#<?= $p['id'] ?></td>
                        <td><strong><?= htmlspecialchars($p['supplier_name']) ?></strong></td>
                        <td><?= date('d/m/Y', strtotime($p['payment_date'])) ?></td>
                        <td><span class="badge bg-success"><?= number_format($p['amount'], 2) ?> MAD</span></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($p['payment_method']) ?></span></td>
                        <td>
                            <?php if (count($allocations) > 0): ?>
                                <span class="badge bg-primary"><?= count($allocations) ?> commande(s)</span><br>
                                <small><?= $uniqueDestinations ?></small>
                            <?php else: ?>
                                <span class="text-muted">Aucune</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= number_format($allocatedTotal, 2) ?> MAD
                            <br><small class="text-muted"><?= number_format($p['amount'] - $allocatedTotal, 2) ?> MAD restant</small>
                        </td>
                        <td>
                            <?php if (!empty($p['proof_file'])): ?>
                                <a href="<?= htmlspecialchars($p['proof_file']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Voir</a>
                            <?php else: ?>
                                <span class="text-muted">Aucune</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <!-- Bouton de déclenchement -->
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDeletePayment<?= $p['id'] ?>">
                                🗑️ Supprimer
                            </button>

                        </td>

                        <!-- Modal de suppression -->
                        <div class="modal fade" id="modalDeletePayment<?= $p['id'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $p['id'] ?>" aria-hidden="true">
                          <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                              <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title" id="modalLabel<?= $p['id'] ?>">Supprimer le paiement #<?= $p['id'] ?></h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                              </div>
                              <div class="modal-body">
                                Supprimer le paiement <strong>#<?= $p['id'] ?></strong> effectué le <strong><?= date('d/m/Y', strtotime($p['payment_date'])) ?></strong> ?
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <form method="POST" action="?route=payments/delete/<?= $p['id'] ?>">
                                    <button type="submit" class="btn btn-danger">Oui, supprimer</button>
                                </form>
                              </div>
                            </div>
                          </div>
                        </div>

                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include 'views/layout/footer.php'; ?>
