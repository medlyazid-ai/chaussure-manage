<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Liste des produits</h2>
    <a href="?route=products/create" class="btn btn-primary">➕ Ajouter un produit</a>
</div>

<form method="GET" class="row g-2 mb-3">
    <input type="hidden" name="route" value="products">
    <div class="col-md-5">
        <input type="text" name="search" class="form-control" placeholder="Rechercher un produit..." value="<?= isset($_GET['search']) ? e($_GET['search']) : '' ?>">
    </div>
    <div class="col-md-4">
        <select name="category" class="form-select">
            <option value="">-- Toutes les catégories --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= e($cat) ?>" <?= (isset($_GET['category']) && $_GET['category'] === $cat) ? 'selected' : '' ?>>
                    <?= e($cat) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <button class="btn btn-outline-secondary w-100" type="submit">🔍 Filtrer</button>
    </div>
</form>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Image</th>
                <th>Nom</th>
                <th>Variantes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $product): ?>
            <tr>
                <td>
                    <?php if (!empty($product['image_path'])): ?>
                        <img src="<?= $product['image_path'] ?>" class="img-fluid rounded shadow" style="max-width: 200px;">
                    <?php else: ?>
                        <span class="text-muted">Aucune image</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($product['name']) ?></td>
                <td>
                    <ul class="mb-0">
                        <?php if (empty($product['variants'])): ?>
                            <div class="alert alert-info">Aucune variants enregistrée pour l’instant.</div>
                        <?php else: ?>
                            <?php foreach ($product['variants'] as $variant): ?>
                                <li><?= htmlspecialchars($variant['size']) ?> : <?= (int)($variant['stock_quantity'] ?? 0) ?> en stock</li>
                            <?php endforeach; ?>
                        <?php endif; ?>    
                    </ul>
                </td>
                <td>
                    <a href="?route=products/edit/<?= $product['id'] ?>" class="btn btn-sm btn-warning">✏️ Modifier</a>

                    <!-- Bouton pour ouvrir modale -->
                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalDelete<?= $product['id'] ?>">🗑️ Supprimer</button>

                    <!-- Modale Bootstrap -->
                    <div class="modal fade" id="modalDelete<?= $product['id'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $product['id'] ?>" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                          <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="modalLabel<?= $product['id'] ?>">Supprimer produit</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            Supprimer le produit <strong><?= htmlspecialchars($product['name']) ?></strong> ?
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <form method="POST" action="?route=products/delete/<?= $product['id'] ?>">
                                <?= csrf_field(); ?>
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

<?= render_pagination($page ?? 1, $totalPages ?? 1, array_merge($_GET, ['route' => 'products'])) ?>

<?php include 'views/layout/footer.php'; ?>
