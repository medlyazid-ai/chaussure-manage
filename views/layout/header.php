<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Chaussures Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body { padding-top: 70px; }
    .navbar-brand img {
      height: 30px;
      margin-right: 10px;
    }
  </style>
</head>
<body>

<!-- ✅ Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="?route=dashboard">
      <img src="public/logo.png" alt="Logo">
      <span>Chaussures Admin</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="?route=products">Produits</a></li>
        <li class="nav-item"><a class="nav-link" href="?route=suppliers">Fournisseurs</a></li>
        <li class="nav-item"><a class="nav-link" href="?route=orders">Commandes</a></li>
        <li class="nav-item"><a class="nav-link" href="?route=payments">Paiements</a></li>
        <li class="nav-item"><a class="nav-link" href="?route=shipments">Shipments</a></li>
        <li class="nav-item"><a class="nav-link" href="?route=dashboard">Dashboard</a></li>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link text-danger" href="?route=logout">Déconnexion</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
