<?php include 'views/layout/header.php'; ?>

<div class="container mt-4">
    <h2>🚚 Transporteurs</h2>
    <a href="?route=transports/create" class="btn btn-success mb-3">➕ Ajouter un transporteur</a>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= e($_SESSION['success']);
        unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= e($_SESSION['error']);
        unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nom</th>
                    <th>Type</th>
                    <th>Contact</th>
                    <th>Date création</th>
                    <th class="text-center" style="width: 180px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transports as $transport): ?>
                    <tr>
                        <td><?= htmlspecialchars($transport['name']) ?></td>
                        <td><?= htmlspecialchars($transport['transport_type']) ?></td>
                        <td><?= htmlspecialchars($transport['contact_info']) ?></td>
                        <td><?= date('d/m/Y', strtotime($transport['created_at'])) ?></td>
                        <td class="text-center">
                            <a href="?route=transports/edit/<?= $transport['id'] ?>" class="btn btn-sm btn-primary">✏️ Modifier</a>

                            <!-- 🗑️ Suppression avec modale -->
                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalDelete<?= $transport['id'] ?>">🗑️ Supprimer</button>

                            <div class="modal fade" id="modalDelete<?= $transport['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-danger">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">Supprimer le transporteur</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            Confirmer la suppression de <strong><?= htmlspecialchars($transport['name']) ?></strong> ?
                                        </div>
                                        <div class="modal-footer">
                                            <form method="POST" action="?route=transports/delete/<?= $transport['id'] ?>">
                                                <?= csrf_field(); ?>
                                                <button type="submit" class="btn btn-danger">Oui, supprimer</button>
                                            </form>
                                            <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>
