<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Chaussures Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap CSS + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
      min-height: 100vh;
      display: flex;
    }

    .sidebar {
      width: 240px;
      background-color: #343a40;
      color: white;
      flex-shrink: 0;
      padding-top: 60px;
      transition: transform 0.3s ease-in-out;
    }

    .sidebar a {
      color: white;
      display: block;
      padding: 12px 20px;
      text-decoration: none;
    }

    .sidebar a:hover, .sidebar a.active {
      background-color: #495057;
    }

    .content {
      flex-grow: 1;
      padding: 20px;
      margin-left: 10px;
      padding-top: 80px; /* ✅ Ajout important */
      transition: margin-left 0.3s ease-in-out;
    }


    .navbar-top {
      height: 60px;
      width: 100%;
      background-color: #212529;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 1030;
      color: white;
      display: flex;
      align-items: center;
      padding: 0 20px;
      justify-content: space-between;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Responsive */
    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
        position: fixed;
        height: 100%;
        top: 60px;
        left: 0;
        z-index: 1040;
      }

      .sidebar.show {
        transform: translateX(0);
      }

      .content {
        margin-left: 0;
      }
    }
  </style>
</head>
<body>

<!-- 🔝 Barre du haut -->
<div class="navbar-top">
  <button class="btn btn-outline-light d-md-none" id="toggleSidebar"><i class="bi bi-list"></i></button>
  <h5 class="m-0 d-flex align-items-center">
    <img src="public/logo.png" alt="Logo" style="height: 30px; margin-right: 10px;"> Chaussures Admin
  </h5>
</div>

<!-- 📦 Sidebar -->
<div class="sidebar" id="sidebar">
  <a href="?route=dashboard"><i class="bi bi-house"></i> Dashboard</a>
  <a href="?route=products"><i class="bi bi-box"></i> Produits</a>
  <a href="?route=suppliers"><i class="bi bi-truck"></i> Fournisseurs</a>
  <a href="?route=orders"><i class="bi bi-clipboard-check"></i> Commandes</a>
  <a href="?route=payments"><i class="bi bi-cash-coin"></i> Paiements</a>
  <a href="?route=shipments"><i class="bi bi-send"></i> Envois</a>
  <a href="?route=stocks"><i class="bi bi-warehouse"></i> Stocks</a>

  <hr class="bg-light">
  <div class="text-uppercase text-white-50 px-3 small">🧾 Ventes clients</div>
  <a href="?route=client_sales"><i class="bi bi-receipt"></i> Factures clients</a>
  <a href="?route=select_country"><i class="bi bi-plus-circle"></i> Nouvelle facture</a>

  <hr class="bg-light">
  <a href="?route=logout" class="text-danger"><i class="bi bi-box-arrow-right"></i> Déconnexion</a>
</div>


<!-- 🧩 Contenu principal -->
<div class="content">
