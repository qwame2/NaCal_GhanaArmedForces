-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 03, 2026 at 09:56 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nacal_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-setting_organization_name', 's:5:\"NACOC\";', 1780559725);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `edit_requests`
--

CREATE TABLE `edit_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `item_type` varchar(255) NOT NULL,
  `item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `request_type` varchar(255) NOT NULL DEFAULT 'edit',
  `reason` text NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `payload` longtext DEFAULT NULL,
  `original_payload` text DEFAULT NULL COMMENT 'JSON: original values before edits were made',
  `rollback_fields` text DEFAULT NULL COMMENT 'JSON: admin-flagged fields and correction notes for rollback',
  `approved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_batches`
--

CREATE TABLE `inventory_batches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ledge_category` varchar(255) NOT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `supplier_status` varchar(255) DEFAULT NULL,
  `donor_name` varchar(255) DEFAULT NULL,
  `acquisition_type` varchar(255) NOT NULL DEFAULT 'Supplier',
  `entry_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `arrival_date` datetime DEFAULT NULL,
  `recorded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `approval_status` varchar(255) NOT NULL DEFAULT 'approved',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `batch_id` bigint(20) UNSIGNED NOT NULL,
  `description` varchar(255) NOT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `stock_balance` varchar(255) NOT NULL DEFAULT '0',
  `qty` varchar(255) DEFAULT NULL,
  `variance` varchar(255) NOT NULL DEFAULT '0',
  `remarks` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `issuances`
--

CREATE TABLE `issuances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `issuance_date` date NOT NULL,
  `beneficiary` varchar(255) NOT NULL,
  `authority` varchar(255) DEFAULT NULL,
  `issuance_type` varchar(255) NOT NULL DEFAULT 'Permanent',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `issued_items`
--

CREATE TABLE `issued_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `issuance_id` bigint(20) UNSIGNED NOT NULL,
  `description` varchar(255) NOT NULL,
  `ledge_category` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED NOT NULL,
  `receiver_id` bigint(20) UNSIGNED NOT NULL,
  `message` text DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `attachment_name` varchar(255) DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_automated` tinyint(1) NOT NULL DEFAULT 0,
  `edit_request_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_04_10_142009_create_inventory_batches_table', 1),
(5, '2026_04_10_142011_create_inventory_items_table', 1),
(6, '2026_04_10_150102_change_balance_columns_to_string_in_inventory_items_table', 1),
(7, '2026_04_10_151200_add_remarks_to_inventory_items_table', 1),
(8, '2026_04_11_161800_add_qty_to_inventory_items_table', 1),
(9, '2026_04_11_212500_drop_folio_from_inventory_items_table', 1),
(10, '2026_04_11_212504_drop_folio_from_inventory_items_table', 1),
(11, '2026_04_13_002800_add_donor_to_inventory_batches_table', 1),
(12, '2026_04_13_003400_make_supplier_name_nullable_in_inventory_batches_table', 1),
(13, '2026_04_15_224127_create_issuances_table', 1),
(14, '2026_04_15_225000_add_missing_columns_to_issuances_table', 1),
(15, '2026_04_15_225500_create_missing_issued_items_table', 1),
(16, '2026_04_15_230500_add_issuance_type_to_issuances_table', 1),
(17, '2026_04_15_230700_drop_siv_no_from_issuances_table', 1),
(18, '2026_04_19_150000_add_username_to_users_table', 1),
(19, '2026_04_19_151000_make_email_nullable_in_users_table', 1),
(20, '2026_04_19_184130_add_avatar_to_users_table', 1),
(21, '2026_04_19_190000_add_professional_columns_to_users_table', 1),
(22, '2026_04_20_000000_add_two_factor_to_users_table', 1),
(23, '2026_04_20_000001_add_last_login_at_to_users_table', 1),
(24, '2026_04_20_000002_add_2fa_verification_columns_to_users_table', 1),
(25, '2026_04_22_223000_add_authority_to_issuances_table', 1),
(26, '2026_04_27_224500_drop_ledge_balance_from_inventory_items_table', 1),
(27, '2026_04_27_230600_add_arrival_date_to_inventory_batches_table', 1),
(28, '2026_04_29_162852_add_unit_to_inventory_items_table', 1),
(29, '2026_04_30_214200_add_is_admin_to_users_table', 1),
(30, '2026_04_30_223700_add_is_online_to_users_table', 1),
(31, '2026_05_01_000000_add_is_active_to_users_table', 1),
(32, '2026_05_01_210000_create_notification_acknowledgements_table', 1),
(33, '2026_05_01_213000_create_system_logs_table', 1),
(34, '2026_05_01_224000_add_last_logout_at_to_users_table', 1),
(35, '2026_05_03_001000_create_messages_table', 1),
(36, '2026_05_06_011700_create_edit_requests_table', 1),
(37, '2026_05_08_221800_add_request_type_to_edit_requests', 1),
(38, '2026_05_09_231743_add_supplier_status_to_inventory_batches_table', 1),
(39, '2026_05_10_000200_add_approval_columns_to_inventory_batches_table', 1),
(40, '2026_05_10_002100_add_payload_to_edit_requests_table', 1),
(41, '2026_05_10_002500_make_item_id_nullable_in_edit_requests_table', 1),
(42, '2026_05_10_003200_add_is_automated_to_messages_table', 1),
(43, '2026_05_10_003900_tag_legacy_automated_messages', 1),
(44, '2026_05_10_010600_add_edit_request_id_to_messages_table', 1),
(45, '2026_05_10_011230_add_recorded_by_to_inventory_batches', 1),
(46, '2026_05_10_222653_add_approved_at_to_edit_requests_table', 1),
(47, '2026_05_11_200000_create_settings_table', 1),
(48, '2026_05_11_201000_add_more_system_settings', 1),
(49, '2026_05_11_225000_add_permission_columns_to_users_table', 1),
(50, '2026_05_14_000000_remove_obsolete_security_settings', 1),
(51, '2026_05_14_005900_add_is_archived_to_messages_and_logs', 1),
(52, '2026_05_15_000000_add_must_change_password_to_users_table', 1),
(53, '2026_05_15_010000_create_password_reset_requests_table', 1),
(54, '2026_05_16_000000_create_returned_items_table', 1),
(55, '2026_05_16_221500_add_recovery_secret_to_users_table', 1),
(56, '2026_05_19_180000_add_location_to_inventory_items_table', 1),
(57, '2026_05_19_201500_create_suppliers_table', 1),
(58, '2026_05_20_000001_add_rollback_fields_to_edit_requests_table', 1),
(59, '2026_05_20_000002_tag_personnel_view_automated_messages', 1),
(60, '2026_05_20_000003_add_original_payload_to_edit_requests_table', 1),
(61, '2026_05_20_200000_create_store_requisitions_table', 1),
(62, '2026_05_22_220000_add_collection_to_store_requisitions_table', 1),
(63, '2026_05_23_100000_add_usage_type_to_store_requisitions_table', 1),
(64, '2026_05_23_102000_add_alternative_description_to_store_requisition_items_table', 1),
(65, '2026_05_23_103000_add_alternative_quantity_approved_to_store_requisition_items_table', 1),
(66, '2026_05_23_174100_add_decline_reason_to_store_requisitions_table', 1),
(67, '2026_05_24_193500_add_collector_details_to_store_requisitions_table', 1),
(68, '2026_05_26_120000_add_temp_account_columns_to_users_table', 1),
(69, '2026_05_27_103200_add_alternative_status_to_store_requisitions_table', 1),
(70, '2026_05_27_111500_create_receipts_table', 1),
(71, '2026_05_27_112000_add_signature_to_users_table', 1),
(72, '2026_05_30_170000_add_performance_indexes', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notification_acknowledgements`
--

CREATE TABLE `notification_acknowledgements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `item_description` varchar(255) NOT NULL,
  `alert_type` varchar(255) NOT NULL,
  `acknowledged_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_requests`
--

CREATE TABLE `password_reset_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `otp` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `requisition_id` bigint(20) UNSIGNED NOT NULL,
  `receipt_number` varchar(255) NOT NULL,
  `collector_name` varchar(255) NOT NULL,
  `collector_contact` varchar(255) NOT NULL,
  `collector_location` varchar(255) NOT NULL,
  `collected_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `issued_by` bigint(20) UNSIGNED NOT NULL,
  `approved_by_dept_head` varchar(255) DEFAULT NULL,
  `approved_by_stores_head` varchar(255) DEFAULT NULL,
  `items_json` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `returned_items`
--

CREATE TABLE `returned_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `issued_item_id` bigint(20) UNSIGNED NOT NULL,
  `returned_qty` int(11) NOT NULL,
  `return_date` date NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('CHkPCe3czgYKT7ZLP7VcFDERF1AXGp7GLRDgUuxC', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMFViWmZ0M3A0Q0dRdDFXVWlCSkhhT0JqZ040dWxJcUh4b2ZITUFYViI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozOToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FwaS9ub3RpZmljYXRpb25zIjt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvbm90aWZpY2F0aW9ucyI7czo1OiJyb3V0ZSI7czoxNzoiYXBpLm5vdGlmaWNhdGlvbnMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1780473399),
('Cjep6UolQmAIIxEdub75NaITiN30d36IQ3GwFUNN', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiU1FiYzg1UXRHckVPUU9PRklremJuU2FmRHZzcERSejdBdUdvdmxqSCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvbm90aWZpY2F0aW9ucyI7czo1OiJyb3V0ZSI7czoxNzoiYXBpLm5vdGlmaWNhdGlvbnMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjM6InVybCI7YToxOntzOjg6ImludGVuZGVkIjtzOjM5OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL25vdGlmaWNhdGlvbnMiO319', 1780473401),
('iFMGma7fJRRGBBwO5xVjvwFEYtBq8JdNGu1wAkr8', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUU9aenYxcmVmQ3c2WTZ4N0ZnaEV1cTZTUmlhYWFvOHBKYmdSbVNWSCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozOToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FwaS9ub3RpZmljYXRpb25zIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=', 1780473403),
('IrOZqM0ZlR9mGnCA4UeneuC2xvxjILPIZKyB4cL0', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:151.0) Gecko/20100101 Firefox/151.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoibG9YSm0xNzhJSGpRNEprcjBCalJqUUN6N2pDNUNqblZRbXY5N2FzdiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MzoidXJsIjthOjE6e3M6ODoiaW50ZW5kZWQiO3M6Mzg6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvdG90YWwtdW5yZWFkIjt9fQ==', 1780473403),
('upyi5F6Fvr3LvbM0DLfLArhO4yoZpM1RsmKZxRvl', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 OPR/131.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWTBXTnEzMUd4RkJIYUxUYjVMemRzRFV1ZEl1YUpyQXBYcXVsSzFCTSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozODoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FwaS90b3RhbC11bnJlYWQiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1780473404);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'string',
  `group` varchar(255) NOT NULL DEFAULT 'general',
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `type`, `group`, `description`, `created_at`, `updated_at`) VALUES
(1, 'low_stock_threshold', '100', 'integer', 'inventory', 'The minimum stock level before triggering a low stock alert.', '2026-06-03 07:55:20', '2026-06-03 07:55:20'),
(3, 'system_maintenance_mode', 'false', 'boolean', 'system', 'Restrict access to the system for maintenance.', '2026-06-03 07:55:20', '2026-06-03 07:55:20'),
(4, 'organization_name', 'NACOC', 'string', 'system', 'The official name of the organization displayed across the platform.', '2026-06-03 07:55:20', '2026-06-03 07:55:20'),
(5, 'support_email', 'support@nacoc.gov', 'string', 'system', 'The email address used for system support and automated notifications.', '2026-06-03 07:55:20', '2026-06-03 07:55:20'),
(7, 'max_login_attempts', '5', 'integer', 'security', 'Maximum allowed failed login attempts before an account is temporarily locked.', '2026-06-03 07:55:20', '2026-06-03 07:55:20'),
(8, 'default_pagination_limit', '15', 'integer', 'ui', 'The default number of rows to display in inventory and log tables.', '2026-06-03 07:55:20', '2026-06-03 07:55:20'),
(9, 'allow_personnel_registration', 'true', 'boolean', 'system', 'Allow new personnel to register accounts (requires admin approval later).', '2026-06-03 07:55:20', '2026-06-03 07:55:20'),
(10, 'stores_dept_head_approval_categories', '[]', 'json', 'inventory', 'Categories of items that require Department Head (Stores) approval before going to the Head of Stores.', '2026-06-03 07:55:22', '2026-06-03 07:55:22');

-- --------------------------------------------------------

--
-- Table structure for table `store_requisitions`
--

CREATE TABLE `store_requisitions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `requester_name` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `rank_or_title` varchar(255) DEFAULT NULL,
  `requested_by` bigint(20) UNSIGNED DEFAULT NULL,
  `purpose` text NOT NULL,
  `priority` enum('low','normal','urgent') NOT NULL DEFAULT 'normal',
  `status` enum('pending','approved','partially_approved','declined') NOT NULL DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `decline_reason` text DEFAULT NULL,
  `processed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `origin_approved_by` varchar(255) DEFAULT NULL,
  `stores_approved_by` varchar(255) DEFAULT NULL,
  `usage_type` varchar(255) NOT NULL DEFAULT 'permanent',
  `collected_at` timestamp NULL DEFAULT NULL,
  `collected_by` bigint(20) UNSIGNED DEFAULT NULL,
  `main_admin_status` varchar(255) NOT NULL DEFAULT 'pending',
  `origin_admin_status` varchar(255) NOT NULL DEFAULT 'pending',
  `alternative_status` varchar(255) DEFAULT NULL,
  `collector_name` varchar(255) DEFAULT NULL,
  `collector_contact` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `store_requisition_items`
--

CREATE TABLE `store_requisition_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `requisition_id` bigint(20) UNSIGNED NOT NULL,
  `description` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `unit` varchar(255) NOT NULL DEFAULT 'units',
  `quantity_requested` decimal(15,2) NOT NULL DEFAULT 0.00,
  `quantity_approved` decimal(15,2) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `alternative_description` varchar(255) DEFAULT NULL,
  `alternative_quantity_approved` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `delivery_person` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `desc` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `event_type` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `severity` varchar(255) NOT NULL DEFAULT 'info',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `ip_address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `can_add_inventory` tinyint(1) NOT NULL DEFAULT 1,
  `can_operate_logistics` tinyint(1) NOT NULL DEFAULT 1,
  `can_generate_reports` tinyint(1) NOT NULL DEFAULT 1,
  `is_online` tinyint(1) NOT NULL DEFAULT 0,
  `department` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `recovery_secret` varchar(255) DEFAULT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `is_temp_account` tinyint(1) NOT NULL DEFAULT 0,
  `otp_token` varchar(255) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `two_factor_code` varchar(255) DEFAULT NULL,
  `two_factor_expires_at` datetime DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `service_number` varchar(255) DEFAULT NULL,
  `signature` varchar(255) DEFAULT NULL,
  `last_logout_at` timestamp NULL DEFAULT NULL,
  `rank` varchar(255) DEFAULT NULL,
  `sponsored_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `edit_requests`
--
ALTER TABLE `edit_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `edit_requests_user_id_foreign` (`user_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `inventory_batches`
--
ALTER TABLE `inventory_batches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_batches_approved_by_foreign` (`approved_by`),
  ADD KEY `inventory_batches_recorded_by_foreign` (`recorded_by`),
  ADD KEY `inventory_batches_supplier_status_index` (`supplier_status`),
  ADD KEY `inventory_batches_ledge_category_index` (`ledge_category`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_items_batch_id_foreign` (`batch_id`),
  ADD KEY `inventory_items_description_index` (`description`);

--
-- Indexes for table `issuances`
--
ALTER TABLE `issuances`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `issued_items`
--
ALTER TABLE `issued_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `issued_items_issuance_id_foreign` (`issuance_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `messages_edit_request_id_index` (`edit_request_id`),
  ADD KEY `messages_sender_id_index` (`sender_id`),
  ADD KEY `messages_receiver_id_index` (`receiver_id`),
  ADD KEY `messages_is_automated_index` (`is_automated`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification_acknowledgements`
--
ALTER TABLE `notification_acknowledgements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `notif_ack_user_item_type_unique` (`user_id`,`item_description`,`alert_type`);

--
-- Indexes for table `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `password_reset_requests_user_id_foreign` (`user_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `receipts`
--
ALTER TABLE `receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipts_requisition_id_unique` (`requisition_id`),
  ADD UNIQUE KEY `receipts_receipt_number_unique` (`receipt_number`);

--
-- Indexes for table `returned_items`
--
ALTER TABLE `returned_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `returned_items_issued_item_id_foreign` (`issued_item_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`);

--
-- Indexes for table `store_requisitions`
--
ALTER TABLE `store_requisitions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `store_requisitions_requested_by_foreign` (`requested_by`),
  ADD KEY `store_requisitions_processed_by_foreign` (`processed_by`),
  ADD KEY `store_requisitions_collected_by_foreign` (`collected_by`),
  ADD KEY `store_requisitions_department_index` (`department`),
  ADD KEY `store_requisitions_status_index` (`status`),
  ADD KEY `store_requisitions_alternative_status_index` (`alternative_status`);

--
-- Indexes for table `store_requisition_items`
--
ALTER TABLE `store_requisition_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `store_requisition_items_requisition_id_foreign` (`requisition_id`),
  ADD KEY `store_requisition_items_description_index` (`description`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `suppliers_name_unique` (`name`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `system_logs_user_id_foreign` (`user_id`),
  ADD KEY `system_logs_event_type_index` (`event_type`),
  ADD KEY `system_logs_action_index` (`action`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_sponsored_by_foreign` (`sponsored_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `edit_requests`
--
ALTER TABLE `edit_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_batches`
--
ALTER TABLE `inventory_batches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `issuances`
--
ALTER TABLE `issuances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `issued_items`
--
ALTER TABLE `issued_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `notification_acknowledgements`
--
ALTER TABLE `notification_acknowledgements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `returned_items`
--
ALTER TABLE `returned_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `store_requisitions`
--
ALTER TABLE `store_requisitions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `store_requisition_items`
--
ALTER TABLE `store_requisition_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `edit_requests`
--
ALTER TABLE `edit_requests`
  ADD CONSTRAINT `edit_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_batches`
--
ALTER TABLE `inventory_batches`
  ADD CONSTRAINT `inventory_batches_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_batches_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD CONSTRAINT `inventory_items_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `inventory_batches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `issued_items`
--
ALTER TABLE `issued_items`
  ADD CONSTRAINT `issued_items_issuance_id_foreign` FOREIGN KEY (`issuance_id`) REFERENCES `issuances` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_acknowledgements`
--
ALTER TABLE `notification_acknowledgements`
  ADD CONSTRAINT `notification_acknowledgements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  ADD CONSTRAINT `password_reset_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `returned_items`
--
ALTER TABLE `returned_items`
  ADD CONSTRAINT `returned_items_issued_item_id_foreign` FOREIGN KEY (`issued_item_id`) REFERENCES `issued_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `store_requisitions`
--
ALTER TABLE `store_requisitions`
  ADD CONSTRAINT `store_requisitions_collected_by_foreign` FOREIGN KEY (`collected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `store_requisitions_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `store_requisitions_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `store_requisition_items`
--
ALTER TABLE `store_requisition_items`
  ADD CONSTRAINT `store_requisition_items_requisition_id_foreign` FOREIGN KEY (`requisition_id`) REFERENCES `store_requisitions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_sponsored_by_foreign` FOREIGN KEY (`sponsored_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
