<?php
// 🔁 Exemple : http://localhost:8888/chaussures_app_structure/
$basePath = '/';

// URL de base pour les routes (routing interne)
define('BASE_URL', $basePath . 'index.php?route=');

// URL de base pour les fichiers statiques (images, CSS, JS)
define('ASSETS_URL', $basePath . 'public/');
