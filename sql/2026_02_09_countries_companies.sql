-- Ajout des sociétés par pays + liaison aux commandes/ventes

CREATE TABLE IF NOT EXISTS `country_companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `contact` varchar(150) DEFAULT '',
  `address` varchar(255) DEFAULT '',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_company_country` (`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `orders`
  ADD COLUMN IF NOT EXISTS `company_id` int(11) DEFAULT NULL AFTER `country_id`,
  ADD KEY `idx_orders_company` (`company_id`);

ALTER TABLE `client_sales`
  ADD COLUMN IF NOT EXISTS `company_id` int(11) DEFAULT NULL AFTER `country_id`,
  ADD KEY `idx_sales_company` (`company_id`);

-- Foreign keys (optionnel)
-- ALTER TABLE `country_companies` ADD CONSTRAINT `fk_company_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`);
-- ALTER TABLE `orders` ADD CONSTRAINT `fk_orders_company` FOREIGN KEY (`company_id`) REFERENCES `country_companies` (`id`);
-- ALTER TABLE `client_sales` ADD CONSTRAINT `fk_sales_company` FOREIGN KEY (`company_id`) REFERENCES `country_companies` (`id`);
