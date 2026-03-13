-- ===========================================
-- Full Database Schema for Costing System
-- Dharma Electrindo Mfg
-- ===========================================
-- Import this file in phpMyAdmin SQL tab

-- Create database
CREATE DATABASE IF NOT EXISTS `costing_manufaktur` 
    DEFAULT CHARACTER SET utf8mb4 
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE `costing_manufaktur`;

-- ===========================================
-- Users Table (Laravel Default)
-- ===========================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- Password Reset Tokens Table
-- ===========================================
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- Sessions Table
-- ===========================================
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- Cache Table
-- ===========================================
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- Jobs Table (for Laravel Queue)
-- ===========================================
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- Migrations Table
-- ===========================================
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- Products Table
-- ===========================================
CREATE TABLE IF NOT EXISTS `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `line` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- Customers Table
-- ===========================================
CREATE TABLE IF NOT EXISTS `customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- Materials Table
-- ===========================================
CREATE TABLE IF NOT EXISTS `materials` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `part_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `part_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PCS',
  `pro_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supplier_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `materials_part_no_unique` (`part_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- Costing Data Table (Main Table)
-- ===========================================
CREATE TABLE IF NOT EXISTS `costing_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `period` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g., 2025-01',
  `wo_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  
  -- Exchange rates
  `exchange_rate_usd` decimal(15,2) NOT NULL DEFAULT '15500.00',
  `exchange_rate_jpy` decimal(15,2) NOT NULL DEFAULT '103.00',
  `lme_rate` decimal(15,2) DEFAULT NULL,
  
  -- Production parameters
  `forecast` int NOT NULL DEFAULT '0',
  `project_period` int NOT NULL DEFAULT '12',
  
  -- Actual costs
  `material_cost` decimal(20,2) NOT NULL DEFAULT '0.00',
  `labor_cost` decimal(20,2) NOT NULL DEFAULT '0.00',
  `overhead_cost` decimal(20,2) NOT NULL DEFAULT '0.00',
  `scrap_cost` decimal(20,2) NOT NULL DEFAULT '0.00',
  `revenue` decimal(20,2) NOT NULL DEFAULT '0.00',
  `qty_good` int NOT NULL DEFAULT '0',
  
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `costing_data_product_id_foreign` (`product_id`),
  KEY `costing_data_customer_id_foreign` (`customer_id`),
  CONSTRAINT `costing_data_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `costing_data_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- Material Breakdowns Table
-- ===========================================
CREATE TABLE IF NOT EXISTS `material_breakdowns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `costing_data_id` bigint unsigned NOT NULL,
  `material_id` bigint unsigned NOT NULL,
  
  `qty_req` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `amount1` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `unit_price_basis` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'IDR' COMMENT 'IDR, USD, JPY',
  `qty_moq` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `cn_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'C or N',
  `import_tax_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `amount2` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `currency2` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'IDR',
  `unit_price2` decimal(15,4) NOT NULL DEFAULT '0.0000',
  
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `material_breakdowns_costing_data_id_foreign` (`costing_data_id`),
  KEY `material_breakdowns_material_id_foreign` (`material_id`),
  CONSTRAINT `material_breakdowns_costing_data_id_foreign` FOREIGN KEY (`costing_data_id`) REFERENCES `costing_data` (`id`) ON DELETE CASCADE,
  CONSTRAINT `material_breakdowns_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- Insert Initial Migration Records
-- ===========================================
INSERT INTO `migrations` (`migration`, `batch`) VALUES
('0001_01_01_000000_create_users_table', 1),
('0001_01_01_000001_create_cache_table', 1),
('0001_01_01_000002_create_jobs_table', 1),
('2026_01_19_000001_create_products_table', 1),
('2026_01_19_000002_create_customers_table', 1),
('2026_01_19_000003_create_materials_table', 1),
('2026_01_19_000004_create_costing_data_table', 1),
('2026_01_19_000005_create_material_breakdowns_table', 1);

-- ===========================================
-- Sample Data (Optional)
-- ===========================================

-- Sample Products
INSERT INTO `products` (`code`, `name`, `line`, `created_at`, `updated_at`) VALUES
('PRD-001', 'Transformer 50KVA', 'LINE-A', NOW(), NOW()),
('PRD-002', 'Transformer 100KVA', 'LINE-A', NOW(), NOW()),
('PRD-003', 'Panel Box MDP', 'LINE-B', NOW(), NOW());

-- Sample Customers
INSERT INTO `customers` (`code`, `name`, `created_at`, `updated_at`) VALUES
('CUST-001', 'PT. Sumber Energi Listrik', NOW(), NOW()),
('CUST-002', 'PT. Indo Electric Power', NOW(), NOW()),
('CUST-003', 'PT. Mega Jaya Elektrik', NOW(), NOW());

-- Sample Materials
INSERT INTO `materials` (`part_no`, `id_code`, `part_name`, `unit`, `pro_code`, `supplier_name`, `created_at`, `updated_at`) VALUES
('MAT-001', 'CU-001', 'Copper Wire 2.5mm', 'KG', 'PRO-001', 'PT. Tembaga Indonesia', NOW(), NOW()),
('MAT-002', 'AL-001', 'Aluminium Sheet 2mm', 'KG', 'PRO-002', 'PT. Alumindo Jaya', NOW(), NOW()),
('MAT-003', 'ST-001', 'Steel Plate 3mm', 'KG', 'PRO-003', 'PT. Krakatau Steel', NOW(), NOW()),
('MAT-004', 'INS-001', 'Insulation Paper', 'M', 'PRO-004', 'PT. Kertas Leces', NOW(), NOW()),
('MAT-005', 'OIL-001', 'Transformer Oil', 'LTR', 'PRO-005', 'PT. Pertamina Lubricants', NOW(), NOW());

-- ===========================================
-- Done! Database is ready
-- ===========================================
