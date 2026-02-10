<?php if (empty($variants)): ?>
    <div class="alert alert-warning">Aucun stock disponible pour cette société.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Produit</th>
                    <th>Taille</th>
                    <th>Couleur</th>
                    <th>Stock</th>
                    <th>Qté vendue</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($variants as $v): ?>
                    <tr>
                        <td><?= e($v['product_name']) ?></td>
                        <td><?= e($v['size']) ?></td>
                        <td><?= e($v['color']) ?></td>
                        <td><span class="badge bg-secondary"><?= (int)$v['current_stock'] ?></span></td>
                        <td style="width:160px;">
                            <input type="number" name="items[<?= $v['variant_id'] ?>][quantity_sold]" class="form-control" min="0" value="0">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
