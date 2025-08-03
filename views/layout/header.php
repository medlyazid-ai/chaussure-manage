<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Chaussures Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- ✅ Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <!-- ✅ Styles personnalisés -->
  <style>
    body {
      min-height: 100vh;
      display: flex;
    }

    .navbar-top {
      height: 60px;
      width: 100%;
      background-color: #212529;
      color: white;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 1030;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
      text-decoration: none;
      display: block;
      padding: 12px 20px;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background-color: #495057;
    }

    .content {
      flex-grow: 1;
      padding: 20px;
      margin-left: 10px;
      padding-top: 80px;
      transition: margin-left 0.3s ease-in-out;
    }

    /* 📱 Responsive sidebar */
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

  <!-- 🔝 Barre supérieure -->
  <div class="navbar-top">
    <button class="btn btn-outline-light d-md-none" id="toggleSidebar"><i class="bi bi-list"></i></button>
    <h5 class="m-0 d-flex align-items-center">
      <img src="public/logo.png" alt="Logo" style="height: 30px; margin-right: 10px;">
      Chaussures Admin
    </h5>
  </div>


<!-- 📦 Barre latérale -->
<div class="sidebar" id="sidebar">
    <a href="?route=dashboard">🏠 Dashboard</a>
    <a href="?route=products">👟 Produits</a>
    <a href="?route=suppliers">👥 Fournisseurs</a>
    <a href="?route=transports">🚛 Transporteurs</a>
    <a href="?route=orders">🧾 Commandes</a>
    <a href="?route=payments">💰 Paiements</a>
    <a href="?route=shipments">📦 Envois</a>
    <a href="?route=stocks">🏬 Stocks</a>

    <hr class="bg-light">
    <div class="text-uppercase text-white-50 px-3 small">🧾 Ventes clients</div>
    <a href="?route=client_sales">🧾 Factures clients</a>
    <a href="?route=select_country">➕ Nouvelle facture</a>

    <hr class="bg-light">
    <a href="?route=logout" class="text-danger">🚪 Déconnexion</a>
</div>


  <!-- 🧩 Contenu principal -->
  <div class="content">
