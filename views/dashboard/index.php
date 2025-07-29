<?php
if (!isset($_SESSION['user'])) {
    header('Location: /login');
    exit;
}
?>
<h1>Bienvenue, <?= htmlspecialchars($_SESSION['user']['name']) ?> !</h1>
<a href="/logout">Se déconnecter</a>
