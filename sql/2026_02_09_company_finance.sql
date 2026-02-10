-- Partenaires
CREATE TABLE IF NOT EXISTS `partners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Comptes bancaires partenaires
CREATE TABLE IF NOT EXISTS `partner_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `partner_id` int(11) NOT NULL,
  `bank_name` varchar(100) DEFAULT '',
  `account_label` varchar(100) NOT NULL,
  `account_number` varchar(100) DEFAULT '',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_partner_accounts_partner` (`partner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Factures sociétés (hebdo)
CREATE TABLE IF NOT EXISTS `company_invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `invoice_date` date NOT NULL,
  `amount_due` decimal(10,2) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `proof_file` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_company_invoices_company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `company_invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `quantity_sold` int(11) NOT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_company_invoice_items_invoice` (`invoice_id`),
  KEY `idx_company_invoice_items_variant` (`variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Encaissements (argent récupéré)
CREATE TABLE IF NOT EXISTS `company_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `partner_id` int(11) NOT NULL,
  `account_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `method` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `proof_file` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_company_payments_invoice` (`invoice_id`),
  KEY `idx_company_payments_partner` (`partner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Charges partenaires
CREATE TABLE IF NOT EXISTS `partner_expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `partner_id` int(11) NOT NULL,
  `account_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL,
  `category` varchar(100) DEFAULT '',
  `notes` text DEFAULT NULL,
  `proof_file` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_partner_expenses_partner` (`partner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Vue stock société
DROP VIEW IF EXISTS `company_stock_view`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW `company_stock_view` AS
SELECT
  o.company_id AS company_id,
  v.id AS variant_id,
  p.name AS product_name,
  v.size AS size,
  v.color AS color,
  COALESCE(SUM(si.quantity_sent), 0) AS total_received,
  COALESCE((
    SELECT SUM(cii.quantity_sold)
    FROM company_invoice_items cii
    JOIN company_invoices ci ON ci.id = cii.invoice_id
    WHERE ci.company_id = o.company_id AND cii.variant_id = v.id
  ), 0) AS total_sold,
  0 AS manual_adjustment,
  COALESCE(SUM(si.quantity_sent), 0) - COALESCE((
    SELECT SUM(cii.quantity_sold)
    FROM company_invoice_items cii
    JOIN company_invoices ci ON ci.id = cii.invoice_id
    WHERE ci.company_id = o.company_id AND cii.variant_id = v.id
  ), 0) AS current_stock
FROM shipments s
JOIN shipment_items si ON si.shipment_id = s.id
JOIN order_items oi ON si.order_item_id = oi.id
JOIN orders o ON s.order_id = o.id
JOIN variants v ON oi.variant_id = v.id
JOIN products p ON v.product_id = p.id
WHERE s.status IN ('Arrivé à destination', 'Livré à destination')
GROUP BY o.company_id, v.id;

-- Mise à jour de la vue stock pays (inclure ventes sociétés)
DROP VIEW IF EXISTS `real_stock_view`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW `real_stock_view` AS
SELECT
  o.country_id AS country_id,
  v.id AS variant_id,
  p.name AS product_name,
  v.size AS size,
  v.color AS color,
  COALESCE(SUM(si.quantity_sent), 0) AS total_received,
  (
    COALESCE((
      SELECT SUM(csi.quantity_sold)
      FROM client_sale_items csi
      JOIN client_sales cs ON cs.id = csi.sale_id
      WHERE cs.country_id = o.country_id AND csi.variant_id = v.id
    ), 0)
    +
    COALESCE((
      SELECT SUM(cii.quantity_sold)
      FROM company_invoice_items cii
      JOIN company_invoices ci ON ci.id = cii.invoice_id
      JOIN country_companies cc ON cc.id = ci.company_id
      WHERE cc.country_id = o.country_id AND cii.variant_id = v.id
    ), 0)
  ) AS total_sold,
  COALESCE((
    SELECT SUM(sa.adjusted_quantity)
    FROM stock_adjustments sa
    WHERE sa.country_id = o.country_id AND sa.variant_id = v.id
  ), 0) AS manual_adjustment,
  COALESCE(SUM(si.quantity_sent), 0)
  - (
    COALESCE((
      SELECT SUM(csi.quantity_sold)
      FROM client_sale_items csi
      JOIN client_sales cs ON cs.id = csi.sale_id
      WHERE cs.country_id = o.country_id AND csi.variant_id = v.id
    ), 0)
    +
    COALESCE((
      SELECT SUM(cii.quantity_sold)
      FROM company_invoice_items cii
      JOIN company_invoices ci ON ci.id = cii.invoice_id
      JOIN country_companies cc ON cc.id = ci.company_id
      WHERE cc.country_id = o.country_id AND cii.variant_id = v.id
    ), 0)
  )
  + COALESCE((
    SELECT SUM(sa.adjusted_quantity)
    FROM stock_adjustments sa
    WHERE sa.country_id = o.country_id AND sa.variant_id = v.id
  ), 0) AS current_stock
FROM shipments s
JOIN shipment_items si ON si.shipment_id = s.id
JOIN order_items oi ON si.order_item_id = oi.id
JOIN orders o ON s.order_id = o.id
JOIN variants v ON oi.variant_id = v.id
JOIN products p ON v.product_id = p.id
WHERE s.status IN ('Arrivé à destination', 'Livré à destination')
GROUP BY o.country_id, v.id;
