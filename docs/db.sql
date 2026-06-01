-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Jun 01, 2026 at 08:13 AM
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

INSERT INTO `books` (`id`, `user_id`, `type_key`, `currency_code`, `title`, `description`, `icon`, `color`, `settings_json`, `is_archived`, `sort_order`, `last_opened_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
('096ca5af-d877-4323-8cac-a908efdf145c', '11111111-1111-1111-1111-111111111111', 'todo', NULL, 'asdasd', 'asdasd', NULL, NULL, NULL, 0, 5, NULL, '2026-05-13 18:29:34', '2026-05-17 15:50:03', '2026-05-17 10:50:03'),
('123', '11111111-1111-1111-1111-111111111111', 'finance', 'UZS', 'Birinchi kitob', 'Yaxshi kitobvha haqida', NULL, NULL, NULL, 1, 3, NULL, '2026-05-13 12:41:35', '2026-05-28 19:55:35', NULL),
('34c416bd-5631-4394-84bc-2e05e436351d', '11111111-1111-1111-1111-111111111111', 'minishop', 'UZS', 'Dukon', NULL, NULL, NULL, NULL, 1, 8, NULL, '2026-05-15 20:21:34', '2026-05-28 16:43:29', NULL),
('3ed398e7-591a-4eb0-b5a4-e26437cb27e0', '11111111-1111-1111-1111-111111111111', 'minishop', 'UZS', 'Super Kitobcha', NULL, NULL, NULL, NULL, 0, 9, NULL, '2026-05-28 19:08:59', '2026-05-28 19:08:59', NULL),
('57104b19-df50-48c7-ac2f-89bf87dde244', '11111111-1111-1111-1111-111111111111', 'finance', 'UZS', 'moliya', NULL, NULL, NULL, '{\"money_display\": {\"show_cents\": true, \"thousand_separator\": \"comma\"}}', 0, 10, NULL, '2026-05-29 17:56:55', '2026-05-29 17:56:55', NULL),
('aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1', '11111111-1111-1111-1111-111111111111', 'notes', NULL, 'Daily Notes', 'Personal notes book for Ali', NULL, NULL, NULL, 0, 1, NULL, '2026-05-11 20:52:13', '2026-05-28 16:43:52', NULL),
('aaaaaaa2-aaaa-aaaa-aaaa-aaaaaaaaaaa2', '11111111-1111-1111-1111-111111111111', 'todo', NULL, 'Personal Tasks', 'Task management book for Ali', NULL, NULL, NULL, 0, 2, NULL, '2026-05-11 20:52:13', '2026-05-18 15:39:52', '2026-05-18 10:39:52'),
('b00eb1d2-4403-446d-88bb-b00f9e6ad8d6', '11111111-1111-1111-1111-111111111111', 'minishop', 'UZS', 'Do\'konim \'', 'sotuvlarim', NULL, NULL, '{\"money_display\": {\"show_cents\": false, \"thousand_separator\": \"space\"}}', 0, 7, NULL, '2026-05-15 16:30:28', '2026-05-29 18:54:34', NULL),
('bcc06a2a-f691-460a-9a35-5ef871a8c56b', '11111111-1111-1111-1111-111111111111', 'notes', NULL, 'Salom', 'asdas', NULL, NULL, NULL, 1, 6, NULL, '2026-05-13 19:01:30', '2026-05-28 20:00:09', NULL),
('c95e3e81-f172-4a9d-b854-962d4b12031f', '11111111-1111-1111-1111-111111111111', 'finance', 'UZS', 'Salom kitob', 'Asasdashdbas', NULL, NULL, NULL, 0, 4, NULL, '2026-05-13 18:29:07', '2026-05-26 15:46:08', '2026-05-18 10:39:48'),
('f4c58109-cce8-4092-9c78-c27f2ec2858f', '11111111-1111-1111-1111-111111111111', 'minishop', 'UZS', 'sd', NULL, NULL, NULL, NULL, 1, 9, NULL, '2026-05-15 20:28:45', '2026-05-28 16:36:04', '2026-05-28 11:36:04');

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
('todo', 'Todo', 'Book type for task management', 0, 0, '2026-05-11 20:01:14', '2026-06-01 13:13:32');

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

-- --------------------------------------------------------

--
-- Table structure for table `finance_categories`
--

CREATE TABLE `finance_categories` (
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
-- Table structure for table `finance_transactions`
--

CREATE TABLE `finance_transactions` (
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
-- Table structure for table `minishop_categories`
--

CREATE TABLE `minishop_categories` (
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
-- Table structure for table `minishop_customers`
--

CREATE TABLE `minishop_customers` (
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

-- --------------------------------------------------------

--
-- Table structure for table `minishop_products`
--

CREATE TABLE `minishop_products` (
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

-- --------------------------------------------------------

--
-- Table structure for table `minishop_sales`
--

CREATE TABLE `minishop_sales` (
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

-- --------------------------------------------------------

--
-- Table structure for table `minishop_sale_items`
--

CREATE TABLE `minishop_sale_items` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sale_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_sku` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` decimal(10,3) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `line_total` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `minishop_sale_payments`
--

CREATE TABLE `minishop_sale_payments` (
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

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
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
-- Table structure for table `todos`
--

CREATE TABLE `todos` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `book_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `priority` enum('low','medium','high') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `is_completed` tinyint(1) NOT NULL DEFAULT '0',
  `due_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `position` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL
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
('11111111-1111-1111-1111-111111111111', 'aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1', 'Muslimbek VD', 'vosidiy@gmail.com', '+998946875461', 'Uzbekistan', 'UZ', 'Tashkent', 'Asia/Tashkent', NULL, '2026-05-11 20:52:13', '$2y$10$IeDce2Wh1snpcmmg3ZdpPeKC9vwbQcDZBkvmBR/MiN0vWC2xKPoUy', NULL, NULL, 'en', 'active', '2026-06-01 06:56:53', 'free', NULL, '2026-05-11 20:52:13', '2026-06-01 11:56:53', NULL);

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
-- Indexes for table `finance_categories`
--
ALTER TABLE `finance_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_finance_categories_book_id` (`book_id`),
  ADD KEY `idx_finance_categories_book_type` (`book_id`,`type`),
  ADD KEY `idx_finance_categories_created_by` (`created_by`);

--
-- Indexes for table `finance_transactions`
--
ALTER TABLE `finance_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_finance_transactions_book_id` (`book_id`),
  ADD KEY `idx_finance_transactions_category_id` (`category_id`),
  ADD KEY `idx_finance_transactions_book_date` (`book_id`,`transaction_date`),
  ADD KEY `idx_finance_transactions_book_type` (`book_id`,`type`),
  ADD KEY `idx_finance_transactions_created_by` (`created_by`);

--
-- Indexes for table `minishop_categories`
--
ALTER TABLE `minishop_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_minishop_categories_book_id` (`book_id`),
  ADD KEY `idx_minishop_categories_created_by` (`created_by`),
  ADD KEY `idx_minishop_categories_book_deleted` (`book_id`,`deleted_at`),
  ADD KEY `idx_minishop_categories_book_sort` (`book_id`,`sort_order`);

--
-- Indexes for table `minishop_customers`
--
ALTER TABLE `minishop_customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_minishop_customers_book_id` (`book_id`),
  ADD KEY `idx_minishop_customers_created_by` (`created_by`),
  ADD KEY `idx_minishop_customers_book_deleted` (`book_id`,`deleted_at`),
  ADD KEY `idx_minishop_customers_book_reminder` (`book_id`,`reminder_at`),
  ADD KEY `idx_minishop_customers_book_phone` (`book_id`,`phone`);

--
-- Indexes for table `minishop_products`
--
ALTER TABLE `minishop_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_minishop_products_book_id` (`book_id`),
  ADD KEY `idx_minishop_products_created_by` (`created_by`),
  ADD KEY `idx_minishop_products_category_id` (`category_id`),
  ADD KEY `idx_minishop_products_book_category` (`book_id`,`category_id`),
  ADD KEY `idx_minishop_products_book_active` (`book_id`,`is_active`),
  ADD KEY `idx_minishop_products_book_deleted` (`book_id`,`deleted_at`),
  ADD KEY `idx_minishop_products_book_quantity` (`book_id`,`quantity`);

--
-- Indexes for table `minishop_sales`
--
ALTER TABLE `minishop_sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_minishop_sales_book_id` (`book_id`),
  ADD KEY `idx_minishop_sales_created_by` (`created_by`),
  ADD KEY `idx_minishop_sales_customer_id` (`customer_id`),
  ADD KEY `idx_minishop_sales_book_sold_at` (`book_id`,`sold_at`),
  ADD KEY `idx_minishop_sales_book_customer` (`book_id`,`customer_id`),
  ADD KEY `idx_minishop_sales_book_payment_status` (`book_id`,`payment_status`),
  ADD KEY `idx_minishop_sales_book_deleted` (`book_id`,`deleted_at`);

--
-- Indexes for table `minishop_sale_items`
--
ALTER TABLE `minishop_sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_minishop_sale_items_sale_id` (`sale_id`),
  ADD KEY `idx_minishop_sale_items_product_id` (`product_id`);

--
-- Indexes for table `minishop_sale_payments`
--
ALTER TABLE `minishop_sale_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_minishop_sale_payments_sale_id` (`sale_id`),
  ADD KEY `idx_minishop_sale_payments_created_by` (`created_by`),
  ADD KEY `idx_minishop_sale_payments_paid_at` (`paid_at`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notes_book_id` (`book_id`),
  ADD KEY `idx_notes_book_position` (`book_id`,`position`),
  ADD KEY `idx_notes_book_pinned` (`book_id`,`is_pinned`),
  ADD KEY `idx_notes_created_by` (`created_by`),
  ADD KEY `idx_notes_book_archived` (`book_id`,`is_archived`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_password_reset_tokens_user_id` (`user_id`),
  ADD KEY `idx_password_reset_tokens_expires_at` (`expires_at`);

--
-- Indexes for table `todos`
--
ALTER TABLE `todos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_todos_book_id` (`book_id`),
  ADD KEY `idx_todos_parent_id` (`parent_id`),
  ADD KEY `idx_todos_book_completed` (`book_id`,`is_completed`),
  ADD KEY `idx_todos_book_due_at` (`book_id`,`due_at`),
  ADD KEY `idx_todos_created_by` (`created_by`);

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
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `fk_books_type` FOREIGN KEY (`type_key`) REFERENCES `book_types` (`type_key`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_books_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `finance_categories`
--
ALTER TABLE `finance_categories`
  ADD CONSTRAINT `fk_finance_categories_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_finance_categories_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `finance_transactions`
--
ALTER TABLE `finance_transactions`
  ADD CONSTRAINT `fk_finance_transactions_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_finance_transactions_category` FOREIGN KEY (`category_id`) REFERENCES `finance_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_finance_transactions_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `minishop_categories`
--
ALTER TABLE `minishop_categories`
  ADD CONSTRAINT `fk_minishop_categories_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_minishop_categories_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `minishop_customers`
--
ALTER TABLE `minishop_customers`
  ADD CONSTRAINT `fk_minishop_customers_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_minishop_customers_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `minishop_products`
--
ALTER TABLE `minishop_products`
  ADD CONSTRAINT `fk_minishop_products_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_minishop_products_category` FOREIGN KEY (`category_id`) REFERENCES `minishop_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_minishop_products_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `minishop_sales`
--
ALTER TABLE `minishop_sales`
  ADD CONSTRAINT `fk_minishop_sales_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_minishop_sales_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_minishop_sales_customer` FOREIGN KEY (`customer_id`) REFERENCES `minishop_customers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `minishop_sale_items`
--
ALTER TABLE `minishop_sale_items`
  ADD CONSTRAINT `fk_minishop_sale_items_product` FOREIGN KEY (`product_id`) REFERENCES `minishop_products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_minishop_sale_items_sale` FOREIGN KEY (`sale_id`) REFERENCES `minishop_sales` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `minishop_sale_payments`
--
ALTER TABLE `minishop_sale_payments`
  ADD CONSTRAINT `fk_minishop_sale_payments_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_minishop_sale_payments_sale` FOREIGN KEY (`sale_id`) REFERENCES `minishop_sales` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `fk_notes_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notes_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `fk_password_reset_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `todos`
--
ALTER TABLE `todos`
  ADD CONSTRAINT `fk_todos_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_todos_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_todos_parent` FOREIGN KEY (`parent_id`) REFERENCES `todos` (`id`) ON DELETE SET NULL;

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
