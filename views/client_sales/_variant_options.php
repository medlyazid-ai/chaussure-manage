<option value="">-- Choisir une variante --</option>
<?php foreach ($variants as $variant): ?>
    <option value="<?= $variant['variant_id'] ?>">
        <?= htmlspecialchars($variant['product_name'] . ' - ' . $variant['size'] . ' / ' . $variant['color']) ?>
        (Stock : <?= $variant['current_stock'] ?>)
    </option>
<?php endforeach; ?>
