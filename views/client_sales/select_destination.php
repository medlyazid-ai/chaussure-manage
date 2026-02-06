<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>🎯 Sélection Destination</title>
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { 
            color: #333; 
            text-align: center;
            margin-bottom: 30px;
        }
        .selection-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 30px;
        }
        .selection-box {
            border: 2px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            background: #fafafa;
        }
        .selection-box h2 {
            color: #2196F3;
            margin-top: 0;
        }
        .selection-box select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 10px 0;
        }
        .selection-box button {
            width: 100%;
            padding: 12px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .selection-box button:hover {
            background: #45a049;
        }
        a.btn-back {
            display: inline-block;
            padding: 10px 20px;
            background: #666;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        a.btn-back:hover { background: #555; }
        .note {
            background: #e7f3fe;
            padding: 15px;
            border-left: 4px solid #2196F3;
            margin: 20px 0;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>🎯 Enregistrer une Vente Client</h1>
    
    <a href="?route=client_sales" class="btn-back">← Retour aux ventes</a>

    <div class="note">
        <strong>💡 Nouveau système dynamique !</strong><br>
        Vous pouvez maintenant enregistrer les ventes par <strong>société de livraison</strong> (recommandé) 
        ou continuer avec le système par pays (legacy).
    </div>

    <div class="selection-grid">
        <!-- Option 1: Par Société de Livraison (Nouveau) -->
        <div class="selection-box">
            <h2>🚚 Par Société de Livraison</h2>
            <p><em>Système recommandé - Le stock est géré par la société de livraison qui récupère les produits</em></p>
            
            <form method="GET" action="">
                <input type="hidden" name="route" value="client_sales/create">
                <label for="transport_id"><strong>Sélectionner une société:</strong></label>
                <select name="transport_id" id="transport_id" required>
                    <option value="">-- Choisir --</option>
                    <?php foreach ($transports as $t): ?>
                        <option value="<?= $t['id'] ?>">
                            <?= htmlspecialchars($t['name']) ?> - <?= htmlspecialchars($t['transport_type']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Continuer avec cette société →</button>
            </form>
        </div>

        <!-- Option 2: Par Pays (Legacy) -->
        <div class="selection-box">
            <h2>🌍 Par Pays (Legacy)</h2>
            <p><em>Ancien système - Le stock est géré par destination géographique</em></p>
            
            <form method="GET" action="">
                <input type="hidden" name="route" value="client_sales/create">
                <label for="country_id"><strong>Sélectionner un pays:</strong></label>
                <select name="country_id" id="country_id" required>
                    <option value="">-- Choisir --</option>
                    <?php foreach ($countries as $c): ?>
                        <option value="<?= $c['id'] ?>">
                            <?= htmlspecialchars($c['flag']) ?> <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Continuer avec ce pays →</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
