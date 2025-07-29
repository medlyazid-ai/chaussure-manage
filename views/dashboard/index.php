<?php
require_once 'auth_check.php';
include 'views/layout/header.php';
?>

<div class="container mt-4">
    <div class="alert alert-success rounded-4 shadow-sm">
        👋 Bonjour <strong><?= htmlspecialchars($_SESSION['user']['name']) ?></strong>, bienvenue sur votre espace personnel.
    </div>

    <div class="row g-4 mt-4">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow rounded-4 border-0">
                <div class="card-body text-center">
                    <h5 class="card-title mb-3">📦 Commandes</h5>
                    <p class="card-text">Gérez vos commandes, suivez les envois partiels et consultez leur statut.</p>
                    <a href="?route=orders" class="btn btn-outline-primary">Voir les commandes</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4">
            <div class="card shadow rounded-4 border-0">
                <div class="card-body text-center">
                    <h5 class="card-title mb-3">👞 Produits</h5>
                    <p class="card-text">Ajoutez, modifiez ou supprimez des produits et leurs variantes.</p>
                    <a href="?route=products" class="btn btn-outline-success">Gérer les produits</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4">
            <div class="card shadow rounded-4 border-0">
                <div class="card-body text-center">
                    <h5 class="card-title mb-3">💰 Paiements</h5>
                    <p class="card-text">Consultez les paiements fournisseurs et affectez-les aux commandes.</p>
                    <a href="?route=payments" class="btn btn-outline-warning">Voir les paiements</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card shadow rounded-4 border-0">
                <div class="card-body text-center">
                    <h5 class="card-title mb-3">➕ Ajouter un compte</h5>
                    <p class="card-text">Créez un nouveau compte utilisateur pour un collaborateur ou un fournisseur.</p>
                    <a href="?route=register" class="btn btn-outline-info">Créer un compte</a>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include 'views/layout/footer.php'; ?>
