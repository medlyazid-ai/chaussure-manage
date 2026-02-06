<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>📦 Détail Stock - <?= htmlspecialchars($transport['transport_name'] ?? 'Transport') ?></title>
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:hover { background-color: #f5f5f5; }
        .stock-positive { color: green; font-weight: bold; }
        .stock-negative { color: red; font-weight: bold; }
        .stock-zero { color: orange; }
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
        .btn-adjust { background: #FF9800; }
        .btn-adjust:hover { background: #e68900; }
        .adjustment-form {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
            display: none;
        }
        .adjustment-form input, .adjustment-form textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .adjustment-form button {
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .adjustment-form button:hover { background: #45a049; }
        .adjustments-list {
            font-size: 0.85em;
            color: #666;
            margin-top: 5px;
        }
        .alert { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
    </style>
    <script>
        function toggleAdjustmentForm(variantId) {
            const form = document.getElementById('adjust-form-' + variantId);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</head>
<body>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">❌ <?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<h1>📦 Stock Détaillé - <?= htmlspecialchars($transport['transport_name']) ?></h1>
<p><strong>Type:</strong> <?= htmlspecialchars($transport['transport_type']) ?></p>

<a href="?route=stocks" class="btn">← Retour à la vue globale</a>

<?php if (empty($stocks)): ?>
    <p><em>Aucun stock trouvé pour cette société de livraison.</em></p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th>Taille</th>
                <th>Couleur</th>
                <th>📥 Reçu</th>
                <th>📤 Vendu</th>
                <th>⚙️ Ajustement</th>
                <th>📊 Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stocks as $s): ?>
                <?php 
                    $currentStock = $s['current_stock'] ?? 0;
                    $stockClass = $currentStock > 0 ? 'stock-positive' : ($currentStock == 0 ? 'stock-zero' : 'stock-negative');
                ?>
                <tr>
                    <td><?= htmlspecialchars($s['product_name']) ?></td>
                    <td><?= htmlspecialchars($s['size']) ?></td>
                    <td><?= htmlspecialchars($s['color']) ?></td>
                    <td><?= number_format($s['total_received'], 0) ?></td>
                    <td><?= number_format($s['total_sold'], 0) ?></td>
                    <td><?= number_format($s['manual_adjustment'], 0) ?></td>
                    <td class="<?= $stockClass ?>">
                        <?= number_format($currentStock, 0) ?>
                    </td>
                    <td>
                        <button onclick="toggleAdjustmentForm(<?= $s['variant_id'] ?>)" class="btn btn-adjust">
                            ⚙️ Ajuster
                        </button>
                        
                        <!-- Formulaire d'ajustement -->
                        <div id="adjust-form-<?= $s['variant_id'] ?>" class="adjustment-form">
                            <form method="POST" action="?route=stocks/adjust">
                                <input type="hidden" name="transport_id" value="<?= $transport['transport_id'] ?>">
                                <input type="hidden" name="variant_id" value="<?= $s['variant_id'] ?>">
                                
                                <label>Quantité à ajuster (+ ou -):</label>
                                <input type="number" name="adjusted_quantity" required placeholder="Ex: +10 ou -5">
                                
                                <label>Raison:</label>
                                <textarea name="reason" required placeholder="Ex: Casse, erreur d'inventaire..."></textarea>
                                
                                <button type="submit">✅ Enregistrer l'ajustement</button>
                            </form>
                            
                            <?php if (!empty($s['adjustments'])): ?>
                                <div class="adjustments-list">
                                    <strong>Historique des ajustements:</strong>
                                    <ul>
                                        <?php foreach ($s['adjustments'] as $adj): ?>
                                            <li>
                                                <?= htmlspecialchars($adj['adjusted_quantity']) ?> 
                                                - <?= htmlspecialchars($adj['reason']) ?>
                                                (<?= date('d/m/Y', strtotime($adj['created_at'])) ?>)
                                                <form method="POST" action="?route=stocks/delete-adjustment" style="display:inline;">
                                                    <input type="hidden" name="adjustment_id" value="<?= $adj['id'] ?>">
                                                    <button type="submit" onclick="return confirm('Supprimer cet ajustement ?')" 
                                                            style="background:red;color:white;border:none;padding:2px 8px;cursor:pointer;">
                                                        🗑️
                                                    </button>
                                                </form>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>
