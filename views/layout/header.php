<?php
require_once __DIR__ . '/../../utils.php';
start_session_if_needed();
?>
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
      overflow-x: hidden;
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
      padding-top: 60px;
      transition: transform 0.3s ease-in-out;
      overflow-y: auto;
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      z-index: 1020;
    }

    .sidebar a {
      color: white;
      text-decoration: none;
      display: block;
      padding: 12px 20px;
    }

    .sidebar .section-header {
      width: 100%;
      text-align: left;
      background: none;
      border: none;
      color: #cfd4da;
      padding: 10px 20px;
      text-transform: uppercase;
      font-size: 12px;
      letter-spacing: 0.04em;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .sidebar .section-header:hover {
      color: #ffffff;
    }

    .sidebar .section-links a {
      padding-left: 34px;
      font-size: 14px;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background-color: #495057;
    }

    .content {
      padding: 20px;
      margin-left: 240px;
      padding-top: 80px;
      transition: margin-left 0.3s ease-in-out;
    }

    /* 📱 Responsive sidebar */
    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
        position: fixed;
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

    <button class="section-header" data-bs-toggle="collapse" data-bs-target="#menu-catalog" aria-expanded="true">
        📦 Catalogue <span>▾</span>
    </button>
    <div id="menu-catalog" class="collapse show section-links" data-bs-parent="#sidebar">
        <a href="?route=products">👟 Produits</a>
        <a href="?route=variants">🔖 Variantes</a>
    </div>

    <button class="section-header" data-bs-toggle="collapse" data-bs-target="#menu-operations" aria-expanded="true">
        📥 Opérations <span>▾</span>
    </button>
    <div id="menu-operations" class="collapse show section-links" data-bs-parent="#sidebar">
        <a href="?route=suppliers">👥 Fournisseurs</a>
        <a href="?route=orders">🧾 Commandes</a>
        <a href="?route=payments">💰 Paiements</a>
        <a href="?route=shipments">📦 Envois</a>
        <a href="?route=transports">🚛 Transporteurs</a>
    </div>

    <button class="section-header" data-bs-toggle="collapse" data-bs-target="#menu-reference" aria-expanded="false">
        🗂️ Référentiel <span>▾</span>
    </button>
    <div id="menu-reference" class="collapse section-links" data-bs-parent="#sidebar">
        <a href="?route=countries">🌍 Pays</a>
        <a href="?route=companies">🏢 Sociétés</a>
        <a href="?route=company_invoices">🧾 Factures sociétés</a>
        <a href="?route=stocks">🏬 Stocks</a>
    </div>

    <button class="section-header" data-bs-toggle="collapse" data-bs-target="#menu-partners" aria-expanded="false">
        🤝 Partenaires <span>▾</span>
    </button>
    <div id="menu-partners" class="collapse section-links" data-bs-parent="#sidebar">
        <a href="?route=partners">🤝 Partenaires</a>
        <a href="?route=accounts">🏦 Comptes</a>
        <a href="?route=partner_expenses">💸 Charges</a>
    </div>

    <button class="section-header" data-bs-toggle="collapse" data-bs-target="#menu-client-sales" aria-expanded="true">
        🧾 Ventes clients <span>▾</span>
    </button>
    <div id="menu-client-sales" class="collapse show section-links" data-bs-parent="#sidebar">
        <a href="?route=client_sales">🧾 Factures clients</a>
        <a href="?route=select_country">➕ Nouvelle facture</a>
    </div>

    <button class="section-header" data-bs-toggle="collapse" data-bs-target="#menu-reports" aria-expanded="false">
        📊 Rapports <span>▾</span>
    </button>
    <div id="menu-reports" class="collapse section-links" data-bs-parent="#sidebar">
        <a href="?route=reports/partners">📊 Partenaires</a>
        <a href="?route=reports/company_stock">📦 Stock société</a>
        <a href="?route=reports/company_dashboard">🏢 Dash sociétés</a>
    </div>

    <hr class="bg-light">
    <a href="?route=logout" class="text-danger">🚪 Déconnexion</a>
</div>


  <!-- 🧩 Contenu principal -->
  <div class="content">
  <script>
    (function () {
      const current = window.location.search.replace('?','');
      const links = document.querySelectorAll('#sidebar a[href^="?route="]');
      let activeLink = null;
      links.forEach(a => {
        const href = a.getAttribute('href').replace('?','');
        if (current.startsWith(href)) {
          activeLink = a;
        }
      });
      if (activeLink) {
        activeLink.classList.add('active');
        const parentCollapse = activeLink.closest('.collapse');
        if (parentCollapse && typeof bootstrap !== 'undefined') {
          new bootstrap.Collapse(parentCollapse, { toggle: true });
        } else if (parentCollapse) {
          parentCollapse.classList.add('show');
        }
      }
    })();
  </script>
