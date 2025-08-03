<?php include 'views/layout/header.php'; ?>

<div class="container mt-4">
    <h2>✏️ Modifier le transporteur</h2>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'];
            unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="POST" action="?route=transports/update/<?= $transport['id'] ?>">
            <div class="mb-3">
                <label for="name" class="form-label">🚛 Nom du transporteur</label>
                <input type="text" class="form-control" name="name" id="name" required
                       value="<?= htmlspecialchars($transport['name']) ?>">
            </div>

            <div class="mb-3">
                <label for="transport_type" class="form-label">🚚 Type de transport</label>
                <select name="transport_type" id="transport_type" class="form-select" required>
                    <option value="">-- Sélectionner --</option>
                    <?php
                    $types = ['Maritime', 'Aérien', 'Routier', 'Ferroviaire', 'Autre'];
foreach ($types as $type) {
    $selected = ($transport['transport_type'] === $type) ? 'selected' : '';
    echo "<option value=\"$type\" $selected>$type</option>";
}
?>
                </select>
            </div>

            <div class="mb-3">
                <label for="contact_info" class="form-label">📞 Contact</label>
                <input type="text" class="form-control" name="contact_info" id="contact_info"
                       value="<?= htmlspecialchars($transport['contact_info']) ?>">
            </div>



            <button type="submit" class="btn btn-success">💾 Enregistrer les modifications</button>
            <a href="?route=transports" class="btn btn-secondary">↩️ Retour</a>
        </form>

</div>

<?php include 'views/layout/footer.php'; ?>
