-- Ajustements stock par société
CREATE TABLE IF NOT EXISTS `company_stock_adjustments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `adjusted_quantity` int(11) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_company_stock_adj_company` (`company_id`),
  KEY `idx_company_stock_adj_variant` (`variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Mettre à jour la vue company_stock_view pour intégrer les ajustements société
DROP VIEW IF EXISTS `company_stock_view`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW `company_stock_view` AS
SELECT
  base.company_id AS company_id,
  v.id AS variant_id,
  p.name AS product_name,
  v.size AS size,
  v.color AS color,
  COALESCE((
    SELECT SUM(si.quantity_sent)
    FROM shipments s
    JOIN shipment_items si ON si.shipment_id = s.id
    JOIN order_items oi ON si.order_item_id = oi.id
    JOIN orders o ON s.order_id = o.id
    WHERE s.status IN ('Arrivé à destination', 'Livré à destination')
      AND o.company_id = base.company_id
      AND oi.variant_id = base.variant_id
  ), 0) AS total_received,
  COALESCE((
    SELECT SUM(cii.quantity_sold)
    FROM company_invoice_items cii
    JOIN company_invoices ci ON ci.id = cii.invoice_id
    WHERE ci.company_id = base.company_id
      AND cii.variant_id = base.variant_id
  ), 0) AS total_sold,
  COALESCE((
    SELECT SUM(csa.adjusted_quantity)
    FROM company_stock_adjustments csa
    WHERE csa.company_id = base.company_id
      AND csa.variant_id = base.variant_id
  ), 0) AS manual_adjustment,
  COALESCE((
    SELECT SUM(si.quantity_sent)
    FROM shipments s
    JOIN shipment_items si ON si.shipment_id = s.id
    JOIN order_items oi ON si.order_item_id = oi.id
    JOIN orders o ON s.order_id = o.id
    WHERE s.status IN ('Arrivé à destination', 'Livré à destination')
      AND o.company_id = base.company_id
      AND oi.variant_id = base.variant_id
  ), 0)
  - COALESCE((
    SELECT SUM(cii.quantity_sold)
    FROM company_invoice_items cii
    JOIN company_invoices ci ON ci.id = cii.invoice_id
    WHERE ci.company_id = base.company_id
      AND cii.variant_id = base.variant_id
  ), 0)
  + COALESCE((
    SELECT SUM(csa.adjusted_quantity)
    FROM company_stock_adjustments csa
    WHERE csa.company_id = base.company_id
      AND csa.variant_id = base.variant_id
  ), 0) AS current_stock
FROM (
  SELECT o.company_id, oi.variant_id
  FROM shipments s
  JOIN shipment_items si ON si.shipment_id = s.id
  JOIN order_items oi ON si.order_item_id = oi.id
  JOIN orders o ON s.order_id = o.id
  WHERE s.status IN ('Arrivé à destination', 'Livré à destination')
    AND o.company_id IS NOT NULL
  GROUP BY o.company_id, oi.variant_id
  UNION
  SELECT ci.company_id, cii.variant_id
  FROM company_invoices ci
  JOIN company_invoice_items cii ON cii.invoice_id = ci.id
  WHERE ci.company_id IS NOT NULL
  GROUP BY ci.company_id, cii.variant_id
  UNION
  SELECT csa.company_id, csa.variant_id
  FROM company_stock_adjustments csa
  WHERE csa.company_id IS NOT NULL
  GROUP BY csa.company_id, csa.variant_id
) base
JOIN variants v ON v.id = base.variant_id
JOIN products p ON p.id = v.product_id;
