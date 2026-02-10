-- Ajout des champs d'encaissement partenaire sur les ventes clients

ALTER TABLE client_sales
  ADD COLUMN partner_id INT(11) NULL AFTER company_id,
  ADD COLUMN account_id INT(11) NULL AFTER partner_id,
  ADD COLUMN amount_received DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER account_id,
  ADD COLUMN currency VARCHAR(10) NOT NULL DEFAULT 'USD' AFTER amount_received,
  ADD COLUMN received_date DATE NULL AFTER currency,
  ADD COLUMN payment_method VARCHAR(50) NULL AFTER received_date,
  ADD KEY idx_client_sales_partner (partner_id),
  ADD KEY idx_client_sales_account (account_id);

ALTER TABLE client_sales
  ADD CONSTRAINT fk_client_sales_partner FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_client_sales_account FOREIGN KEY (account_id) REFERENCES partner_accounts(id) ON DELETE SET NULL;
