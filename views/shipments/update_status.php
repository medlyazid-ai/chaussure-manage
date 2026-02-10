<?php include 'views/layout/header.php'; ?>

<div class="container mt-4">
    <a href="?route=shipments/show/<?= (int)$shipment['id'] ?>" class="btn btn-secondary mb-3">⬅️ Retour à l’envoi</a>

    <h3>Mise à jour du statut – Envoi #<?= (int)$shipment['id'] ?></h3>
    <p class="text-muted">
        Commande du <?php
$niceDate = '—';
if (!empty($shipment['doc_date'])) {
    try {
        $niceDate = (new DateTime($shipment['doc_date']))->format('d/m/Y');
    } catch (Exception $e) {
        $niceDate = htmlspecialchars($shipment['doc_date']);
    }
}
?>
<p class="text-muted">
    Commande du <?= $niceDate ?> – Destination : <?= htmlspecialchars($shipment['country_name']) ?>
</p>

    </p>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= e($_SESSION['error']);
        unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="POST" action="?route=shipments/update_status/<?= (int)$shipment['id'] ?>" class="mt-3">
        <?= csrf_field(); ?>
        <div class="mb-3">
            <label class="form-label">Statut</label>
            <select name="status" class="form-select" required>
                <?php foreach ($availableStatuses as $status): ?>
                    <option value="<?= htmlspecialchars($status) ?>" <?= ($shipment['status'] === $status ? 'selected' : '') ?>>
                        <?= htmlspecialchars($status) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Commentaire (livraison)</label>
            <textarea name="delivery_comment" class="form-control" rows="3" placeholder="Optionnel"><?= htmlspecialchars($shipment['delivery_comment'] ?? '') ?></textarea>
        </div>

        <button class="btn btn-primary">Mettre à jour</button>
    </form>
</div>

<?php include 'views/layout/footer.php'; ?>
