<?php if (!empty($orders)): ?>
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Commande</th>
                <th>Pays</th>
                <th>Total</th>
                <th>Déjà payé</th>
                <th>À allouer</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <?php
                    $remaining = $order['total_amount'] - $order['already_paid'];
                    $isPrefill = !empty($prefillOrderId) && (string)$prefillOrderId === (string)$order['id'];
                ?>
                <tr>
                    <td><?= $isPrefill ? '<span class="badge bg-primary">Cible</span> ' : '' ?>#<?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['destination_country'] ?? 'Inconnu') ?></td>
                    <td><?= number_format($order['total_amount'], 2) ?> DH</td>
                    <td><?= number_format($order['already_paid'], 2) ?> DH</td>
                    <td>
                        <input type="number" step="0.01"
                            name="allocations[<?= $order['id'] ?>]"
                            max="<?= $remaining ?>"
                            class="form-control"
                            placeholder="Montant à affecter"
                            value="<?= $isPrefill ? number_format($remaining, 2, '.', '') : '' ?>">

                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="alert alert-warning">Aucune commande impayée trouvée pour ce fournisseur.</div>
<?php endif; ?>
