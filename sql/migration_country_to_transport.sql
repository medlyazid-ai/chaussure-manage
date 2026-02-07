-- ============================================================================
-- Migration: Country-based to Transport-based Stock Management
-- Date: 2026-02-06
-- Description: Refactor stock tracking from countries to transport companies
-- ============================================================================

-- Step 1: Add transport_id to orders table (keep country_id temporarily for migration)
ALTER TABLE `orders` 
ADD COLUMN `transport_id` INT(11) DEFAULT NULL AFTER `country_id`,
ADD KEY `fk_orders_transport` (`transport_id`);

-- Step 2: Create transport_stocks table (replaces country_stocks concept)
CREATE TABLE IF NOT EXISTS `transport_stocks` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `transport_id` INT(11) NOT NULL,
  `variant_id` INT(11) NOT NULL,
  `quantity` INT(11) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_transport_variant` (`transport_id`, `variant_id`),
  KEY `fk_transport` (`transport_id`),
  KEY `fk_variant` (`variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Step 3: Update stock_adjustments to support transport_id (keep country_id for backward compatibility)
ALTER TABLE `stock_adjustments`
ADD COLUMN `transport_id` INT(11) DEFAULT NULL AFTER `country_id`,
ADD KEY `fk_stock_adj_transport` (`transport_id`);

-- Step 4: Update client_sales to support transport_id (keep country_id for now)
ALTER TABLE `client_sales`
ADD COLUMN `transport_id` INT(11) DEFAULT NULL AFTER `country_id`,
ADD KEY `fk_client_sale_transport` (`transport_id`);

-- Step 5: Data Migration - Map existing country-based data to transports
-- Note: This requires manual mapping or a default transport per country
-- For now, we'll create a temporary mapping based on existing data

-- Option A: Migrate orders to use first available transport for each country
UPDATE `orders` o
LEFT JOIN (
    SELECT DISTINCT country_id, MIN(transport_id) as transport_id
    FROM shipments s
    JOIN orders o2 ON s.order_id = o2.id
    WHERE s.transport_id IS NOT NULL
    GROUP BY country_id
) mapping ON o.country_id = mapping.country_id
SET o.transport_id = mapping.transport_id
WHERE o.transport_id IS NULL;

-- Option B: For orders without a clear transport mapping, use a default transport
-- (Assumes transport ID 1 exists - adjust as needed)
UPDATE `orders` 
SET transport_id = 1 
WHERE transport_id IS NULL;

-- Step 6: Migrate country_stocks to transport_stocks
INSERT INTO `transport_stocks` (`transport_id`, `variant_id`, `quantity`, `created_at`)
SELECT 
    COALESCE(t.transport_id, 1) as transport_id,
    cs.variant_id,
    cs.quantity,
    cs.created_at
FROM `country_stocks` cs
LEFT JOIN (
    SELECT DISTINCT o.country_id, MIN(s.transport_id) as transport_id
    FROM orders o
    JOIN shipments s ON s.order_id = o.id
    WHERE s.transport_id IS NOT NULL
    GROUP BY o.country_id
) t ON cs.country_id = t.country_id
ON DUPLICATE KEY UPDATE 
    quantity = quantity + VALUES(quantity);

-- Step 7: Migrate stock_adjustments to use transport_id
UPDATE `stock_adjustments` sa
LEFT JOIN (
    SELECT DISTINCT country_id, MIN(transport_id) as transport_id
    FROM orders
    WHERE transport_id IS NOT NULL
    GROUP BY country_id
) mapping ON sa.country_id = mapping.country_id
SET sa.transport_id = COALESCE(mapping.transport_id, 1)
WHERE sa.transport_id IS NULL;

-- Step 8: Migrate client_sales to use transport_id
UPDATE `client_sales` cs
LEFT JOIN (
    SELECT DISTINCT country_id, MIN(transport_id) as transport_id
    FROM orders
    WHERE transport_id IS NOT NULL
    GROUP BY country_id
) mapping ON cs.country_id = mapping.country_id
SET cs.transport_id = COALESCE(mapping.transport_id, 1)
WHERE cs.transport_id IS NULL;

-- Step 9: Create new transport_stock_view (replaces real_stock_view)
DROP VIEW IF EXISTS `transport_stock_view`;

CREATE ALGORITHM=UNDEFINED 
SQL SECURITY INVOKER 
VIEW `transport_stock_view` AS 
SELECT 
    o.transport_id AS transport_id,
    v.id AS variant_id,
    p.name AS product_name,
    v.size AS size,
    v.color AS color,
    COALESCE(SUM(si.quantity_sent), 0) AS total_received,
    COALESCE((
        SELECT SUM(csi.quantity_sold) 
        FROM client_sale_items csi 
        JOIN client_sales cs ON cs.id = csi.sale_id 
        WHERE cs.transport_id = o.transport_id 
        AND csi.variant_id = v.id
    ), 0) AS total_sold,
    COALESCE((
        SELECT SUM(sa.adjusted_quantity) 
        FROM stock_adjustments sa 
        WHERE sa.transport_id = o.transport_id 
        AND sa.variant_id = v.id
    ), 0) AS manual_adjustment,
    COALESCE(SUM(si.quantity_sent), 0) 
    - COALESCE((
        SELECT SUM(csi.quantity_sold) 
        FROM client_sale_items csi 
        JOIN client_sales cs ON cs.id = csi.sale_id 
        WHERE cs.transport_id = o.transport_id 
        AND csi.variant_id = v.id
    ), 0) 
    + COALESCE((
        SELECT SUM(sa.adjusted_quantity) 
        FROM stock_adjustments sa 
        WHERE sa.transport_id = o.transport_id 
        AND sa.variant_id = v.id
    ), 0) AS current_stock
FROM shipments s
JOIN shipment_items si ON si.shipment_id = s.id
JOIN order_items oi ON si.order_item_id = oi.id
JOIN orders o ON s.order_id = o.id
JOIN variants v ON oi.variant_id = v.id
JOIN products p ON v.product_id = p.id
WHERE s.status = 'Arrivé à destination'
AND o.transport_id IS NOT NULL
GROUP BY o.transport_id, v.id;

-- Step 10: Add foreign key constraints
ALTER TABLE `orders`
ADD CONSTRAINT `fk_orders_transport` 
FOREIGN KEY (`transport_id`) REFERENCES `transports` (`id`);

ALTER TABLE `transport_stocks`
ADD CONSTRAINT `fk_transport_stocks_transport` 
FOREIGN KEY (`transport_id`) REFERENCES `transports` (`id`),
ADD CONSTRAINT `fk_transport_stocks_variant` 
FOREIGN KEY (`variant_id`) REFERENCES `variants` (`id`) ON DELETE CASCADE;

ALTER TABLE `stock_adjustments`
ADD CONSTRAINT `fk_stock_adj_transport` 
FOREIGN KEY (`transport_id`) REFERENCES `transports` (`id`);

ALTER TABLE `client_sales`
ADD CONSTRAINT `fk_client_sales_transport` 
FOREIGN KEY (`transport_id`) REFERENCES `transports` (`id`);

-- ============================================================================
-- OPTIONAL: After verifying the migration works, you can drop old columns
-- ============================================================================

-- WARNING: Only run these after confirming everything works!
-- These are commented out for safety

-- ALTER TABLE `orders` DROP FOREIGN KEY `fk_orders_country`;
-- ALTER TABLE `orders` DROP COLUMN `country_id`;

-- ALTER TABLE `stock_adjustments` DROP FOREIGN KEY IF EXISTS `stock_adjustments_ibfk_1`;
-- ALTER TABLE `stock_adjustments` DROP COLUMN `country_id`;

-- ALTER TABLE `client_sales` DROP FOREIGN KEY IF EXISTS `country_id`;
-- ALTER TABLE `client_sales` DROP COLUMN `country_id`;

-- DROP TABLE IF EXISTS `country_stocks`;
-- DROP VIEW IF EXISTS `real_stock_view`;

-- ============================================================================
-- ROLLBACK INSTRUCTIONS (in case of issues)
-- ============================================================================
-- To rollback this migration:
-- 1. Restore database from backup
-- OR
-- 2. Drop new columns and tables:
--    ALTER TABLE orders DROP COLUMN transport_id;
--    DROP TABLE transport_stocks;
--    ALTER TABLE stock_adjustments DROP COLUMN transport_id;
--    ALTER TABLE client_sales DROP COLUMN transport_id;
--    DROP VIEW transport_stock_view;
