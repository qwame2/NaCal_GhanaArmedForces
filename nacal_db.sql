-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 22, 2026 at 01:52 AM
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
  `donor_name` varchar(255) DEFAULT NULL,
  `acquisition_type` varchar(255) NOT NULL DEFAULT 'Supplier',
  `entry_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_batches`
--

INSERT INTO `inventory_batches` (`id`, `ledge_category`, `supplier_name`, `donor_name`, `acquisition_type`, `entry_date`, `created_at`, `updated_at`) VALUES
(10, 'C', 'CompuGhana [Partial Delivery]', NULL, 'Supplier', '2026-04-11 12:52:06', '2026-04-11 12:53:22', '2026-04-11 12:53:22'),
(11, 'B', 'Brono [Full Delivery]', NULL, 'Supplier', '2026-04-11 13:05:39', '2026-04-11 13:09:27', '2026-04-11 13:09:27'),
(12, 'B', 'Brono [Full Delivery]', NULL, 'Supplier', '2026-04-11 14:00:12', '2026-04-11 14:01:09', '2026-04-11 14:01:09'),
(14, 'C', 'CompuGhana [Full Delivery]', NULL, 'Supplier', '2026-04-11 16:21:10', '2026-04-11 16:22:50', '2026-04-11 16:22:50'),
(16, 'B', 'Brono [Full Delivery]', NULL, 'Supplier', '2026-04-11 18:45:33', '2026-04-11 18:47:49', '2026-04-11 18:47:49'),
(18, 'B', 'Brono [Full Delivery]', NULL, 'Supplier', '2026-04-11 21:27:39', '2026-04-11 21:28:28', '2026-04-11 21:28:28'),
(19, 'B', 'Brono [Full Delivery]', NULL, 'Supplier', '2026-04-12 22:49:55', '2026-04-12 22:50:52', '2026-04-12 22:50:52'),
(23, 'B', 'Brono', 'ADOMAKO EMMANUEL', 'Donor', '2026-04-13 00:40:00', '2026-04-13 00:40:22', '2026-04-13 00:40:22'),
(24, 'B', 'Brono [Partial Delivery]', NULL, 'Supplier', '2026-04-13 21:18:05', '2026-04-13 21:18:46', '2026-04-13 21:18:46'),
(25, 'B', '[Partial Delivery]', NULL, 'Supplier', '2026-04-13 21:48:59', '2026-04-13 21:54:09', '2026-04-13 21:54:09'),
(26, 'B', 'Brono [Full Delivery]', NULL, 'Supplier', '2026-04-13 22:36:27', '2026-04-13 22:35:55', '2026-04-13 22:36:27'),
(27, 'B', 'Brono [Full Delivery]', NULL, 'Supplier', '2026-04-13 22:38:05', '2026-04-13 22:37:18', '2026-04-13 22:38:05');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `batch_id` bigint(20) UNSIGNED NOT NULL,
  `description` varchar(255) NOT NULL,
  `ledge_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `stock_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `qty` varchar(255) DEFAULT NULL,
  `variance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_items`
--

INSERT INTO `inventory_items` (`id`, `batch_id`, `description`, `ledge_balance`, `stock_balance`, `qty`, `variance`, `remarks`, `created_at`, `updated_at`) VALUES
(9, 10, 'DELL Laptop', 9.00, 18.00, NULL, 9.00, 'Brand New', '2026-04-11 12:53:22', '2026-04-21 20:44:15'),
(10, 11, 'Broom', 45.00, 0.00, NULL, -41.00, NULL, '2026-04-11 13:09:27', '2026-04-19 21:57:58'),
(11, 12, 'BRUSH', 23.00, 23.00, NULL, 0.00, NULL, '2026-04-11 14:01:09', '2026-04-11 14:01:09'),
(14, 16, 'Broom', 9.00, 0.00, '9', 0.00, NULL, '2026-04-11 18:47:49', '2026-04-19 21:57:58'),
(15, 16, 'BRUSH', 8.00, 8.00, '8', 0.00, NULL, '2026-04-11 18:47:49', '2026-04-11 18:47:49'),
(16, 16, 'BRUSH STICK', 7.00, 1.00, '7', 0.00, NULL, '2026-04-11 18:47:49', '2026-04-21 02:30:37'),
(17, 16, 'BRUSH', 6.00, 6.00, '6', 0.00, NULL, '2026-04-11 18:47:49', '2026-04-11 18:47:49'),
(18, 16, 'Broom', 4.00, 0.00, '4', 0.00, NULL, '2026-04-11 18:47:49', '2026-04-19 21:57:58'),
(20, 18, 'BRUSH', 100.00, 100.00, '100', 0.00, NULL, '2026-04-11 21:28:28', '2026-04-11 21:28:28'),
(21, 19, 'Broom', 13.00, 0.00, '12', -12.00, 'we cant find it', '2026-04-12 22:50:52', '2026-04-19 21:57:58'),
(25, 23, 'Broom', 12.00, 10.00, '12', 0.00, NULL, '2026-04-13 00:40:22', '2026-04-19 21:57:58'),
(26, 24, 'BRUSH STICK', 20.00, 20.00, '20', 0.00, '[Pending: 10] | Supplemented with 8 additional units. | Supplemented with 2 additional units.', '2026-04-13 21:18:46', '2026-04-13 22:25:25'),
(27, 25, 'BRUSH', 20.00, 18.00, '20', -2.00, '2 is spoilt | Supplemented with 10 additional units.', '2026-04-13 21:54:09', '2026-04-13 22:24:41'),
(28, 26, 'BRUSH STICK', 30.00, 28.00, '30', -2.00, '2 spoilt | Supplemented with 10 additional units.', '2026-04-13 22:35:55', '2026-04-13 22:36:27'),
(29, 27, 'BRUSH', 40.00, 40.00, '40', 0.00, 'Supplemented with 20 additional units.', '2026-04-13 22:37:18', '2026-04-13 22:38:05');

-- --------------------------------------------------------

--
-- Table structure for table `issuances`
--

CREATE TABLE `issuances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `issuance_date` date NOT NULL,
  `beneficiary` varchar(255) NOT NULL,
  `issuance_type` varchar(255) NOT NULL DEFAULT 'Permanent',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `issuances`
--

INSERT INTO `issuances` (`id`, `issuance_date`, `beneficiary`, `issuance_type`, `created_at`, `updated_at`) VALUES
(3, '2026-04-15', 'prnd', 'Permanent', '2026-04-15 23:28:29', '2026-04-15 23:28:29'),
(4, '2026-04-15', 'prnd', 'Temporary', '2026-04-15 23:47:34', '2026-04-15 23:47:34'),
(5, '2026-04-21', 'prnd', 'Permanent', '2026-04-21 02:30:37', '2026-04-21 02:30:37'),
(6, '2026-04-19', 'prnd', 'Permanent', '2026-04-19 21:57:58', '2026-04-19 21:57:58');

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

--
-- Dumping data for table `issued_items`
--

INSERT INTO `issued_items` (`id`, `issuance_id`, `description`, `ledge_category`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 3, 'BRUSH STICK', 'B', 5, '2026-04-15 23:28:29', '2026-04-15 23:28:29'),
(2, 4, 'DELL Laptop', 'C', 0, '2026-04-15 23:47:34', '2026-04-20 22:14:59'),
(3, 5, 'DELL Laptop', 'C', 0, '2026-04-21 02:30:37', '2026-04-21 20:44:15'),
(4, 5, 'BRUSH STICK', 'B', 1, '2026-04-21 02:30:37', '2026-04-21 02:30:37'),
(5, 6, 'Broom', 'B', 20, '2026-04-19 21:57:58', '2026-04-19 21:57:58');

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
(4, '2026_04_10_142009_create_inventory_batches_table', 2),
(5, '2026_04_10_142011_create_inventory_items_table', 2),
(6, '2026_04_10_150102_change_balance_columns_to_string_in_inventory_items_table', 3),
(7, '2026_04_10_151200_add_remarks_to_inventory_items_table', 3),
(8, '2026_04_11_161800_add_qty_to_inventory_items_table', 4),
(9, '2026_04_11_212500_drop_folio_from_inventory_items_table', 5),
(10, '2026_04_11_212504_drop_folio_from_inventory_items_table', 5),
(11, '2026_04_13_002800_add_donor_to_inventory_batches_table', 6),
(12, '2026_04_13_003400_make_supplier_name_nullable_in_inventory_batches_table', 7),
(13, '2026_04_15_224127_create_issuances_table', 8),
(14, '2026_04_15_225000_add_missing_columns_to_issuances_table', 9),
(15, '2026_04_15_225500_create_missing_issued_items_table', 10),
(16, '2026_04_15_230500_add_issuance_type_to_issuances_table', 11),
(17, '2026_04_15_230700_drop_siv_no_from_issuances_table', 12),
(18, '2026_04_19_150000_add_username_to_users_table', 13),
(19, '2026_04_19_151000_make_email_nullable_in_users_table', 13),
(20, '2026_04_19_184130_add_avatar_to_users_table', 14),
(21, '2026_04_19_190000_add_professional_columns_to_users_table', 15),
(22, '2026_04_20_000000_add_two_factor_to_users_table', 16),
(23, '2026_04_20_200709_add_two_factor_to_users_table', 16),
(24, '2026_04_20_000001_add_last_login_at_to_users_table', 17),
(25, '2026_04_20_000002_add_2fa_verification_columns_to_users_table', 18);

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
('gRaXwDlXQddXeacWZdJpovZpiBfcI5ydofwlsAzw', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiWDhPRWl5Qk8xWnBtcDFDQlNvRHpoWm12VkluQktwWDVMODUzUHVvTCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI5OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvcmVwb3J0cyI7czo1OiJyb3V0ZSI7czoxMzoicmVwb3J0cy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI7fQ==', 1776815460);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `two_factor_code` varchar(255) DEFAULT NULL,
  `two_factor_expires_at` datetime DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `role`, `department`, `phone`, `email`, `email_verified_at`, `password`, `two_factor_enabled`, `two_factor_code`, `two_factor_expires_at`, `avatar`, `remember_token`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'Adomako Emmanuel', 'Admin', NULL, NULL, '0203204273', NULL, NULL, '$2y$12$y9uGVCtcCiygJ5ZRjkTs5OBBZZGJRBFflQM3fmMUqozqYgvVKa.3u', 0, NULL, NULL, 'avatars/wh4s3zviRsRHvPVdepNxGHZOWgytjqMZ8ESzElqc.png', NULL, NULL, '2026-04-19 18:42:46', '2026-04-19 20:35:51'),
(2, 'Adomako Emmanuel', 'Officer', NULL, NULL, NULL, NULL, NULL, '$2y$12$7nHOwrwgfOBRRlDqaJugX.onoaKWcZUydLVCLnCR0lqg3efcEQWnK', 1, '103218', '2026-04-20 20:41:00', 'avatars/00WE7O2xUfbSf8xNBE050HJKPNRqHZ5U6rwX1LWG.png', NULL, '2026-04-21 20:18:57', '2026-04-20 20:03:57', '2026-04-21 23:44:00');

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
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `inventory_batches`
--
ALTER TABLE `inventory_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_items_batch_id_foreign` (`batch_id`);

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
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_batches`
--
ALTER TABLE `inventory_batches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `issuances`
--
ALTER TABLE `issuances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `issued_items`
--
ALTER TABLE `issued_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
