-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Jun 09, 2026 at 02:07 PM
-- Server version: 8.0.44
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `oqkitob`
--

-- --------------------------------------------------------

--
-- Table structure for table `app_finance_categories`
--

CREATE TABLE `app_finance_categories` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `book_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('income','expense') COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_finance_transactions`
--

CREATE TABLE `app_finance_transactions` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `book_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('income','expense') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency_code` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_date` date NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_minishop_categories`
--

CREATE TABLE `app_minishop_categories` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `book_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_minishop_customers`
--

CREATE TABLE `app_minishop_customers` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `book_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `reminder_at` datetime DEFAULT NULL,
  `reminder_note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `app_minishop_customers`
--

INSERT INTO `app_minishop_customers` (`id`, `book_id`, `created_by`, `name`, `phone`, `note`, `reminder_at`, `reminder_note`, `created_at`, `updated_at`, `deleted_at`) VALUES
('9dd15c52-3d62-463c-9bfd-4ebae40969e7', '3ed398e7-591a-4eb0-b5a4-e26437cb27e0', '11111111-1111-1111-1111-111111111111', 'Ahmad', '+998001111111', NULL, NULL, NULL, '2026-06-01 08:35:07', '2026-06-01 08:35:07', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `app_minishop_products`
--

CREATE TABLE `app_minishop_products` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `book_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(15,2) NOT NULL,
  `quantity` decimal(10,3) NOT NULL DEFAULT '0.000',
  `low_stock_alert` decimal(10,3) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `app_minishop_products`
--

INSERT INTO `app_minishop_products` (`id`, `book_id`, `created_by`, `category_id`, `name`, `sku`, `price`, `quantity`, `low_stock_alert`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
('0c75507d-b7d1-4d01-a62c-5e4e5e8a4401', '3ed398e7-591a-4eb0-b5a4-e26437cb27e0', '11111111-1111-1111-1111-111111111111', NULL, 'Qizil Lola guli', NULL, 2500.00, 2.000, 3.000, 1, '2026-06-01 08:34:29', '2026-06-01 13:10:10', NULL),
('9b991bbb-ef4a-4d29-b484-d4d5ac326635', '3ed398e7-591a-4eb0-b5a4-e26437cb27e0', '11111111-1111-1111-1111-111111111111', NULL, 'Ajoyib gul', NULL, 1200.00, 1.000, 3.000, 1, '2026-06-01 08:34:16', '2026-06-01 13:10:10', NULL),
('d2b50c1e-edea-45d3-bf4f-36b152d37f42', '3ed398e7-591a-4eb0-b5a4-e26437cb27e0', '11111111-1111-1111-1111-111111111111', NULL, 'Romashka guli', NULL, 3000.00, 2.000, 3.000, 1, '2026-06-01 08:34:42', '2026-06-01 11:24:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `app_minishop_sales`
--

CREATE TABLE `app_minishop_sales` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `book_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency_code` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtotal_amount` decimal(15,2) NOT NULL,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `due_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `payment_status` enum('unpaid','partial','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `note` text COLLATE utf8mb4_unicode_ci,
  `sold_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `app_minishop_sales`
--

INSERT INTO `app_minishop_sales` (`id`, `book_id`, `created_by`, `customer_id`, `currency_code`, `subtotal_amount`, `discount_amount`, `total_amount`, `paid_amount`, `due_amount`, `payment_status`, `note`, `sold_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
('18f9d966-0907-42dc-abe6-4b382e022d66', '3ed398e7-591a-4eb0-b5a4-e26437cb27e0', '11111111-1111-1111-1111-111111111111', NULL, 'UZS', 6700.00, 0.00, 6700.00, 6700.00, 0.00, 'paid', NULL, '2026-06-01 15:30:12', '2026-06-01 10:30:12', '2026-06-01 10:30:12', NULL),
('1f4c7b8f-ab90-4032-8283-323cda89b3ac', '3ed398e7-591a-4eb0-b5a4-e26437cb27e0', '11111111-1111-1111-1111-111111111111', NULL, 'UZS', 6700.00, 0.00, 6700.00, 6700.00, 0.00, 'paid', NULL, '2026-06-01 16:24:14', '2026-06-01 11:24:14', '2026-06-01 11:24:14', NULL),
('2c0795ce-eeda-406a-aac6-4886989b3950', '3ed398e7-591a-4eb0-b5a4-e26437cb27e0', '11111111-1111-1111-1111-111111111111', NULL, 'UZS', 3000.00, 0.00, 3000.00, 0.00, 3000.00, 'unpaid', NULL, '2026-06-01 15:40:19', '2026-06-01 10:40:19', '2026-06-01 13:02:58', NULL),
('2c6d6694-fc3a-4848-b3dd-accbf534765a', '3ed398e7-591a-4eb0-b5a4-e26437cb27e0', '11111111-1111-1111-1111-111111111111', NULL, 'UZS', 6700.00, 0.00, 6700.00, 6700.00, 0.00, 'paid', NULL, '2026-06-01 16:24:03', '2026-06-01 11:24:03', '2026-06-01 11:24:03', NULL),
('4a010e7e-5091-40ad-99a2-1b6f076f5c7b', '3ed398e7-591a-4eb0-b5a4-e26437cb27e0', '11111111-1111-1111-1111-111111111111', NULL, 'UZS', 3700.00, 0.00, 3700.00, 3700.00, 0.00, 'paid', NULL, '2026-06-01 18:10:10', '2026-06-01 13:10:10', '2026-06-01 13:10:10', NULL),
('5b93a30b-bfd9-4d49-af31-5637dc38ee76', '3ed398e7-591a-4eb0-b5a4-e26437cb27e0', '11111111-1111-1111-1111-111111111111', NULL, 'UZS', 7900.00, 900.00, 7000.00, 1900.00, 5100.00, 'partial', NULL, '2026-06-01 15:39:32', '2026-06-01 10:39:32', '2026-06-01 10:39:32', NULL),
('7046dc26-87d9-45ec-9b97-3ae318f33347', '3ed398e7-591a-4eb0-b5a4-e26437cb27e0', '11111111-1111-1111-1111-111111111111', NULL, 'UZS', 3700.00, 0.00, 3700.00, 3700.00, 0.00, 'paid', NULL, '2026-06-01 18:08:38', '2026-06-01 13:08:38', '2026-06-01 13:08:38', NULL),
('86cf2064-c46a-47e0-ab13-1df6e2d184ed', '3ed398e7-591a-4eb0-b5a4-e26437cb27e0', '11111111-1111-1111-1111-111111111111', NULL, 'UZS', 6700.00, 0.00, 6700.00, 6700.00, 0.00, 'paid', NULL, '2026-06-01 14:56:55', '2026-06-01 09:56:55', '2026-06-01 09:56:55', NULL),
('db4ac82b-c43c-4df2-a31d-313d04922dc4', '3ed398e7-591a-4eb0-b5a4-e26437cb27e0', '11111111-1111-1111-1111-111111111111', '9dd15c52-3d62-463c-9bfd-4ebae40969e7', 'UZS', 9700.00, 0.00, 9700.00, 9000.00, 700.00, 'partial', NULL, '2026-06-01 13:35:15', '2026-06-01 08:35:15', '2026-06-01 08:35:15', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `app_minishop_sale_items`
--

CREATE TABLE `app_minishop_sale_items` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sale_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_sku` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` decimal(10,3) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `line_total` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `app_minishop_sale_items`
--

INSERT INTO `app_minishop_sale_items` (`id`, `sale_id`, `product_id`, `product_name`, `product_sku`, `quantity`, `unit_price`, `line_total`) VALUES
('0e5c4f1d-46ac-4b10-af06-0a7211fbb766', '86cf2064-c46a-47e0-ab13-1df6e2d184ed', 'd2b50c1e-edea-45d3-bf4f-36b152d37f42', 'Romashka guli', NULL, 1.000, 3000.00, 3000.00),
('114f11d2-df22-4282-9860-8176b7ebc4e4', '5b93a30b-bfd9-4d49-af31-5637dc38ee76', 'd2b50c1e-edea-45d3-bf4f-36b152d37f42', 'Romashka guli', NULL, 1.000, 3000.00, 3000.00),
('14bed4e3-9b46-49d0-b5a2-17b73c85c36f', '1f4c7b8f-ab90-4032-8283-323cda89b3ac', 'd2b50c1e-edea-45d3-bf4f-36b152d37f42', 'Romashka guli', NULL, 1.000, 3000.00, 3000.00),
('15486e3e-3d56-47ad-ba88-a69625d932d0', '7046dc26-87d9-45ec-9b97-3ae318f33347', '9b991bbb-ef4a-4d29-b484-d4d5ac326635', 'Ajoyib gul', NULL, 1.000, 1200.00, 1200.00),
('23e83459-8e6b-405d-b4e2-92e1c91f22e7', '5b93a30b-bfd9-4d49-af31-5637dc38ee76', '0c75507d-b7d1-4d01-a62c-5e4e5e8a4401', 'Qizil Lola guli', NULL, 1.000, 2500.00, 2500.00),
('249f25b6-7fa5-4008-8023-da46abd6c45d', '86cf2064-c46a-47e0-ab13-1df6e2d184ed', '0c75507d-b7d1-4d01-a62c-5e4e5e8a4401', 'Qizil Lola guli', NULL, 1.000, 2500.00, 2500.00),
('2b3ab9c8-3831-41a2-986e-a23d75d27fa6', 'db4ac82b-c43c-4df2-a31d-313d04922dc4', '0c75507d-b7d1-4d01-a62c-5e4e5e8a4401', 'Qizil Lola guli', NULL, 1.000, 2500.00, 2500.00),
('31c74663-262b-4e7d-a581-07091f198ad6', '5b93a30b-bfd9-4d49-af31-5637dc38ee76', '9b991bbb-ef4a-4d29-b484-d4d5ac326635', 'Ajoyib gul', NULL, 2.000, 1200.00, 2400.00),
('41714273-c94e-43ac-aeef-45537f0f6486', '2c0795ce-eeda-406a-aac6-4886989b3950', 'd2b50c1e-edea-45d3-bf4f-36b152d37f42', 'Romashka guli', NULL, 1.000, 3000.00, 3000.00),
('4bfedcf7-5fd8-48ce-8ad2-a2804511bee0', '2c6d6694-fc3a-4848-b3dd-accbf534765a', '9b991bbb-ef4a-4d29-b484-d4d5ac326635', 'Ajoyib gul', NULL, 1.000, 1200.00, 1200.00),
('57fc7f09-aaf4-4e4b-baa4-e9bd9f709e7c', '7046dc26-87d9-45ec-9b97-3ae318f33347', '0c75507d-b7d1-4d01-a62c-5e4e5e8a4401', 'Qizil Lola guli', NULL, 1.000, 2500.00, 2500.00),
('5a97ea51-c1d1-4b0b-9ab0-7b1a44685abb', '18f9d966-0907-42dc-abe6-4b382e022d66', 'd2b50c1e-edea-45d3-bf4f-36b152d37f42', 'Romashka guli', NULL, 1.000, 3000.00, 3000.00),
('5fc7fa79-ae7c-4f8c-a02d-6e7592619892', 'db4ac82b-c43c-4df2-a31d-313d04922dc4', 'd2b50c1e-edea-45d3-bf4f-36b152d37f42', 'Romashka guli', NULL, 2.000, 3000.00, 6000.00),
('62c56368-8734-45e4-a9d0-4eee4398b398', '18f9d966-0907-42dc-abe6-4b382e022d66', '9b991bbb-ef4a-4d29-b484-d4d5ac326635', 'Ajoyib gul', NULL, 1.000, 1200.00, 1200.00),
('76fb37f2-7bd8-446e-a1d7-583c4f6f0401', '18f9d966-0907-42dc-abe6-4b382e022d66', '0c75507d-b7d1-4d01-a62c-5e4e5e8a4401', 'Qizil Lola guli', NULL, 1.000, 2500.00, 2500.00),
('972d56ab-2e74-47e1-b65b-6e2efb45d985', 'db4ac82b-c43c-4df2-a31d-313d04922dc4', '9b991bbb-ef4a-4d29-b484-d4d5ac326635', 'Ajoyib gul', NULL, 1.000, 1200.00, 1200.00),
('99e41953-db19-45b6-8d0f-8bb53da4929e', '86cf2064-c46a-47e0-ab13-1df6e2d184ed', '9b991bbb-ef4a-4d29-b484-d4d5ac326635', 'Ajoyib gul', NULL, 1.000, 1200.00, 1200.00),
('a77433d8-6c32-4656-bf35-31a1562ce907', '4a010e7e-5091-40ad-99a2-1b6f076f5c7b', '0c75507d-b7d1-4d01-a62c-5e4e5e8a4401', 'Qizil Lola guli', NULL, 1.000, 2500.00, 2500.00),
('c0f076a7-a4b2-44d3-9a34-24c0be237961', '4a010e7e-5091-40ad-99a2-1b6f076f5c7b', '9b991bbb-ef4a-4d29-b484-d4d5ac326635', 'Ajoyib gul', NULL, 1.000, 1200.00, 1200.00),
('d7d0189d-a122-4b71-9e9c-e58b872bca91', '2c6d6694-fc3a-4848-b3dd-accbf534765a', '0c75507d-b7d1-4d01-a62c-5e4e5e8a4401', 'Qizil Lola guli', NULL, 1.000, 2500.00, 2500.00),
('dd73f1a9-132a-4c4f-b17b-c274514d1e25', '1f4c7b8f-ab90-4032-8283-323cda89b3ac', '0c75507d-b7d1-4d01-a62c-5e4e5e8a4401', 'Qizil Lola guli', NULL, 1.000, 2500.00, 2500.00),
('ea10d26e-8588-4da3-a5c5-d5107c93109b', '2c6d6694-fc3a-4848-b3dd-accbf534765a', 'd2b50c1e-edea-45d3-bf4f-36b152d37f42', 'Romashka guli', NULL, 1.000, 3000.00, 3000.00),
('ef226453-7f94-49fb-abf5-20b706de6dcb', '1f4c7b8f-ab90-4032-8283-323cda89b3ac', '9b991bbb-ef4a-4d29-b484-d4d5ac326635', 'Ajoyib gul', NULL, 1.000, 1200.00, 1200.00);

-- --------------------------------------------------------

--
-- Table structure for table `app_minishop_sale_payments`
--

CREATE TABLE `app_minishop_sale_payments` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sale_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency_code` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` enum('cash','card') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `paid_at` datetime NOT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `app_minishop_sale_payments`
--

INSERT INTO `app_minishop_sale_payments` (`id`, `sale_id`, `created_by`, `currency_code`, `amount`, `payment_method`, `paid_at`, `note`, `created_at`) VALUES
('22094e8c-f120-449a-8719-daa19d2d7f70', '4a010e7e-5091-40ad-99a2-1b6f076f5c7b', '11111111-1111-1111-1111-111111111111', 'UZS', 3700.00, 'cash', '2026-06-01 18:10:10', NULL, '2026-06-01 13:10:10'),
('2a281e73-dd45-4882-9e84-f5234247c5a0', '1f4c7b8f-ab90-4032-8283-323cda89b3ac', '11111111-1111-1111-1111-111111111111', 'UZS', 6700.00, 'cash', '2026-06-01 16:24:14', NULL, '2026-06-01 11:24:14'),
('3cd2cd07-e94d-4e96-a294-7e2bbe621f29', '7046dc26-87d9-45ec-9b97-3ae318f33347', '11111111-1111-1111-1111-111111111111', 'UZS', 3700.00, 'cash', '2026-06-01 18:08:38', NULL, '2026-06-01 13:08:38'),
('4746e2e1-23c8-4f3d-9004-fb9db720bf4a', '86cf2064-c46a-47e0-ab13-1df6e2d184ed', '11111111-1111-1111-1111-111111111111', 'UZS', 6700.00, 'cash', '2026-06-01 14:56:55', NULL, '2026-06-01 09:56:55'),
('67d08671-2062-44e3-8530-4360971dc179', '2c6d6694-fc3a-4848-b3dd-accbf534765a', '11111111-1111-1111-1111-111111111111', 'UZS', 6700.00, 'cash', '2026-06-01 16:24:03', NULL, '2026-06-01 11:24:03'),
('74fe860c-282c-4dc0-b9ed-bfdc5ce900a3', '18f9d966-0907-42dc-abe6-4b382e022d66', '11111111-1111-1111-1111-111111111111', 'UZS', 6700.00, 'cash', '2026-06-01 15:30:12', NULL, '2026-06-01 10:30:12'),
('a787e3f0-d5f3-4bb8-8d0c-4bdf6daac069', '5b93a30b-bfd9-4d49-af31-5637dc38ee76', '11111111-1111-1111-1111-111111111111', 'UZS', 1900.00, 'cash', '2026-06-01 15:39:32', NULL, '2026-06-01 10:39:32'),
('dc10d619-e9a9-4098-8fa2-5ad88e04d4f1', 'db4ac82b-c43c-4df2-a31d-313d04922dc4', '11111111-1111-1111-1111-111111111111', 'UZS', 9000.00, 'cash', '2026-06-01 13:35:15', NULL, '2026-06-01 08:35:15');

-- --------------------------------------------------------

--
-- Table structure for table `app_notes`
--

CREATE TABLE `app_notes` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `book_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` int NOT NULL DEFAULT '0',
  `is_pinned` tinyint(1) NOT NULL DEFAULT '0',
  `is_archived` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_service_customers`
--

CREATE TABLE `app_service_customers` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `book_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `messenger` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `location` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `app_service_customers`
--

INSERT INTO `app_service_customers` (`id`, `book_id`, `created_by`, `name`, `phone`, `messenger`, `address`, `location`, `created_at`, `updated_at`, `deleted_at`) VALUES
('27ea7ae3-00d5-4c2b-9b5d-bd323b6313ff', 'f31b3055-1793-4ddf-9e13-abf65c8743fb', '11111111-1111-1111-1111-111111111111', 'Muslim', '904232018', 'vosidiy', 'Xadra 3', NULL, '2026-06-06 07:27:01', '2026-06-06 12:55:21', NULL),
('4efa679d-7c91-498a-ba55-9aed63c3a2b1', 'f31b3055-1793-4ddf-9e13-abf65c8743fb', '11111111-1111-1111-1111-111111111111', 'Muslim 2', '+998946875461', NULL, 'Xadra', NULL, '2026-06-09 13:20:48', '2026-06-09 13:20:48', NULL),
('66f35382-c771-4d1e-9e43-4f952049aac5', 'f31b3055-1793-4ddf-9e13-abf65c8743fb', '11111111-1111-1111-1111-111111111111', 'asdasd', 'asdasd', NULL, NULL, NULL, '2026-06-06 09:12:07', '2026-06-06 12:55:18', NULL),
('7f0218a8-c4c8-4b2e-911b-ed98195ce743', 'f31b3055-1793-4ddf-9e13-abf65c8743fb', '11111111-1111-1111-1111-111111111111', 'Muslimjon', '998946875461', 'vosidiy', 'Xadra 3', NULL, '2026-06-04 13:58:45', '2026-06-06 12:55:12', NULL),
('b1b0a9e9-3f4c-4013-98dd-54924688ef1c', 'f31b3055-1793-4ddf-9e13-abf65c8743fb', '11111111-1111-1111-1111-111111111111', 'Ahmad', '901112233', NULL, 'Toshkent qoy\'liq', NULL, '2026-06-06 09:11:06', '2026-06-06 09:11:06', NULL),
('b2cb4208-73f5-4888-be62-092f6f719fd9', 'f31b3055-1793-4ddf-9e13-abf65c8743fb', '11111111-1111-1111-1111-111111111111', 'Alisher', '+998971112233', NULL, 'Chorsu 3 10', 'Mijoz 112321', '2026-06-06 13:20:54', '2026-06-06 13:22:31', NULL),
('c06eaa68-072e-411c-9269-0f69e41daafe', 'f31b3055-1793-4ddf-9e13-abf65c8743fb', '11111111-1111-1111-1111-111111111111', 'Muslim VD', '946875461', NULL, NULL, NULL, '2026-06-09 13:21:45', '2026-06-09 13:21:45', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `app_service_orders`
--

CREATE TABLE `app_service_orders` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `book_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency_code` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtotal_amount` decimal(15,2) NOT NULL,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(15,2) NOT NULL,
  `order_status` enum('received','working','ready','delivered') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'received',
  `note` text COLLATE utf8mb4_unicode_ci,
  `received_at` datetime NOT NULL,
  `ready_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `app_service_orders`
--

INSERT INTO `app_service_orders` (`id`, `book_id`, `created_by`, `customer_id`, `currency_code`, `subtotal_amount`, `discount_amount`, `total_amount`, `order_status`, `note`, `received_at`, `ready_at`, `delivered_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
('1bb3b912-2778-4d56-8d30-33233d6567b7', 'f31b3055-1793-4ddf-9e13-abf65c8743fb', '11111111-1111-1111-1111-111111111111', '7f0218a8-c4c8-4b2e-911b-ed98195ce743', 'UZS', 40000.00, 0.00, 40000.00, 'received', NULL, '2026-06-06 12:36:29', NULL, NULL, '2026-06-06 12:36:29', '2026-06-08 08:23:32', NULL),
('26012c6d-ec66-484e-922c-f159ad60c42c', 'f31b3055-1793-4ddf-9e13-abf65c8743fb', '11111111-1111-1111-1111-111111111111', 'b1b0a9e9-3f4c-4013-98dd-54924688ef1c', 'UZS', 324000.00, 0.00, 324000.00, 'working', 'Vaqtida yetkazish srochni', '2026-06-06 09:11:06', NULL, NULL, '2026-06-06 09:11:06', '2026-06-09 13:29:16', NULL),
('459734cd-fd07-42be-a926-eb1c0839b833', 'f31b3055-1793-4ddf-9e13-abf65c8743fb', '11111111-1111-1111-1111-111111111111', '27ea7ae3-00d5-4c2b-9b5d-bd323b6313ff', 'UZS', 140000.00, 5000.00, 135000.00, 'delivered', NULL, '2026-06-06 07:27:01', '2026-06-06 15:00:46', '2026-06-08 11:50:23', '2026-06-06 07:27:01', '2026-06-09 18:34:26', NULL),
('575b6243-818e-469d-9eae-0ec79ab15140', 'f31b3055-1793-4ddf-9e13-abf65c8743fb', '11111111-1111-1111-1111-111111111111', 'b2cb4208-73f5-4888-be62-092f6f719fd9', 'UZS', 70000.00, 0.00, 70000.00, 'received', 'Dog\'larni ketkazish', '2026-06-06 13:22:00', NULL, NULL, '2026-06-06 13:22:00', '2026-06-08 08:26:35', NULL),
('867bcba0-cbc7-44a1-abdd-160858e80ae7', 'f31b3055-1793-4ddf-9e13-abf65c8743fb', '11111111-1111-1111-1111-111111111111', '7f0218a8-c4c8-4b2e-911b-ed98195ce743', 'UZS', 40000.00, 10000.00, 30000.00, 'delivered', 'Ajoyib izoh umumiy', '2026-06-04 13:58:45', '2026-06-08 08:23:02', '2026-06-08 08:25:23', '2026-06-04 13:58:45', '2026-06-08 08:25:23', NULL),
('e24f8012-5d68-4cd4-b207-8f3127896a79', 'f31b3055-1793-4ddf-9e13-abf65c8743fb', '11111111-1111-1111-1111-111111111111', 'c06eaa68-072e-411c-9269-0f69e41daafe', 'UZS', 20000.00, 0.00, 20000.00, 'received', NULL, '2026-06-09 13:21:45', NULL, NULL, '2026-06-09 13:21:45', '2026-06-09 13:21:45', NULL),
('ea12e80b-ad0f-47a0-8dad-f12222db6b5b', 'f31b3055-1793-4ddf-9e13-abf65c8743fb', '11111111-1111-1111-1111-111111111111', '66f35382-c771-4d1e-9e43-4f952049aac5', 'UZS', 40000.00, 0.00, 40000.00, 'ready', NULL, '2026-06-06 09:12:07', '2026-06-09 13:29:20', NULL, '2026-06-06 09:12:07', '2026-06-09 13:29:20', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `app_service_order_items`
--

CREATE TABLE `app_service_order_items` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_type_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `object_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(10,3) NOT NULL,
  `unit_code` enum('qty','m2','kg') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'qty',
  `unit_price` decimal(15,2) NOT NULL,
  `line_total` decimal(15,2) NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `attachment_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `app_service_order_items`
--

INSERT INTO `app_service_order_items` (`id`, `order_id`, `service_type_id`, `object_name`, `service_name`, `quantity`, `unit_code`, `unit_price`, `line_total`, `note`, `attachment_path`, `sort_order`) VALUES
('10f61444-e20a-47f6-9581-29fa4da04fbc', '1bb3b912-2778-4d56-8d30-33233d6567b7', '8884bd20-75fa-433e-ad4a-f80420be6bd6', 'Shim oq', 'Normal cleaning', 1.000, 'qty', 20000.00, 20000.00, 'Dog\'ni ketkazish', NULL, 0),
('1545d3cb-1d4f-48a4-bb23-6dc4e28bd090', '26012c6d-ec66-484e-922c-f159ad60c42c', '129e9b8a-a307-4c50-9bcd-405da63b0857', 'Gilam 3x4', 'Gilam yuvish deluxe', 12.000, 'm2', 7000.00, 84000.00, NULL, NULL, 1),
('1576353b-8f82-4cb9-be47-5eae3a9bf9d2', '459734cd-fd07-42be-a926-eb1c0839b833', '129e9b8a-a307-4c50-9bcd-405da63b0857', 'Gilam', 'Gilam yuvish deluxe', 20.000, 'm2', 7000.00, 140000.00, NULL, NULL, 0),
('28061c57-58b6-459a-ae1a-cb87d02d4048', '26012c6d-ec66-484e-922c-f159ad60c42c', '8884bd20-75fa-433e-ad4a-f80420be6bd6', 'Gilam', 'Normal cleaning', 2.000, 'qty', 120000.00, 240000.00, NULL, NULL, 0),
('3bbee472-b3cf-4c2d-b5d5-926fae0a8292', '575b6243-818e-469d-9eae-0ec79ab15140', '8884bd20-75fa-433e-ad4a-f80420be6bd6', 'Koylak', 'Normal cleaning', 2.000, 'qty', 25000.00, 50000.00, NULL, NULL, 1),
('681a0b80-7a1f-464a-ae57-e9316748181e', '575b6243-818e-469d-9eae-0ec79ab15140', '8884bd20-75fa-433e-ad4a-f80420be6bd6', 'Palto', 'Normal cleaning', 1.000, 'qty', 20000.00, 20000.00, NULL, NULL, 0),
('8ff6970e-6258-46be-8b1c-c6552f5cd2b4', '1bb3b912-2778-4d56-8d30-33233d6567b7', '8884bd20-75fa-433e-ad4a-f80420be6bd6', 'Shim qora', 'Normal cleaning', 1.000, 'qty', 20000.00, 20000.00, NULL, NULL, 1),
('a7114adf-ee4b-46a7-b500-99c452a50255', 'e24f8012-5d68-4cd4-b207-8f3127896a79', '8884bd20-75fa-433e-ad4a-f80420be6bd6', 'Shim', 'Normal cleaning', 1.000, 'qty', 20000.00, 20000.00, NULL, NULL, 0),
('e05370f4-3c7d-494d-8b6f-c1d0c53362e0', 'ea12e80b-ad0f-47a0-8dad-f12222db6b5b', '8884bd20-75fa-433e-ad4a-f80420be6bd6', 'sadasd', 'Normal cleaning', 2.000, 'qty', 20000.00, 40000.00, NULL, NULL, 0),
('e13507de-a5c7-4289-8224-0aa27e8940bc', '867bcba0-cbc7-44a1-abdd-160858e80ae7', '8884bd20-75fa-433e-ad4a-f80420be6bd6', 'Shim', 'Normal cleaning', 2.000, 'qty', 20000.00, 40000.00, 'Dog\'larni ketkazish', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `app_service_types`
--

CREATE TABLE `app_service_types` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `book_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `default_unit` enum('qty','m2','kg') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'qty',
  `default_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `app_service_types`
--

INSERT INTO `app_service_types` (`id`, `book_id`, `created_by`, `name`, `default_unit`, `default_price`, `sort_order`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
('129e9b8a-a307-4c50-9bcd-405da63b0857', 'f31b3055-1793-4ddf-9e13-abf65c8743fb', '11111111-1111-1111-1111-111111111111', 'Gilam yuvish deluxe', 'm2', 7000.00, 1, 1, '2026-06-04 13:55:26', '2026-06-04 13:55:26', NULL),
('80fc7edc-e7a7-437b-af69-f2c5f86991e8', 'f31b3055-1793-4ddf-9e13-abf65c8743fb', '11111111-1111-1111-1111-111111111111', 'All service', 'm2', 0.00, 4, 1, '2026-06-06 10:22:36', '2026-06-06 10:25:48', '2026-06-06 10:25:48'),
('8884bd20-75fa-433e-ad4a-f80420be6bd6', 'f31b3055-1793-4ddf-9e13-abf65c8743fb', '11111111-1111-1111-1111-111111111111', 'Normal cleaning', 'qty', 20000.00, 0, 1, '2026-06-04 13:54:56', '2026-06-06 10:23:22', NULL),
('bdfc7de5-3e00-4def-b0bc-a8141b323406', 'f31b3055-1793-4ddf-9e13-abf65c8743fb', '11111111-1111-1111-1111-111111111111', 'Tozalash gilam', 'qty', 20000.00, 2, 1, '2026-06-06 10:14:45', '2026-06-06 10:14:45', NULL),
('d3af6e0d-8133-45b4-b564-cd903d400d22', 'f31b3055-1793-4ddf-9e13-abf65c8743fb', '11111111-1111-1111-1111-111111111111', 'Quritish', 'm2', 5000.00, 3, 1, '2026-06-06 10:16:57', '2026-06-06 10:25:28', '2026-06-06 10:25:28');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `currency_code` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `settings_json` json DEFAULT NULL,
  `show_cents` tinyint(1) NOT NULL DEFAULT '1',
  `thousand_separator` enum('comma','dot','space') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'comma',
  `is_archived` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int NOT NULL DEFAULT '0',
  `last_opened_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `user_id`, `type_key`, `currency_code`, `title`, `description`, `icon`, `color`, `settings_json`, `show_cents`, `thousand_separator`, `is_archived`, `sort_order`, `last_opened_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
('123', '11111111-1111-1111-1111-111111111111', 'finance', 'UZS', 'Birinchi kitob', 'Yaxshi kitobvha haqida', NULL, NULL, NULL, 1, 'comma', 1, 3, NULL, '2026-05-13 12:41:35', '2026-05-28 19:55:35', NULL),
('34c416bd-5631-4394-84bc-2e05e436351d', '11111111-1111-1111-1111-111111111111', 'minishop', 'UZS', 'Dukon', NULL, NULL, NULL, NULL, 1, 'comma', 1, 8, NULL, '2026-05-15 20:21:34', '2026-05-28 16:43:29', NULL),
('3ed398e7-591a-4eb0-b5a4-e26437cb27e0', '11111111-1111-1111-1111-111111111111', 'minishop', 'UZS', 'Super Kitobcha', NULL, NULL, NULL, NULL, 1, 'comma', 0, 9, NULL, '2026-05-28 19:08:59', '2026-06-02 15:35:23', NULL),
('57104b19-df50-48c7-ac2f-89bf87dde244', '11111111-1111-1111-1111-111111111111', 'finance', 'UZS', 'moliya', NULL, NULL, NULL, NULL, 1, 'comma', 0, 10, NULL, '2026-05-29 17:56:55', '2026-06-02 15:35:28', NULL),
('aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1', '11111111-1111-1111-1111-111111111111', 'notes', NULL, 'Daily Notes', 'Personal notes book for Ali', NULL, NULL, NULL, 0, 'space', 0, 1, NULL, '2026-05-11 20:52:13', '2026-06-02 16:18:56', NULL),
('b00eb1d2-4403-446d-88bb-b00f9e6ad8d6', '11111111-1111-1111-1111-111111111111', 'minishop', 'UZS', 'My Shop', 'sotuvlarim', NULL, NULL, NULL, 1, 'comma', 1, 7, NULL, '2026-05-15 16:30:28', '2026-06-06 12:31:45', NULL),
('bcc06a2a-f691-460a-9a35-5ef871a8c56b', '11111111-1111-1111-1111-111111111111', 'notes', NULL, 'Salom', 'asdas', NULL, NULL, NULL, 1, 'comma', 1, 6, NULL, '2026-05-13 19:01:30', '2026-05-28 20:00:09', NULL),
('c95e3e81-f172-4a9d-b854-962d4b12031f', '11111111-1111-1111-1111-111111111111', 'finance', 'UZS', 'Salom kitob', 'Asasdashdbas', NULL, NULL, NULL, 1, 'comma', 0, 4, NULL, '2026-05-13 18:29:07', '2026-05-26 15:46:08', '2026-05-18 10:39:48'),
('f31b3055-1793-4ddf-9e13-abf65c8743fb', '11111111-1111-1111-1111-111111111111', 'service', 'UZS', 'Laundry service', NULL, NULL, NULL, NULL, 1, 'comma', 0, 0, NULL, '2026-06-04 11:30:11', '2026-06-04 11:30:11', NULL),
('f4c58109-cce8-4092-9c78-c27f2ec2858f', '11111111-1111-1111-1111-111111111111', 'minishop', 'UZS', 'sd', NULL, NULL, NULL, NULL, 1, 'comma', 1, 9, NULL, '2026-05-15 20:28:45', '2026-05-28 16:36:04', '2026-05-28 11:36:04');

-- --------------------------------------------------------

--
-- Table structure for table `book_types`
--

CREATE TABLE `book_types` (
  `type_key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requires_currency` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `book_types`
--

INSERT INTO `book_types` (`type_key`, `name`, `description`, `requires_currency`, `is_active`, `created_at`, `updated_at`) VALUES
('finance', 'Finance', 'Book type for income and expense tracking', 1, 0, '2026-05-11 20:01:14', '2026-06-01 13:13:39'),
('minishop', 'Min-store', 'Book type for small shop sales and inventory tracking', 1, 1, '2026-05-15 14:01:14', '2026-05-26 15:46:08'),
('notes', 'Notes', 'Book type for note taking', 0, 1, '2026-05-11 20:01:14', '2026-05-26 16:46:46'),
('service', 'Services', 'Book type for service business orders and customer tracking', 1, 1, '2026-06-03 11:52:56', '2026-06-03 11:52:56');

-- --------------------------------------------------------

--
-- Table structure for table `ci_sessions`
--

CREATE TABLE `ci_sessions` (
  `id` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` bigint UNSIGNED NOT NULL DEFAULT '0',
  `data` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ci_sessions`
--

INSERT INTO `ci_sessions` (`id`, `ip_address`, `timestamp`, `data`) VALUES
('ci_session:008bc1eca3245d1bad8f506cd82dcc96', '::1', 20260608164321, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303931393030313b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:049a34eb7574750d35f1c633b2ed6877', '::1', 20260601152947, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303330393738373b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:053f075b8155f581ba0a45a8520485b1', '::1', 20260604164224, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303537333334343b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:0592e33a8b5f5cd9e0765f5040335d7c', '::1', 20260604152343, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303536383632333b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:0744a2c3cfb11f74f43a72ad80fa3b56', '::1', 20260606135156, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303733353931363b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:07c988b6d62e068a0f68bb41b290f695', '::1', 20260608160242, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303931363536323b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:081ae4a2d50f68360112baa3dcbd42ba', '127.0.0.1', 20260601200414, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303332363235343b),
('ci_session:091383e2298b5d496686dd7a82f9ca39', '::1', 20260602171847, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303430323732373b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:0b31cb98139eb94e78c189e21883ce6d', '::1', 20260606192905, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303735363134353b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:0ca6420e408ecc45234d6505931fe88c', '::1', 20260606182330, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303735323231303b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:0dca894e2dbe6a4af9c6aa92c697c1a3', '::1', 20260601151422, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303330383836323b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:0f62da882051642e08548c77b9c67416', '127.0.0.1', 20260601193332, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303332343431323b),
('ci_session:0f6ff983f94ded6bd7104cb36c5f0f4d', '::1', 20260603121708, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303437313032363b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:1370c34757c99bdf1364f19ec1e3fd4d', '::1', 20260604123444, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303535383438343b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:14643e431033179201654bb69f9ad9c3', '::1', 20260604151156, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303536373931363b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:165f98bee0b492ec4bc5c5dd014a8333', '127.0.0.1', 20260601134021, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303330333232313b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:1d330aa68e9b0d5e017538a16e3b3e4a', '::1', 20260606200039, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303735383033393b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:1f4366e57efb3c900016d91b7a906ed6', '127.0.0.1', 20260608144804, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303931323038343b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:207496bf724b5a5cce4f7980507e4b52', '127.0.0.1', 20260601192739, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303332343035393b),
('ci_session:267a9c3b5548131a80be19de290a8bd4', '::1', 20260606185548, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303735343134383b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:2754626fd9b95d98d102c6a4547d9a3f', '::1', 20260604184657, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303538303831373b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:28885e7e35a85c6354039447354f8319', '::1', 20260608150048, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303931323834383b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:288cb873e69dc79c1d00e73c7c55e45c', '::1', 20260604153131, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303536393039313b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:298e622a04b7e4a1211c1afcbcdf92ab', '::1', 20260606140310, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303733363539303b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:29b2a2d29dcff36d237f85cceb74f969', '::1', 20260606141526, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303733373332363b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:2c6269f08436333c297028baff8acb5f', '127.0.0.1', 20260609190619, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738313031333937373b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:2e5a1b198933b216f4eac9419fe47f15', '::1', 20260606191705, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303735353432353b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:2fb0d2a4040c2ab392862c017e39485c', '::1', 20260604142117, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303536343837373b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:36afaf50451544cb97cabcca6cb99d4a', '::1', 20260602160508, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303339383330383b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:3770d785de01e61c32ac374c649abf2c', '::1', 20260604150621, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303536373538313b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:3a346d6d467009ce66b1c6ba8eae4131', '::1', 20260606154254, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303734323537343b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:3c549a109ecc227683a4404a3cf5bff8', '::1', 20260606145622, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303733393738323b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:3fbae1ca6403723dfdfb29ec2484351f', '::1', 20260601182415, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303332303235353b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b5f63695f70726576696f75735f75726c7c733a3134353a22687474703a2f2f6c6f63616c686f73743a383838382f6170692f696e6465782e7068702f7075626c69632f626f6f6b732f33656433393865372d353931612d346562302d623561342d6532363433376362323765302f6d696e6973686f702f73616c65732f34613031306537652d353039312d343061642d393961322d3162366630373666356337622f72656365697074223b),
('ci_session:43c0af1bfcfe40c310c7d0d30ba123b4', '::1', 20260609190617, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738313031333937373b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:442385031d152cc9578cf2f74241949e', '::1', 20260601161724, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303331323634343b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b5f63695f70726576696f75735f75726c7c733a33363a22687474703a2f2f6c6f63616c686f73743a383838382f6170692f696e6465782e7068702f223b),
('ci_session:4593c1300be8a6c6b13acec6fd302f34', '::1', 20260606150421, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303734303236313b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:47056611898c45b2ba878e86426b8f33', '::1', 20260602164408, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303430303634383b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:49cf35ce39d1a135115e61104850ce8a', '::1', 20260604163314, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303537323739343b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:4a087db245dc60e82e95c0b1aa9e77ba', '::1', 20260609182843, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738313031313732333b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:4d083773d0720eaeea8be1a0b0fe0a85', '127.0.0.1', 20260604113553, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303535343935333b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:4d3f5a2b62dce5a31402ac91f06c2d81', '::1', 20260604185924, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303538313536343b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:4e7c401f644381152026ea59ebc57769', '::1', 20260606194855, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303735373333353b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:52364b884d460750847e888b34aa36e0', '::1', 20260601160801, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303331323038313b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b5f63695f70726576696f75735f75726c7c733a33363a22687474703a2f2f6c6f63616c686f73743a383838382f6170692f696e6465782e7068702f223b),
('ci_session:53b8f673a08e13e7f1f88fa073feb504', '::1', 20260604190820, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303538323130303b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:53c13df7495424a28268780071ee7cf0', '::1', 20260606192229, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303735353734393b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:5a5a427c3107babe6e433615d1c53f38', '127.0.0.1', 20260608132716, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303930373233363b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:5aa2606b3563c774916e99e8403c4b90', '::1', 20260601145655, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303330373831353b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:5bc5fa390a0f0c1f95d88d6e5b9f1a08', '::1', 20260609182231, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738313031313335313b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:65d57b1297db149d5d23b72dc38819f0', '127.0.0.1', 20260601191645, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303332333430353b),
('ci_session:6bf0a3571ce42263789e54cd77a21639', '::1', 20260606174152, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303734393731323b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:6c4119af3f1c53ef06f45abfb9309d71', '127.0.0.1', 20260608144244, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303931313736343b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:6e0988c5416975885b16cbfa8ed07c5f', '::1', 20260602161835, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303339393131353b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:732b15c2c858b4345441c1c3c3a85bd5', '::1', 20260601144954, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303330373339343b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:74070cad80c311e098fc8c238af24ae1', '::1', 20260608162225, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303931373734353b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:779b93aa9a5a9f58b583e068badba060', '::1', 20260601180029, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303331383832393b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b5f63695f70726576696f75735f75726c7c733a38383a22687474703a2f2f6c6f63616c686f73743a383838382f6170692f696e6465782e7068702f7075626c69632f72656365697074732f32633037393563652d656564612d343036612d616163362d343838363938396233393530223b),
('ci_session:77f184adaff08956ef66287f6b0e3f80', '::1', 20260606175307, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303735303338373b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:792ed83a1b089d3f80089a9253f01c0f', '::1', 20260601171021, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303331353832313b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b5f63695f70726576696f75735f75726c7c733a38383a22687474703a2f2f6c6f63616c686f73743a383838382f6170692f696e6465782e7068702f7075626c69632f72656365697074732f32633037393563652d656564612d343036612d616163362d343838363938396233393530223b),
('ci_session:79ad54ef452729d27c014ac87d594ab6', '::1', 20260601152209, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303330393332393b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:79d78c2222eebfd1fbf6d4a0d72d3195', '::1', 20260606163237, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303734353535373b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:7a84ee3cf53c203f200a682d0944da0c', '::1', 20260606134126, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303733353238363b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:844a32f095b3710f1499ca123bb4f30c', '127.0.0.1', 20260601200414, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303332363235343b),
('ci_session:8bb7d11986dee6ebf97410780a0e56de', '::1', 20260606124626, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303733313938363b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:8cb5e34f17cd8ded17ba1566ba38ca8b', '::1', 20260601184020, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303332313232303b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b5f63695f70726576696f75735f75726c7c733a3134353a22687474703a2f2f6c6f63616c686f73743a383838382f6170692f696e6465782e7068702f7075626c69632f626f6f6b732f33656433393865372d353931612d346562302d623561342d6532363433376362323765302f6d696e6973686f702f73616c65732f34613031306537652d353039312d343061642d393961322d3162366630373666356337622f72656365697074223b),
('ci_session:8d617dd4c73c3ff24e78e65fac6e94a7', '::1', 20260606144431, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303733393037313b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:8edcf456926f25ed26e685ca0ad3c3c8', '::1', 20260604150031, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303536373233313b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:991d4b0b63acb4144f8e364c51373c4a', '::1', 20260606181822, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303735313930323b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:9ba49abd91b7fe6cb0d0e3b7c6cf4c18', '::1', 20260601153913, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303331303335333b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:a0077cd9d35ce0b500adaa1190820d27', '::1', 20260608161325, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303931373230353b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:a08bf530cf9f37273add9c8dcd1b61af', '::1', 20260601175528, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303331383532383b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b5f63695f70726576696f75735f75726c7c733a38383a22687474703a2f2f6c6f63616c686f73743a383838382f6170692f696e6465782e7068702f7075626c69632f72656365697074732f32633037393563652d656564612d343036612d616163362d343838363938396233393530223b),
('ci_session:a241368be30145fdb5cc6e1b4d4d3b6d', '::1', 20260606161908, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303734343734383b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:a319b86437a1b4fe4692cc99beb8c0e1', '::1', 20260606185004, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303735333830343b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:a594d61d4c97e6b0a8e4012f3560657c', '::1', 20260606152224, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303734313334343b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:a6370f2e7a3d967da1b9e3b6987feace', '::1', 20260606180514, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303735313131343b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:a7ff82496b2fc0cdb4c0a00c505c367b', '::1', 20260601154449, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303331303638393b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:ad0de09521e05085ee3fe121dcc9033b', '::1', 20260604165703, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303537343232333b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:ae03ecf01f77a375bb3facfd297fd584', '::1', 20260608162854, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303931383133343b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:b0a9749cb4e5c16c0535c4caf8702583', '127.0.0.1', 20260601192151, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303332333731313b),
('ci_session:b53a78119c07267398cad88f167fedd0', '::1', 20260601181914, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303331393935343b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b5f63695f70726576696f75735f75726c7c733a3134353a22687474703a2f2f6c6f63616c686f73743a383838382f6170692f696e6465782e7068702f7075626c69632f626f6f6b732f33656433393865372d353931612d346562302d623561342d6532363433376362323765302f6d696e6973686f702f73616c65732f34613031306537652d353039312d343061642d393961322d3162366630373666356337622f72656365697074223b),
('ci_session:b801b9d5f01badc837a7f2fb17796c5b', '::1', 20260606152740, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303734313636303b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:b8258f2ebb1092235a7163ccacdcec3d', '::1', 20260606195423, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303735373636333b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:b83c43e8de43f0070df9cd701eaafd47', '::1', 20260606194345, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303735373032353b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:c06deefd70ad861bdd1d91e4fea3d895', '::1', 20260606123943, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303733313538333b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:c26c4eb90ed0e177fd14a295da0ce249', '::1', 20260606190734, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303735343835343b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:c28eca4b282b1bd1b7def3b0cd7b4100', '::1', 20260606200431, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303735383033393b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:c2a7efdf87d7a6e3b174f6a8d45b77e5', '::1', 20260606172958, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303734383939383b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:c4fe0f81de93f53967a1a64cf96c65a1', '127.0.0.1', 20260608145529, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303931323532393b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:c557b8f8799d3bb344627c9b123e976a', '::1', 20260609183400, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738313031323034303b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:c8ebbe96a49f3abf9c507a2db93615d0', '127.0.0.1', 20260608141737, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303931303235373b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:c954a45fa01e276d9eae9c87b0a2b827', '::1', 20260602171212, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303430323333323b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:cc68d4c3dbde56eebaf440a79b094c34', '127.0.0.1', 20260601184953, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303332313739333b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b5f63695f70726576696f75735f75726c7c733a3134353a22687474703a2f2f6c6f63616c686f73743a383838382f6170692f696e6465782e7068702f7075626c69632f626f6f6b732f33656433393865372d353931612d346562302d623561342d6532363433376362323765302f6d696e6973686f702f73616c65732f34613031306537652d353039312d343061642d393961322d3162366630373666356337622f72656365697074223b),
('ci_session:cd93a546fffe5deaabd974162ba41509', '::1', 20260606123205, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303733313132353b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:cde39d51f049123a71c9e95d6a7c7ca7', '::1', 20260606151657, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303734313031373b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:ce0e1087613d91d20a70f89207ccb368', '127.0.0.1', 20260601135208, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303330333932383b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:d2def8d97dd1a359d3403b0ea95e93f8', '::1', 20260608164943, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303931393338333b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:d459be810a04254e586a79188b0626f8', '::1', 20260604121539, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303535373333393b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:d4960989e33238b4f2ea4a2080bafc05', '::1', 20260604185415, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303538313235353b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:d70986702a9e1e9e3c6a0da0212b9665', '::1', 20260606153750, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303734323237303b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:d75d40412dda2199c67f86f4e5d77bcd', '::1', 20260604120850, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303535363933303b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:db00acdfe9b4ddb4399463af3b72c6bd', '::1', 20260604145350, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303536363833303b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:dbbafe3dce5832f3f3aeae9da695fd65', '::1', 20260606173553, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303734393335333b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:dbc96ec953849aa1bb507b5a8edd6f65', '::1', 20260606140835, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303733363931353b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:dbcef888e56c047f9010f45e47670ad3', '::1', 20260602161306, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303339383738363b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:de5850c2b68878be072dd6fae50a2d44', '::1', 20260608165055, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303931393338333b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:e122e07b1d640d0ae139bfaab017a672', '::1', 20260604191147, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303538323130303b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:e1d87d569ed77f545a4d63bcbd2139b0', '::1', 20260606151114, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303734303637343b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:e1e0500e7c61d5821b0d169f3a30ffd4', '::1', 20260606164836, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303734363531363b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:e25453985c809d8643c0a528530125ba', '::1', 20260604164805, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303537333638353b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:e352adb84f42d97e30c5a555eeab92df', '::1', 20260606183936, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303735333137363b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:e4fedbc31f5ff681dcad28e3541ad0b1', '::1', 20260604123957, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303535383739373b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:e640b81e79d2000c572f8f96a9d9cacd', '::1', 20260601191144, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303332333130343b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b5f63695f70726576696f75735f75726c7c733a3134353a22687474703a2f2f6c6f63616c686f73743a383838382f6170692f696e6465782e7068702f7075626c69632f626f6f6b732f33656433393865372d353931612d346562302d623561342d6532363433376362323765302f6d696e6973686f702f73616c65732f34613031306537652d353039312d343061642d393961322d3162366630373666356337622f72656365697074223b),
('ci_session:e7ed2d138aeb8e5072c71bbce079f6b0', '::1', 20260606165337, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303734363831373b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:e9d1a0db570011f2f4305e07027735b2', '::1', 20260606134638, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303733353539383b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:f330fd6164424a20c4caec9f2ce77b17', '::1', 20260604173021, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303537363232313b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:f4613464e1b25bb997de0f8b07a6e658', '::1', 20260606175841, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303735303732313b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:f655e325ba245148628169d45fa3e7a5', '::1', 20260606135805, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303733363238353b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:f6a65259722311018f51cc15df8f85d9', '::1', 20260601180715, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303331393233353b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b5f63695f70726576696f75735f75726c7c733a3134353a22687474703a2f2f6c6f63616c686f73743a383838382f6170692f696e6465782e7068702f7075626c69632f626f6f6b732f33656433393865372d353931612d346562302d623561342d6532363433376362323765302f6d696e6973686f702f73616c65732f32633037393563652d656564612d343036612d616163362d3438383639383962333935302f72656365697074223b),
('ci_session:f72c92c3131ace46055077bade414bcd', '127.0.0.1', 20260606122701, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303733303832313b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:f7aad5798a34bb17b00259eb1a650370', '::1', 20260604144242, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303536363136323b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:f90de3925b4d9e7071ff02a7563bae0f', '::1', 20260604114054, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303535353235343b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:fd76887f90bbe8034ce64bc77d980f3f', '::1', 20260601162246, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303331323936363b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b5f63695f70726576696f75735f75726c7c733a38383a22687474703a2f2f6c6f63616c686f73743a383838382f6170692f696e6465782e7068702f7075626c69632f72656365697074732f32633037393563652d656564612d343036612d616163362d343838363938396233393530223b),
('ci_session:ff1f2c2a7181968ba4ccaa7f18854ddc', '::1', 20260602171900, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303430323732373b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b),
('ci_session:ffe43530a6acf9365c0a2d310337238f', '::1', 20260604144810, 0x5f5f63695f6c6173745f726567656e65726174657c693a313738303536363439303b757365725f69647c733a33363a2231313131313131312d313131312d313131312d313131312d313131313131313131313131223b);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `default_book_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timezone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locale` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `status` enum('active','blocked') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `last_login_at` datetime DEFAULT NULL,
  `plan` enum('free','monthly','yearly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'free',
  `license_expires_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `default_book_id`, `name`, `email`, `phone`, `country_name`, `country_code`, `city`, `timezone`, `date_of_birth`, `email_verified_at`, `password_hash`, `google_id`, `avatar`, `locale`, `status`, `last_login_at`, `plan`, `license_expires_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
('11111111-1111-1111-1111-111111111111', 'aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1', 'Muslimbek VD', 'vosidiy@gmail.com', '+998946875461', 'Uzbekistan', 'UZ', 'Tashkent', 'Asia/Tashkent', NULL, '2026-05-11 20:52:13', '$2y$10$IeDce2Wh1snpcmmg3ZdpPeKC9vwbQcDZBkvmBR/MiN0vWC2xKPoUy', NULL, NULL, 'en', 'active', '2026-06-09 13:17:25', 'free', NULL, '2026-05-11 20:52:13', '2026-06-09 18:17:25', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_email_verifications`
--

CREATE TABLE `user_email_verifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `app_finance_categories`
--
ALTER TABLE `app_finance_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_finance_categories_book_id` (`book_id`),
  ADD KEY `idx_finance_categories_book_type` (`book_id`,`type`),
  ADD KEY `idx_finance_categories_created_by` (`created_by`);

--
-- Indexes for table `app_finance_transactions`
--
ALTER TABLE `app_finance_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_finance_transactions_book_id` (`book_id`),
  ADD KEY `idx_finance_transactions_category_id` (`category_id`),
  ADD KEY `idx_finance_transactions_book_date` (`book_id`,`transaction_date`),
  ADD KEY `idx_finance_transactions_book_type` (`book_id`,`type`),
  ADD KEY `idx_finance_transactions_created_by` (`created_by`);

--
-- Indexes for table `app_minishop_categories`
--
ALTER TABLE `app_minishop_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_minishop_categories_book_id` (`book_id`),
  ADD KEY `idx_minishop_categories_created_by` (`created_by`),
  ADD KEY `idx_minishop_categories_book_deleted` (`book_id`,`deleted_at`),
  ADD KEY `idx_minishop_categories_book_sort` (`book_id`,`sort_order`);

--
-- Indexes for table `app_minishop_customers`
--
ALTER TABLE `app_minishop_customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_minishop_customers_book_id` (`book_id`),
  ADD KEY `idx_minishop_customers_created_by` (`created_by`),
  ADD KEY `idx_minishop_customers_book_deleted` (`book_id`,`deleted_at`),
  ADD KEY `idx_minishop_customers_book_reminder` (`book_id`,`reminder_at`),
  ADD KEY `idx_minishop_customers_book_phone` (`book_id`,`phone`);

--
-- Indexes for table `app_minishop_products`
--
ALTER TABLE `app_minishop_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_minishop_products_book_id` (`book_id`),
  ADD KEY `idx_minishop_products_created_by` (`created_by`),
  ADD KEY `idx_minishop_products_category_id` (`category_id`),
  ADD KEY `idx_minishop_products_book_category` (`book_id`,`category_id`),
  ADD KEY `idx_minishop_products_book_active` (`book_id`,`is_active`),
  ADD KEY `idx_minishop_products_book_deleted` (`book_id`,`deleted_at`),
  ADD KEY `idx_minishop_products_book_quantity` (`book_id`,`quantity`);

--
-- Indexes for table `app_minishop_sales`
--
ALTER TABLE `app_minishop_sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_minishop_sales_book_id` (`book_id`),
  ADD KEY `idx_minishop_sales_created_by` (`created_by`),
  ADD KEY `idx_minishop_sales_customer_id` (`customer_id`),
  ADD KEY `idx_minishop_sales_book_sold_at` (`book_id`,`sold_at`),
  ADD KEY `idx_minishop_sales_book_customer` (`book_id`,`customer_id`),
  ADD KEY `idx_minishop_sales_book_payment_status` (`book_id`,`payment_status`),
  ADD KEY `idx_minishop_sales_book_deleted` (`book_id`,`deleted_at`);

--
-- Indexes for table `app_minishop_sale_items`
--
ALTER TABLE `app_minishop_sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_minishop_sale_items_sale_id` (`sale_id`),
  ADD KEY `idx_minishop_sale_items_product_id` (`product_id`);

--
-- Indexes for table `app_minishop_sale_payments`
--
ALTER TABLE `app_minishop_sale_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_minishop_sale_payments_sale_id` (`sale_id`),
  ADD KEY `idx_minishop_sale_payments_created_by` (`created_by`),
  ADD KEY `idx_minishop_sale_payments_paid_at` (`paid_at`);

--
-- Indexes for table `app_notes`
--
ALTER TABLE `app_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notes_book_id` (`book_id`),
  ADD KEY `idx_notes_book_position` (`book_id`,`position`),
  ADD KEY `idx_notes_book_pinned` (`book_id`,`is_pinned`),
  ADD KEY `idx_notes_created_by` (`created_by`),
  ADD KEY `idx_notes_book_archived` (`book_id`,`is_archived`);

--
-- Indexes for table `app_service_customers`
--
ALTER TABLE `app_service_customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_service_customers_book_id` (`book_id`),
  ADD KEY `idx_service_customers_created_by` (`created_by`),
  ADD KEY `idx_service_customers_book_deleted` (`book_id`,`deleted_at`),
  ADD KEY `idx_service_customers_book_phone` (`book_id`,`phone`);

--
-- Indexes for table `app_service_orders`
--
ALTER TABLE `app_service_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_service_orders_book_id` (`book_id`),
  ADD KEY `idx_service_orders_created_by` (`created_by`),
  ADD KEY `idx_service_orders_customer_id` (`customer_id`),
  ADD KEY `idx_service_orders_book_received_at` (`book_id`,`received_at`),
  ADD KEY `idx_service_orders_book_customer` (`book_id`,`customer_id`),
  ADD KEY `idx_service_orders_book_order_status` (`book_id`,`order_status`),
  ADD KEY `idx_service_orders_book_deleted` (`book_id`,`deleted_at`);

--
-- Indexes for table `app_service_order_items`
--
ALTER TABLE `app_service_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_service_order_items_order_id` (`order_id`),
  ADD KEY `idx_service_order_items_service_type_id` (`service_type_id`),
  ADD KEY `idx_service_order_items_order_sort` (`order_id`,`sort_order`),
  ADD KEY `idx_service_order_items_object_name` (`object_name`);

--
-- Indexes for table `app_service_types`
--
ALTER TABLE `app_service_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_service_types_book_id` (`book_id`),
  ADD KEY `idx_service_types_created_by` (`created_by`),
  ADD KEY `idx_service_types_book_active` (`book_id`,`is_active`),
  ADD KEY `idx_service_types_book_sort` (`book_id`,`sort_order`),
  ADD KEY `idx_service_types_book_deleted` (`book_id`,`deleted_at`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_books_user_id` (`user_id`),
  ADD KEY `idx_books_type_key` (`type_key`),
  ADD KEY `idx_books_user_archived` (`user_id`,`is_archived`),
  ADD KEY `idx_books_currency_code` (`currency_code`);

--
-- Indexes for table `book_types`
--
ALTER TABLE `book_types`
  ADD PRIMARY KEY (`type_key`);

--
-- Indexes for table `ci_sessions`
--
ALTER TABLE `ci_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ci_sessions_timestamp` (`timestamp`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_password_reset_tokens_user_id` (`user_id`),
  ADD KEY `idx_password_reset_tokens_expires_at` (`expires_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_phone` (`phone`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `idx_users_default_book_id` (`default_book_id`),
  ADD KEY `idx_users_google_id` (`google_id`),
  ADD KEY `idx_users_plan` (`plan`);

--
-- Indexes for table `user_email_verifications`
--
ALTER TABLE `user_email_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_email_verifications_user_id` (`user_id`),
  ADD KEY `idx_user_email_verifications_expires_at` (`expires_at`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `app_finance_categories`
--
ALTER TABLE `app_finance_categories`
  ADD CONSTRAINT `fk_finance_categories_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_finance_categories_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `app_finance_transactions`
--
ALTER TABLE `app_finance_transactions`
  ADD CONSTRAINT `fk_finance_transactions_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_finance_transactions_category` FOREIGN KEY (`category_id`) REFERENCES `app_finance_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_finance_transactions_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `app_minishop_categories`
--
ALTER TABLE `app_minishop_categories`
  ADD CONSTRAINT `fk_minishop_categories_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_minishop_categories_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `app_minishop_customers`
--
ALTER TABLE `app_minishop_customers`
  ADD CONSTRAINT `fk_minishop_customers_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_minishop_customers_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `app_minishop_products`
--
ALTER TABLE `app_minishop_products`
  ADD CONSTRAINT `fk_minishop_products_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_minishop_products_category` FOREIGN KEY (`category_id`) REFERENCES `app_minishop_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_minishop_products_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `app_minishop_sales`
--
ALTER TABLE `app_minishop_sales`
  ADD CONSTRAINT `fk_minishop_sales_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_minishop_sales_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_minishop_sales_customer` FOREIGN KEY (`customer_id`) REFERENCES `app_minishop_customers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `app_minishop_sale_items`
--
ALTER TABLE `app_minishop_sale_items`
  ADD CONSTRAINT `fk_minishop_sale_items_product` FOREIGN KEY (`product_id`) REFERENCES `app_minishop_products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_minishop_sale_items_sale` FOREIGN KEY (`sale_id`) REFERENCES `app_minishop_sales` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `app_minishop_sale_payments`
--
ALTER TABLE `app_minishop_sale_payments`
  ADD CONSTRAINT `fk_minishop_sale_payments_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_minishop_sale_payments_sale` FOREIGN KEY (`sale_id`) REFERENCES `app_minishop_sales` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `app_notes`
--
ALTER TABLE `app_notes`
  ADD CONSTRAINT `fk_notes_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notes_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `app_service_customers`
--
ALTER TABLE `app_service_customers`
  ADD CONSTRAINT `fk_service_customers_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_service_customers_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `app_service_orders`
--
ALTER TABLE `app_service_orders`
  ADD CONSTRAINT `fk_service_orders_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_service_orders_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_service_orders_customer` FOREIGN KEY (`customer_id`) REFERENCES `app_service_customers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `app_service_order_items`
--
ALTER TABLE `app_service_order_items`
  ADD CONSTRAINT `fk_service_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `app_service_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_service_order_items_service_type` FOREIGN KEY (`service_type_id`) REFERENCES `app_service_types` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `app_service_types`
--
ALTER TABLE `app_service_types`
  ADD CONSTRAINT `fk_service_types_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_service_types_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `fk_books_type` FOREIGN KEY (`type_key`) REFERENCES `book_types` (`type_key`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_books_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `fk_password_reset_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_default_book` FOREIGN KEY (`default_book_id`) REFERENCES `books` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_email_verifications`
--
ALTER TABLE `user_email_verifications`
  ADD CONSTRAINT `fk_user_email_verifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
