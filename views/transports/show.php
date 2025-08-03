<?php include 'views/layout/header.php'; ?>

<div class="container mt-4">
    <h2>📝 Détails du transporteur</h2>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($transport['name']) ?></h5>
            <p><strong>Type :</strong> <?= htmlspecialchars($transport['transport_type']) ?></p>
            <p><strong>Description :</strong> <?= nl2br(htmlspecialchars($transport['description'])) ?></p>
            <p><strong>Ajouté le :</strong> <?= date('d/m/Y H:i', strtotime($transport['created_at'])) ?></p>
        </div>
    </div>

    <a href="?route=transports/edit/<?= $transport['id'] ?>" class="btn btn-warning mt-3">✏️ Modifier</a>
    <a href="?route=transports" class="btn btn-secondary mt-3">⬅️ Retour</a>
</div>

<?php include 'views/layout/footer.php'; ?>
