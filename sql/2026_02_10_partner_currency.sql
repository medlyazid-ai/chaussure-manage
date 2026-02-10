-- Ajout des devises + sécurité colonnes

-- payments.partner_id (si manquant)
SET @col := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'partner_id');
SET @sql := IF(@col = 0, 'ALTER TABLE payments ADD COLUMN partner_id INT(11) DEFAULT NULL AFTER supplier_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- payments.currency
SET @col := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'currency');
SET @sql := IF(@col = 0, 'ALTER TABLE payments ADD COLUMN currency VARCHAR(10) NOT NULL DEFAULT \"MAD\" AFTER amount', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- company_payments.currency
SET @col := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'company_payments' AND COLUMN_NAME = 'currency');
SET @sql := IF(@col = 0, 'ALTER TABLE company_payments ADD COLUMN currency VARCHAR(10) NOT NULL DEFAULT \"MAD\" AFTER amount', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- partner_expenses.currency
SET @col := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'partner_expenses' AND COLUMN_NAME = 'currency');
SET @sql := IF(@col = 0, 'ALTER TABLE partner_expenses ADD COLUMN currency VARCHAR(10) NOT NULL DEFAULT \"MAD\" AFTER amount', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
