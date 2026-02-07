<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>📦 Stock par Société de Livraison</title>
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:hover { background-color: #f5f5f5; }
        .stock-positive { color: green; font-weight: bold; }
        .stock-negative { color: red; font-weight: bold; }
        .transport-type { font-size: 0.9em; color: #666; }
        a.btn { 
            display: inline-block; 
            padding: 8px 15px; 
            background: #2196F3; 
            color: white; 
            text-decoration: none; 
            border-radius: 4px; 
            margin-bottom: 15px;
        }
        a.btn:hover { background: #0b7dda; }
        .alert { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        ✅ <?= htmlspecialchars($_SESSION['success']) ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        ❌ <?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<h1>📦 Stock Global par Société de Livraison</h1>

<a href="?route=dashboard" class="btn">← Retour au tableau de bord</a>

<?php if (empty($transportStocks)): ?>
    <p><em>Aucun stock trouvé. Veuillez d'abord créer des commandes et des envois.</em></p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>🚚 Société de Livraison</th>
                <th>📦 Type</th>
                <th>📥 Total Reçu</th>
                <th>📤 Total Vendu</th>
                <th>⚙️ Ajustements</th>
                <th>📊 Stock Actuel</th>
                <th>🔍 Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transportStocks as $t): ?>
                <?php 
                    $currentStock = $t['total_received'] - $t['total_sold'] + $t['manual_adjustment'];
                    $stockClass = $currentStock >= 0 ? 'stock-positive' : 'stock-negative';
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($t['transport_name']) ?></strong></td>
                    <td class="transport-type"><?= htmlspecialchars($t['transport_type']) ?></td>
                    <td><?= number_format($t['total_received'], 0) ?></td>
                    <td><?= number_format($t['total_sold'], 0) ?></td>
                    <td><?= number_format($t['manual_adjustment'], 0) ?></td>
                    <td class="<?= $stockClass ?>">
                        <?= number_format($currentStock, 0) ?>
                    </td>
                    <td>
                        <a href="?route=stocks/transport/<?= $t['transport_id'] ?>">📋 Voir détail</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>
