<?php if (empty($variants)): ?>
    <div class="alert alert-info mb-0">Aucune variante pour ce produit.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Taille</th>
                    <th>Couleur</th>
                    <th>Quantité</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($variants as $v): ?>
                    <tr>
                        <td><?= e($v['size']) ?></td>
                        <td><?= e($v['color']) ?></td>
                        <td style="width: 160px;">
                            <input type="number" name="variants[<?= $v['id'] ?>][quantity_ordered]" class="form-control" min="0" value="0">
                            <input type="hidden" name="variants[<?= $v['id'] ?>][size]" value="<?= e($v['size']) ?>">
                            <input type="hidden" name="variants[<?= $v['id'] ?>][color]" value="<?= e($v['color']) ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
