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
                <tr>
                    <td>#<?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['destination_country']) ?></td>
                    <td><?= number_format($order['total_amount'], 2) ?> DH</td>
                    <td><?= number_format($order['already_paid'], 2) ?> DH</td>
                    <td>
                        <input type="number" step="0.01"
                            name="allocations[<?= $order['id'] ?>]"
                            max="<?= $order['total_amount'] - $order['already_paid'] ?>"
                            class="form-control"
                            placeholder="Montant à affecter">

                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="alert alert-warning">Aucune commande impayée trouvée pour ce fournisseur.</div>
<?php endif; ?>
