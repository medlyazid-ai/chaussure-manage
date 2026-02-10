-- Ajouter partenaire sur paiements fournisseurs
ALTER TABLE `payments`
  ADD COLUMN `partner_id` int(11) DEFAULT NULL AFTER `supplier_id`,
  ADD KEY `idx_payments_partner` (`partner_id`);
