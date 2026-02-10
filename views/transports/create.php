<?php include 'views/layout/header.php'; ?>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= e($_SESSION['error']);
    unset($_SESSION['error']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= e($_SESSION['success']);
    unset($_SESSION['success']); ?></div>
<?php endif; ?>


<div class="container mt-4">
    <h2>➕ Ajouter un transporteur</h2>
    <form method="POST" action="?route=transports/store">
        <?= csrf_field(); ?>
        <div class="mb-3">
            <label for="name" class="form-label">🚛 Nom du transporteur</label>
            <input type="text" class="form-control" name="name" id="name" required>
        </div>

        <div class="mb-3">
            <label for="transport_type" class="form-label">🚚 Type de transport</label>
            <select name="transport_type" id="transport_type" class="form-select" required>
                <option value="">-- Sélectionner --</option>
                <option value="🚢 Maritime">🚢 Maritime</option>
                <option value="✈️ Aérien">✈️ Aérien</option>
                <option value="🚚 Routier">🚚 Routier</option>
                <option value="🚆 Ferroviaire">🚆 Ferroviaire</option>
                <option value="❓ Autre">❓ Autre</option>
            </select>

        </div>

        <div class="mb-3">
            <label for="contact_info" class="form-label">📞 Contact</label>
            <input type="text" class="form-control" name="contact_info" id="contact_info">
        </div>


        <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
    </form>
</div>

<?php include 'views/layout/footer.php'; ?>
