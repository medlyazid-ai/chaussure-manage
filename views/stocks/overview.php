<?php include 'views/layout/header.php'; ?>

<div class="container mt-4">
    <h2>🏢 Vue d’ensemble des stocks par société</h2>
    <p class="text-muted">Cette vue résume les quantités reçues, vendues et ajustées. Le stock final est calculé dynamiquement.</p>

    <div class="row">
        <?php foreach ($companyStocks as $company): ?>
            <div class="col-12 col-md-6 col-lg-4 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white d-flex align-items-center">
                        <?php if (!empty($company['country_flag'])): ?>
                            <img src="uploads/flags/<?= htmlspecialchars($company['country_flag']) ?>" alt="🇨🇮" class="me-2" style="height: 20px;">
                        <?php endif; ?>
                        <h5 class="mb-0"><?= htmlspecialchars($company['company_name']) ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="text-muted mb-2"><?= htmlspecialchars($company['country_name']) ?></div>
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>📦 Total reçu :</span>
                                <strong class="text-success"><?= $company['total_received'] ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>🛒 Total vendu :</span>
                                <strong class="text-info"><?= $company['total_sold'] ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>✏️ Ajustement :</span>
                                <strong class="text-warning"><?= $company['manual_adjustment'] ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>📊 Stock actuel :</span>
                                <strong class="text-primary">
                                    <?= $company['total_received'] - $company['total_sold'] + $company['manual_adjustment'] ?>
                                    <?php if (($company['total_received'] - $company['total_sold'] + $company['manual_adjustment']) <= 5): ?>
                                        <span class="badge bg-danger ms-2">⚠️ Faible</span>
                                    <?php endif; ?>
                                </strong>
                            </li>
                        </ul>

                        <div class="d-grid mt-3">
                            <a href="?route=stocks/country/<?= $company['company_id'] ?>" class="btn btn-outline-primary btn-sm">
                                🔍 Voir les détails
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>
