-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Feb 03, 2026 at 10:03 PM
-- Server version: 10.6.21-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quwaejeq_chaussure_manage_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `client_sales`
--

CREATE TABLE `client_sales` (
  `id` int(11) NOT NULL,
  `sale_date` date NOT NULL,
  `country_id` int(11) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `proof_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_sales`
--

INSERT INTO `client_sales` (`id`, `sale_date`, `country_id`, `customer_name`, `notes`, `created_at`, `proof_file`) VALUES
(8, '2025-08-11', 1, '', '', '2025-08-14 21:54:31', 'uploads/sales_proofs/1755208471_FCT-25062025-05875 (2).pdf'),
(9, '2025-08-12', 1, 'Colismart', '', '2025-08-14 21:57:40', 'uploads/sales_proofs/1755208660_FCT-30062025-10108.pdf'),
(10, '2025-08-13', 1, 'Colismart', '', '2025-08-14 22:00:36', 'uploads/sales_proofs/1755208836_FCT-07072025-24758.pdf'),
(11, '2025-08-13', 1, '', '', '2025-08-14 22:02:21', 'uploads/sales_proofs/1755208941_FCT-14072025-45743.pdf'),
(12, '2025-08-14', 1, '', '', '2025-08-14 22:03:41', 'uploads/sales_proofs/1755209021_FCT-07082025-38025.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `client_sale_items`
--

CREATE TABLE `client_sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `quantity_sold` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_sale_items`
--

INSERT INTO `client_sale_items` (`id`, `sale_id`, `variant_id`, `quantity_sold`) VALUES
(37, 8, 86, 2),
(38, 8, 87, 4),
(39, 8, 88, 5),
(40, 8, 89, 4),
(41, 8, 90, 2),
(42, 8, 91, 5),
(43, 9, 86, 4),
(44, 9, 87, 1),
(45, 9, 88, 6),
(46, 9, 89, 2),
(47, 9, 90, 4),
(48, 9, 91, 3),
(49, 10, 87, 2),
(50, 10, 88, 5),
(51, 10, 89, 6),
(52, 10, 90, 4),
(53, 10, 91, 1),
(54, 11, 87, 3),
(55, 11, 88, 2),
(56, 11, 89, 3),
(57, 11, 90, 1),
(58, 11, 91, 4),
(59, 12, 86, 1),
(60, 12, 89, 2),
(61, 12, 90, 3),
(62, 12, 91, 1);

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `flag` varchar(10) DEFAULT '',
  `code` varchar(10) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `name`, `flag`, `code`) VALUES
(1, 'Guinée', '🇬🇳', 'GN'),
(2, 'Côte d\'Ivoire', '🇨🇮', 'CI'),
(3, 'Mali', '🇲🇱', 'ML');

-- --------------------------------------------------------

--
-- Table structure for table `country_stocks`
--

CREATE TABLE `country_stocks` (
  `id` int(11) NOT NULL,
  `country_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  `order_date` date DEFAULT NULL,
  `total_quantity` int(11) NOT NULL,
  `status` enum('Initial','Validé – production en cours','Envoi partiel','Envoi complet','Livré à la destination') DEFAULT 'Initial',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `product_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `supplier_id`, `country_id`, `order_date`, `total_quantity`, `status`, `created_at`, `updated_at`, `product_id`) VALUES
(31, 12, 2, NULL, 110, 'Livré à la destination', '2025-08-03 20:45:38', '2025-08-11 19:44:02', 22),
(32, 12, 2, NULL, 94, 'Livré à la destination', '2025-08-03 20:47:59', '2025-08-11 19:46:35', 18),
(33, 12, 2, NULL, 134, 'Initial', '2025-08-03 23:20:31', '2025-08-03 23:20:31', 17),
(34, 12, 1, NULL, 185, 'Envoi partiel', '2025-08-03 23:50:55', '2025-08-14 21:48:46', 22),
(35, 12, 2, NULL, 100, 'Livré à la destination', '2025-08-03 23:53:14', '2025-08-11 19:50:10', 22),
(36, 12, 2, NULL, 115, 'Initial', '2025-08-03 23:57:19', '2025-08-03 23:57:19', 18),
(37, 12, 1, NULL, 150, 'Initial', '2025-08-04 00:01:17', '2025-08-04 00:01:17', 18),
(38, 12, 2, NULL, 115, 'Initial', '2025-08-04 00:02:59', '2025-08-04 00:02:59', 22),
(39, 12, 2, NULL, 128, 'Initial', '2025-08-04 00:06:26', '2025-08-04 00:06:26', 18),
(40, 12, 1, NULL, 50, 'Envoi complet', '2025-08-04 00:07:41', '2025-08-14 20:44:34', 22),
(41, 11, 2, NULL, 100, 'Initial', '2025-08-04 00:26:36', '2025-08-04 00:26:36', 20),
(42, 11, 1, NULL, 100, 'Envoi complet', '2025-08-04 00:28:11', '2025-08-09 20:35:33', 20),
(43, 13, 2, NULL, 148, 'Envoi partiel', '2025-08-04 00:30:26', '2025-08-09 20:24:38', 19),
(44, 13, 1, NULL, 151, 'Envoi partiel', '2025-08-04 00:32:41', '2025-08-09 20:21:28', 19),
(45, 11, 1, NULL, 80, 'Initial', '2025-10-24 11:19:01', '2025-10-24 11:19:01', 20),
(46, 11, 1, NULL, 61, 'Initial', '2025-10-24 11:22:36', '2025-10-24 11:22:36', 23);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `quantity_ordered` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `variant_id`, `quantity_ordered`, `unit_price`) VALUES
(105, 31, 86, 10, 140.00),
(106, 31, 87, 20, 140.00),
(107, 31, 88, 20, 140.00),
(108, 31, 89, 20, 140.00),
(109, 31, 90, 20, 140.00),
(110, 31, 91, 20, 140.00),
(111, 32, 71, 3, 125.00),
(112, 32, 72, 4, 125.00),
(113, 32, 73, 10, 125.00),
(114, 32, 74, 10, 125.00),
(115, 32, 75, 10, 125.00),
(116, 32, 76, 10, 125.00),
(117, 32, 92, 4, 125.00),
(118, 32, 93, 6, 125.00),
(119, 32, 94, 8, 125.00),
(120, 32, 95, 10, 125.00),
(121, 32, 96, 9, 125.00),
(122, 32, 97, 10, 125.00),
(123, 33, 67, 5, 155.00),
(124, 33, 98, 5, 155.00),
(125, 33, 70, 10, 155.00),
(126, 33, 99, 10, 155.00),
(127, 33, 69, 10, 155.00),
(128, 33, 68, 10, 155.00),
(129, 33, 100, 15, 155.00),
(130, 33, 101, 15, 155.00),
(131, 33, 102, 15, 155.00),
(132, 33, 103, 15, 155.00),
(133, 33, 104, 12, 155.00),
(134, 33, 105, 12, 155.00),
(135, 34, 86, 15, 140.00),
(136, 34, 87, 20, 140.00),
(137, 34, 88, 30, 140.00),
(138, 34, 89, 40, 140.00),
(139, 34, 90, 40, 140.00),
(140, 34, 91, 40, 140.00),
(141, 35, 88, 27, 140.00),
(142, 35, 89, 25, 140.00),
(143, 35, 90, 32, 140.00),
(144, 35, 91, 16, 140.00),
(145, 36, 71, 20, 125.00),
(146, 36, 72, 20, 125.00),
(147, 36, 73, 25, 125.00),
(148, 36, 74, 20, 125.00),
(149, 36, 75, 15, 125.00),
(150, 36, 76, 15, 125.00),
(151, 37, 71, 16, 125.00),
(152, 37, 72, 16, 125.00),
(153, 37, 73, 37, 125.00),
(154, 37, 74, 37, 125.00),
(155, 37, 75, 22, 125.00),
(156, 37, 76, 22, 125.00),
(157, 38, 86, 10, 140.00),
(158, 38, 87, 15, 140.00),
(159, 38, 88, 25, 140.00),
(160, 38, 89, 35, 140.00),
(161, 38, 90, 15, 140.00),
(162, 38, 91, 15, 140.00),
(163, 39, 92, 15, 125.00),
(164, 39, 93, 16, 125.00),
(165, 39, 94, 25, 125.00),
(166, 39, 95, 30, 125.00),
(167, 39, 96, 22, 125.00),
(168, 39, 97, 20, 125.00),
(172, 41, 106, 6, 140.00),
(173, 41, 107, 12, 140.00),
(174, 41, 108, 24, 140.00),
(175, 41, 109, 24, 140.00),
(176, 41, 110, 19, 140.00),
(177, 41, 111, 15, 140.00),
(178, 42, 106, 4, 140.00),
(179, 42, 107, 11, 140.00),
(180, 42, 108, 29, 140.00),
(181, 42, 109, 22, 140.00),
(182, 42, 110, 18, 140.00),
(183, 42, 111, 16, 140.00),
(190, 43, 83, 6, 135.00),
(191, 43, 112, 16, 135.00),
(192, 43, 113, 43, 135.00),
(193, 43, 114, 32, 135.00),
(194, 43, 115, 27, 135.00),
(195, 43, 116, 24, 135.00),
(196, 44, 83, 6, 135.00),
(197, 44, 112, 16, 135.00),
(198, 44, 113, 44, 135.00),
(199, 44, 114, 33, 135.00),
(200, 44, 115, 27, 135.00),
(201, 44, 116, 25, 135.00),
(202, 40, 88, 25, 140.00),
(203, 40, 89, 25, 140.00),
(204, 45, 106, 10, 150.00),
(205, 45, 107, 10, 150.00),
(206, 45, 108, 15, 150.00),
(207, 45, 109, 15, 150.00),
(208, 45, 110, 15, 150.00),
(209, 45, 111, 15, 150.00),
(210, 46, 117, 4, 150.00),
(211, 46, 119, 8, 150.00),
(212, 46, 120, 15, 150.00),
(213, 46, 121, 12, 150.00),
(214, 46, 122, 12, 150.00),
(215, 46, 123, 10, 150.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `proof_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `supplier_id`, `payment_date`, `amount`, `payment_method`, `notes`, `proof_file`) VALUES
(45, 13, '2025-08-09', 26200.00, 'Virement', '', NULL),
(46, 11, '2025-08-09', 24000.00, 'Virement', '', NULL),
(49, 13, '2025-08-14', 2500.00, 'Virement', 'amine', 'uploads/payments/13/1755199854_WhatsApp Image 2025-08-14 at 19.50.20.jpeg'),
(50, 12, '2025-08-14', 87820.00, 'Virement', '', NULL),
(51, 12, '2025-08-14', 12700.00, 'Virement', '', NULL),
(52, 12, '2025-08-15', 3000.00, 'Virement', '', NULL),
(53, 12, '2025-08-16', 2500.00, 'Virement', '', NULL),
(54, 13, '2025-08-16', 2500.00, 'Virement', '', NULL),
(55, 13, '2025-08-18', 2000.00, 'Virement', '', 'uploads/payments/13/1755527319_WhatsApp Image 2025-08-18 à 15.27.24_cc90dedf.jpg'),
(56, 12, '2025-08-21', 2500.00, 'Virement', '', NULL),
(57, 12, '2025-08-22', 5000.00, 'Virement', '', NULL),
(58, 13, '2025-08-23', 2500.00, 'Virement', '', NULL),
(59, 11, '2025-08-15', 2000.00, 'Virement', '', NULL),
(60, 11, '2025-09-01', 2000.00, 'Virement', '', NULL),
(61, 12, '2025-08-30', 6000.00, 'Virement', '', NULL),
(62, 12, '2025-09-01', 4500.00, 'Virement', '', NULL),
(63, 13, '2025-09-06', 4000.00, 'Virement', '', NULL),
(64, 12, '2025-09-14', 6000.00, 'Virement', '', NULL),
(65, 13, '2025-09-22', 665.00, 'Virement', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payment_allocations`
--

CREATE TABLE `payment_allocations` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `amount_allocated` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_allocations`
--

INSERT INTO `payment_allocations` (`id`, `payment_id`, `order_id`, `amount_allocated`) VALUES
(44, 45, 44, 20385.00),
(45, 45, 43, 5815.00),
(46, 46, 42, 14000.00),
(47, 46, 41, 10000.00),
(55, 49, 43, 2500.00),
(56, 50, 35, 14000.00),
(57, 50, 34, 25900.00),
(58, 50, 33, 20770.00),
(59, 50, 32, 11750.00),
(60, 50, 31, 15400.00),
(61, 51, 36, 12700.00),
(62, 52, 40, 3000.00),
(63, 53, 40, 825.00),
(64, 53, 36, 1675.00),
(65, 54, 43, 2500.00),
(66, 55, 43, 2000.00),
(67, 56, 40, 2500.00),
(68, 57, 37, 5000.00),
(69, 58, 43, 2500.00),
(70, 59, 41, 2000.00),
(71, 60, 41, 2000.00),
(72, 61, 40, 675.00),
(73, 61, 37, 5325.00),
(74, 62, 38, 4500.00),
(75, 63, 43, 4000.00),
(76, 64, 37, 6000.00),
(77, 65, 43, 665.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `category`, `image_path`) VALUES
(17, 'Boutine', '', 'Classe ', 'uploads/products/1753838885_93014d53-6917-4511-b688-51da9bbc3c43_Untitled-design-16-1_png.webp'),
(18, 'Basket S30', '', 'Sport', 'uploads/products/1753838911_6_c631f137-b46a-4fad-9788-79a2d9cf16ee (1).webp'),
(19, 'Oxforde ', '', 'Classse ', 'uploads/products/1753838931_3b27eeee682087fb5ad662892173f11c-e1727201656621.webp'),
(20, 'Classico SM92', '', 'Classse ', 'uploads/products/1753838976_16_720x_81e7571b-f096-4f35-929f-14a11dd7a8a8.webp'),
(21, 'Derby', 'https://semelledor.com/products/derby', 'Classe', 'uploads/products/1754082105_WhatsApp Image 2025-07-24 at 14.52.29.jpeg'),
(22, 'Capri ', '', 'Classse  ', 'uploads/products/1754250168_CapriPelleNera.webp'),
(23, 'Brogue SM99 - Noir', '', 'Classse ', 'uploads/products/1761304861_bon_33_1.webp'),
(24, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `real_stock_view`
-- (See below for the actual view)
--
CREATE TABLE `real_stock_view` (
`country_id` int(11)
,`variant_id` int(11)
,`product_name` varchar(100)
,`size` varchar(10)
,`color` varchar(50)
,`total_received` decimal(32,0)
,`total_sold` decimal(32,0)
,`manual_adjustment` decimal(32,0)
,`current_stock` decimal(34,0)
);

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `shipment_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `receipt_path` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT 'En attente de confirmation',
  `delivery_comment` text DEFAULT NULL,
  `is_stock_added` tinyint(1) DEFAULT 0,
  `transport_id` int(11) DEFAULT NULL,
  `tracking_code` varchar(100) DEFAULT NULL,
  `package_weight` decimal(8,2) DEFAULT NULL,
  `transport_fee` decimal(10,2) DEFAULT NULL,
  `package_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipments`
--

INSERT INTO `shipments` (`id`, `order_id`, `shipment_date`, `notes`, `receipt_path`, `status`, `delivery_comment`, `is_stock_added`, `transport_id`, `tracking_code`, `package_weight`, `transport_fee`, `package_image`) VALUES
(27, 44, '2025-08-09', '', NULL, 'En attente de confirmation', NULL, 0, 1, '24-C281', NULL, NULL, NULL),
(28, 43, '2025-08-09', '', NULL, 'En attente de confirmation', NULL, 0, 1, '', 91.20, NULL, NULL),
(29, 42, '2025-08-09', '', NULL, 'Expédié', NULL, 0, 1, '', 83.40, NULL, NULL),
(30, 41, '2025-08-07', '', NULL, 'En attente de confirmation', NULL, 0, 3, '', NULL, NULL, NULL),
(31, 31, '2025-08-09', '', NULL, 'Arrivé à destination', NULL, 0, 1, '', NULL, NULL, NULL),
(32, 35, '2025-08-14', '', NULL, 'Arrivé à destination', NULL, 0, 1, '', NULL, NULL, NULL),
(33, 32, '2025-08-14', '', NULL, 'Arrivé à destination', NULL, 0, 1, '', NULL, NULL, NULL),
(34, 34, '2025-08-14', '', NULL, 'Arrivé à destination', NULL, 0, 1, '', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `shipment_items`
--

CREATE TABLE `shipment_items` (
  `id` int(11) NOT NULL,
  `shipment_id` int(11) DEFAULT NULL,
  `order_item_id` int(11) DEFAULT NULL,
  `quantity_sent` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipment_items`
--

INSERT INTO `shipment_items` (`id`, `shipment_id`, `order_item_id`, `quantity_sent`) VALUES
(118, 27, 196, 3),
(119, 27, 197, 14),
(120, 27, 198, 18),
(121, 27, 199, 14),
(122, 27, 200, 14),
(123, 27, 201, 7),
(124, 28, 190, 6),
(125, 28, 191, 16),
(126, 28, 192, 21),
(127, 28, 193, 20),
(128, 28, 194, 20),
(129, 28, 195, 10),
(130, 29, 178, 4),
(131, 29, 179, 11),
(132, 29, 180, 29),
(133, 29, 181, 22),
(134, 29, 182, 18),
(135, 29, 183, 16),
(136, 30, 173, 2),
(137, 30, 174, 24),
(138, 30, 175, 14),
(139, 30, 176, 10),
(140, 31, 105, 10),
(141, 31, 106, 20),
(142, 31, 107, 20),
(143, 31, 108, 20),
(144, 31, 109, 20),
(145, 31, 110, 20),
(146, 32, 141, 27),
(147, 32, 142, 25),
(148, 32, 143, 32),
(149, 32, 144, 16),
(150, 33, 111, 3),
(151, 33, 112, 4),
(152, 33, 113, 10),
(153, 33, 114, 10),
(154, 33, 115, 10),
(155, 33, 116, 10),
(156, 33, 117, 4),
(157, 33, 118, 6),
(158, 33, 119, 8),
(159, 33, 120, 10),
(160, 33, 121, 9),
(161, 33, 122, 10),
(162, 34, 135, 15),
(163, 34, 136, 16),
(164, 34, 137, 26),
(165, 34, 138, 31),
(166, 34, 139, 29),
(167, 34, 140, 29);

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

CREATE TABLE `stocks` (
  `id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_adjustments`
--

CREATE TABLE `stock_adjustments` (
  `id` int(11) NOT NULL,
  `country_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `adjusted_quantity` int(11) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `adjusted_at` datetime DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_adjustments`
--

INSERT INTO `stock_adjustments` (`id`, `country_id`, `variant_id`, `adjusted_quantity`, `reason`, `adjusted_at`, `created_at`) VALUES
(10, 1, 87, -1, 'dedeouanement ', '2025-08-14 21:47:48', '2025-08-14 21:47:48'),
(11, 1, 88, -1, 'dedeouanement ', '2025-08-14 21:47:58', '2025-08-14 21:47:58');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `contact_info` text DEFAULT NULL,
  `contact_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `contact_info`, `contact_name`, `phone`, `email`, `address`, `created_at`) VALUES
(11, 'Younes', NULL, 'El Ghabra', '+212622310545', 'youness@gmail.com', '', '2025-07-30 01:15:31'),
(12, 'Mohamed ', NULL, 'Najjari', '+212664280792', 'najjari@gmail.com', '', '2025-07-30 01:16:16'),
(13, 'Abdeali', NULL, 'Haite', '+212689237655', 'ali@gmail.com', '', '2025-07-30 01:17:02');

-- --------------------------------------------------------

--
-- Table structure for table `transports`
--

CREATE TABLE `transports` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `transport_type` varchar(100) DEFAULT NULL,
  `contact_info` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transports`
--

INSERT INTO `transports` (`id`, `name`, `transport_type`, `contact_info`, `created_at`) VALUES
(1, 'Cargo', '🚚 Routier', '0655643328', '2025-08-02 14:34:15'),
(3, 'Nahda Business', '✈️ Aérien', '9010110101', '2025-08-02 14:38:49'),
(4, 'Mali', 'Maritime', '0010101', '2025-08-03 13:11:41');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Mohammed Lyazidi', 'med.pb.pca@gmail.com', 'h4l6bsWCCL1Hosc3HG6wBPd1pHu8gWe', 'user'),
(2, 'Mohammed', 'simo@gmail.com', '$2y$10$1x5zE1kJJw460.CTlptiV.h4l6bsWCCL1Hosc3HG6wBPd1pHu8gWe', 'user');

-- --------------------------------------------------------

--
-- Table structure for table `variants`
--

CREATE TABLE `variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `size` varchar(10) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `sku` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `variants`
--

INSERT INTO `variants` (`id`, `product_id`, `size`, `color`, `sku`) VALUES
(67, 17, '40', 'Noir', '17-40-NOIR'),
(68, 17, '42', 'Marron ', '17-42-MARRON '),
(69, 17, '42', 'Noir', '17-42-NOIR'),
(70, 17, '41', 'Noir', '17-41-NOIR'),
(71, 18, '40', 'Noir', '18-40-NOIR'),
(72, 18, '41', 'Noir', '18-41-NOIR'),
(73, 18, '42', 'Noir', '18-42-NOIR'),
(74, 18, '43', 'Noir', '18-43-NOIR'),
(75, 18, '44', 'Noir', '18-44-NOIR'),
(76, 18, '45', 'Noir', '18-45-NOIR'),
(77, 19, '33', 'KAKA', '19-33-KAKA'),
(78, 21, '40', 'Marron', '21-40-MARRON'),
(79, 21, '41', 'Marron', '21-41-MARRON'),
(80, 21, '42', 'Marron', '21-42-MARRON'),
(81, 21, '43', 'Marron', '21-43-MARRON'),
(82, 21, '44', 'Marron', '21-44-MARRON'),
(83, 19, '40', 'Noir', '19-40-NOIR'),
(86, 22, '40', 'Noir', '22-40-NOIR'),
(87, 22, '41', 'Noir', '22-41-NOIR'),
(88, 22, '42', 'Noir', '22-42-NOIR'),
(89, 22, '43', 'Noir', '22-43-NOIR'),
(90, 22, '44', 'Noir', '22-44-NOIR'),
(91, 22, '45', 'Noir', '22-45-NOIR'),
(92, 18, '40', 'Marron', '18-40-MARRON'),
(93, 18, '41', 'Marron', '18-41-MARRON'),
(94, 18, '42', 'Marron', '18-42-MARRON'),
(95, 18, '43', 'Marron', '18-43-MARRON'),
(96, 18, '44', 'Marron', '18-44-MARRON'),
(97, 18, '45', 'Marron', '18-45-MARRON'),
(98, 17, '40', 'Marron ', '17-40-MARRON '),
(99, 17, '41', 'Marron', '17-41-MARRON'),
(100, 17, '43', 'Noir', '17-43-NOIR'),
(101, 17, '43', 'Marron', '17-43-MARRON'),
(102, 17, '44', 'Noir', '17-44-NOIR'),
(103, 17, '44', 'Marron', '17-44-MARRON'),
(104, 17, '45', 'Noir', '17-45-NOIR'),
(105, 17, '45', 'Marron', '17-45-MARRON'),
(106, 20, '40', 'Noir', '20-40-NOIR'),
(107, 20, '41', 'Noir', '20-41-NOIR'),
(108, 20, '42', 'Noir', '20-42-NOIR'),
(109, 20, '43', 'Noir', '20-43-NOIR'),
(110, 20, '44', 'Noir', '20-44-NOIR'),
(111, 20, '45', 'Noir', '20-45-NOIR'),
(112, 19, '41', 'Noir', '19-41-NOIR'),
(113, 19, '42', 'Noir', '19-42-NOIR'),
(114, 19, '43', 'Noir', '19-43-NOIR'),
(115, 19, '44', 'Noir', '19-44-NOIR'),
(116, 19, '45', 'Noir', '19-45-NOIR'),
(117, 23, '40', 'Noir', ''),
(119, 23, '41', 'Noir', '23-41-NOIR'),
(120, 23, '42', 'Noir', '23-42-NOIR'),
(121, 23, '43', 'Noir', '23-43-NOIR'),
(122, 23, '44', 'Noir', '23-44-NOIR'),
(123, 23, '45', 'Noir', '23-45-NOIR');

-- --------------------------------------------------------

--
-- Structure for view `real_stock_view`
--
DROP TABLE IF EXISTS `real_stock_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`quwaejeq_tmpO1FCF`@`localhost` SQL SECURITY INVOKER VIEW `real_stock_view`  AS SELECT `o`.`country_id` AS `country_id`, `v`.`id` AS `variant_id`, `p`.`name` AS `product_name`, `v`.`size` AS `size`, `v`.`color` AS `color`, coalesce(sum(`si`.`quantity_sent`),0) AS `total_received`, coalesce((select sum(`csi`.`quantity_sold`) from (`client_sale_items` `csi` join `client_sales` `cs` on(`cs`.`id` = `csi`.`sale_id`)) where `cs`.`country_id` = `o`.`country_id` and `csi`.`variant_id` = `v`.`id`),0) AS `total_sold`, coalesce((select sum(`sa`.`adjusted_quantity`) from `stock_adjustments` `sa` where `sa`.`country_id` = `o`.`country_id` and `sa`.`variant_id` = `v`.`id`),0) AS `manual_adjustment`, coalesce(sum(`si`.`quantity_sent`),0) - coalesce((select sum(`csi`.`quantity_sold`) from (`client_sale_items` `csi` join `client_sales` `cs` on(`cs`.`id` = `csi`.`sale_id`)) where `cs`.`country_id` = `o`.`country_id` and `csi`.`variant_id` = `v`.`id`),0) + coalesce((select sum(`sa`.`adjusted_quantity`) from `stock_adjustments` `sa` where `sa`.`country_id` = `o`.`country_id` and `sa`.`variant_id` = `v`.`id`),0) AS `current_stock` FROM (((((`shipments` `s` join `shipment_items` `si` on(`si`.`shipment_id` = `s`.`id`)) join `order_items` `oi` on(`si`.`order_item_id` = `oi`.`id`)) join `orders` `o` on(`s`.`order_id` = `o`.`id`)) join `variants` `v` on(`oi`.`variant_id` = `v`.`id`)) join `products` `p` on(`v`.`product_id` = `p`.`id`)) WHERE `s`.`status` = 'Arrivé à destination' GROUP BY `o`.`country_id`, `v`.`id` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `client_sales`
--
ALTER TABLE `client_sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `country_id` (`country_id`);

--
-- Indexes for table `client_sale_items`
--
ALTER TABLE `client_sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `country_stocks`
--
ALTER TABLE `country_stocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_country_variant` (`country_id`,`variant_id`),
  ADD UNIQUE KEY `country_variant_unique` (`country_id`,`variant_id`),
  ADD KEY `fk_variant` (`variant_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `fk_orders_country` (`country_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_payments_supplier` (`supplier_id`);

--
-- Indexes for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `fk_shipment_transport` (`transport_id`);

--
-- Indexes for table `shipment_items`
--
ALTER TABLE `shipment_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shipment_id` (`shipment_id`),
  ADD KEY `order_item_id` (`order_item_id`);

--
-- Indexes for table `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `country_id` (`country_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transports`
--
ALTER TABLE `transports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `variants`
--
ALTER TABLE `variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `client_sales`
--
ALTER TABLE `client_sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `client_sale_items`
--
ALTER TABLE `client_sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `country_stocks`
--
ALTER TABLE `country_stocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=216;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `shipment_items`
--
ALTER TABLE `shipment_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=168;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `transports`
--
ALTER TABLE `transports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `variants`
--
ALTER TABLE `variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `client_sales`
--
ALTER TABLE `client_sales`
  ADD CONSTRAINT `client_sales_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`);

--
-- Constraints for table `client_sale_items`
--
ALTER TABLE `client_sale_items`
  ADD CONSTRAINT `client_sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `client_sales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `client_sale_items_ibfk_2` FOREIGN KEY (`variant_id`) REFERENCES `variants` (`id`);

--
-- Constraints for table `country_stocks`
--
ALTER TABLE `country_stocks`
  ADD CONSTRAINT `fk_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_variant` FOREIGN KEY (`variant_id`) REFERENCES `variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`variant_id`) REFERENCES `variants` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Constraints for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  ADD CONSTRAINT `payment_allocations_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`),
  ADD CONSTRAINT `payment_allocations_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `shipments`
--
ALTER TABLE `shipments`
  ADD CONSTRAINT `fk_shipment_transport` FOREIGN KEY (`transport_id`) REFERENCES `transports` (`id`),
  ADD CONSTRAINT `shipments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `shipment_items`
--
ALTER TABLE `shipment_items`
  ADD CONSTRAINT `shipment_items_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`),
  ADD CONSTRAINT `shipment_items_ibfk_2` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`);

--
-- Constraints for table `stocks`
--
ALTER TABLE `stocks`
  ADD CONSTRAINT `stocks_ibfk_1` FOREIGN KEY (`variant_id`) REFERENCES `variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  ADD CONSTRAINT `stock_adjustments_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
  ADD CONSTRAINT `stock_adjustments_ibfk_2` FOREIGN KEY (`variant_id`) REFERENCES `variants` (`id`);

--
-- Constraints for table `variants`
--
ALTER TABLE `variants`
  ADD CONSTRAINT `variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
