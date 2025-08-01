<?php include 'views/layout/header.php'; ?>

<div class="container mt-4">
    <h2>🧾 Créer une facture client</h2>
    <p class="text-muted">Sélectionnez d'abord un pays pour ne voir que les variantes disponibles en stock.</p>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="GET" action="">
        <input type="hidden" name="route" value="client_sales/create">
        
        <div class="mb-3">
            <label for="country_id" class="form-label">🌍 Choisir un pays</label>
            <select name="country_id" id="country_id" class="form-select" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach ($countries as $country): ?>
                    <option value="<?= $country['id'] ?>">
                        <?= htmlspecialchars($country['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">➡️ Continuer</button>
    </form>
</div>

<?php include 'views/layout/footer.php'; ?>
