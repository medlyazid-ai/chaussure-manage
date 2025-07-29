<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Liste des fournisseurs</h2>
    <a href="?route=suppliers/create" class="btn btn-primary">➕ Nouveau fournisseur</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle shadow-sm">
        <thead class="table-light">
            <tr>
                <th>Nom</th>
                <th>Contact</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Adresse</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($suppliers as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['name']) ?></td>
                <td><?= htmlspecialchars($s['contact_name']) ?></td>
                <td><?= htmlspecialchars($s['phone']) ?></td>
                <td><?= htmlspecialchars($s['email']) ?></td>
                <td><?= nl2br(htmlspecialchars($s['address'])) ?></td>
                <td class="text-nowrap">
                    <a href="?route=suppliers/dashboard&id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary">
                        📊 Voir dashboard
                    </a>
                    <a href="?route=suppliers/edit/<?= $s['id'] ?>" class="btn btn-sm btn-warning">✏️ Modifier</a>

                    <!-- Bouton déclencheur de la modale -->
                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalDelete<?= $s['id'] ?>">
                        🗑️ Supprimer
                    </button>

                    <!-- Modale de confirmation -->
                    <div class="modal fade" id="modalDelete<?= $s['id'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $s['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-danger">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title" id="modalLabel<?= $s['id'] ?>">Suppression du fournisseur</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                </div>
                                <div class="modal-body">
                                    Êtes-vous sûr de vouloir supprimer <strong><?= htmlspecialchars($s['name']) ?></strong> ?
                                    <br>Cette action est définitive.
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                    <form method="POST" action="?route=suppliers/delete/<?= $s['id'] ?>" class="d-inline">
                                        <button type="submit" class="btn btn-danger">Oui, supprimer</button>
                                    </form>
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

<?php include 'views/layout/footer.php'; ?>
