-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th3 30, 2026 lúc 03:06 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `qltb`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `borrow_details`
--

CREATE TABLE `borrow_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `borrow_request_id` bigint(20) UNSIGNED NOT NULL,
  `device_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `borrow_requests`
--

CREATE TABLE `borrow_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','approved','rejected','returned') NOT NULL DEFAULT 'pending',
  `purpose` text DEFAULT NULL,
  `class_name` varchar(255) DEFAULT NULL,
  `borrow_date` date NOT NULL,
  `expected_return_date` date NOT NULL,
  `admin_note` text DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `damages`
--

CREATE TABLE `damages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `borrow_detail_id` bigint(20) UNSIGNED DEFAULT NULL,
  `device_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `damage_type` enum('hỏng','mất') NOT NULL DEFAULT 'hỏng',
  `detected_date` date DEFAULT NULL,
  `cause` varchar(255) DEFAULT NULL,
  `resolution` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `severity` enum('minor','moderate','severe') NOT NULL DEFAULT 'minor',
  `reported_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `damages`
--

INSERT INTO `damages` (`id`, `borrow_detail_id`, `device_id`, `quantity`, `damage_type`, `detected_date`, `cause`, `resolution`, `description`, `severity`, `reported_by`, `created_at`, `updated_at`) VALUES
(1, NULL, 9, 1, 'hỏng', '2026-03-30', NULL, NULL, 'abc', 'minor', 2, '2026-03-30 00:45:35', '2026-03-30 00:45:35'),
(2, NULL, 9, 1, 'hỏng', '2026-03-30', NULL, NULL, 'abc', 'minor', 2, '2026-03-30 00:45:41', '2026-03-30 00:45:41'),
(3, NULL, 9, 1, 'hỏng', '2026-03-30', 'abc', 'abc', 'abc', 'minor', 2, '2026-03-30 00:45:47', '2026-03-30 00:45:47'),
(4, NULL, 9, 1, 'hỏng', '2026-03-30', NULL, NULL, 'abc', 'minor', 2, '2026-03-30 00:46:30', '2026-03-30 00:46:30');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `devices`
--

CREATE TABLE `devices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `subject_group` varchar(255) DEFAULT NULL COMMENT 'Tổ chuyên môn: Toán, Lý, Hóa, ...',
  `unit` varchar(255) NOT NULL DEFAULT 'Cái',
  `specification` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `unit_price` decimal(15,2) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `available_qty` int(11) NOT NULL DEFAULT 0,
  `damaged_qty` int(11) NOT NULL DEFAULT 0,
  `lost_qty` int(11) NOT NULL DEFAULT 0,
  `status` enum('available','borrowed','maintenance','damaged') NOT NULL DEFAULT 'available',
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `devices`
--

INSERT INTO `devices` (`id`, `code`, `name`, `category`, `subject`, `subject_group`, `unit`, `specification`, `country`, `unit_price`, `quantity`, `available_qty`, `damaged_qty`, `lost_qty`, `status`, `description`, `created_at`, `updated_at`) VALUES
(1, 'TB001', 'Máy tính xách tay Dell', 'Máy tính', NULL, NULL, 'Cái', NULL, NULL, NULL, 10, 10, 0, 0, 'available', NULL, '2026-03-29 23:54:36', '2026-03-29 23:54:36'),
(2, 'TB002', 'Máy chiếu Epson', 'Máy chiếu', NULL, NULL, 'Cái', NULL, NULL, NULL, 5, 5, 0, 0, 'available', NULL, '2026-03-29 23:54:36', '2026-03-29 23:54:36'),
(3, 'TB003', 'Loa Bluetooth JBL', 'Âm thanh', NULL, NULL, 'Cái', NULL, NULL, NULL, 8, 8, 0, 0, 'available', NULL, '2026-03-29 23:54:36', '2026-03-29 23:54:36'),
(4, 'TB004', 'Micro không dây', 'Âm thanh', NULL, NULL, 'Cái', NULL, NULL, NULL, 6, 6, 0, 0, 'available', NULL, '2026-03-29 23:54:36', '2026-03-29 23:54:36'),
(5, 'TB005', 'Màn hình chiếu 120 inch', 'Máy chiếu', NULL, NULL, 'Cái', NULL, NULL, NULL, 3, 3, 0, 0, 'available', NULL, '2026-03-29 23:54:36', '2026-03-29 23:54:36'),
(6, 'TB006', 'Máy ảnh Canon EOS', 'Máy ảnh', NULL, NULL, 'Cái', NULL, NULL, NULL, 4, 4, 0, 0, 'available', NULL, '2026-03-29 23:54:36', '2026-03-29 23:54:36'),
(7, 'TB007', 'Bảng thông minh tương tác', 'Thiết bị dạy học', NULL, NULL, 'Cái', NULL, NULL, NULL, 8, 8, 0, 0, 'available', NULL, '2026-03-29 23:54:36', '2026-03-30 00:07:08'),
(8, 'TB008', 'Máy in HP LaserJet', 'Máy in', NULL, NULL, 'Cái', NULL, NULL, NULL, 3, 3, 0, 0, 'available', NULL, '2026-03-29 23:54:36', '2026-03-29 23:54:36'),
(9, 'TDD-180-VN', 'Thuoc do do nhua', NULL, 'Toan', NULL, 'Cai', '180 nhua', 'Viet Nam', 31000.00, 10, 10, 0, 0, 'available', NULL, '2026-03-30 00:42:06', '2026-03-30 01:11:44'),
(10, 'TB-670E2-2767', 'Máy tính bàn', 'Máy tính', 'Toan', NULL, 'Cái', NULL, 'Việt Nam', 1000000.00, 20, 20, 0, 0, 'available', 'Dành cho phòng máy tính', '2026-03-30 05:19:57', '2026-03-30 05:19:57'),
(11, 'TB-E7EED-5860', 'Máy tính bàn', 'Máy tính', 'Tin học', NULL, 'Cái', NULL, 'Việt Nam', 10000000.00, 2, 2, 0, 0, 'available', NULL, '2026-03-30 05:20:48', '2026-03-30 05:20:48');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `imports`
--

CREATE TABLE `imports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `device_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `import_date` date NOT NULL,
  `note` text DEFAULT NULL,
  `imported_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `imports`
--

INSERT INTO `imports` (`id`, `device_id`, `quantity`, `price`, `supplier`, `import_date`, `note`, `imported_by`, `created_at`, `updated_at`) VALUES
(1, 7, 1, 5000.00, 'abc', '2026-03-30', 'abc', 1, '2026-03-30 00:06:49', '2026-03-30 00:06:49'),
(2, 7, 5, NULL, 'abc', '2026-03-30', 'abc', 1, '2026-03-30 00:07:08', '2026-03-30 00:07:08');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '2024_01_01_000001_create_devices_table', 1),
(3, '2024_01_01_000002_create_imports_table', 1),
(4, '2024_01_01_000003_create_borrow_requests_table', 1),
(5, '2024_01_01_000004_create_borrow_details_table', 1),
(6, '2024_01_01_000005_create_returns_table', 1),
(7, '2024_01_01_000006_create_damages_table', 1),
(8, '2024_01_01_000007_add_fields_to_devices_table', 2),
(9, '2024_01_01_000008_add_fields_to_damages_table', 2),
(10, '2024_01_01_000009_add_class_to_borrow_requests_table', 2),
(11, '2026_03_30_115710_add_subject_group_to_users_table', 3),
(12, '2026_03_30_124341_add_subject_group_to_devices_table', 4);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `returns`
--

CREATE TABLE `returns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `borrow_request_id` bigint(20) UNSIGNED NOT NULL,
  `returned_by` bigint(20) UNSIGNED NOT NULL,
  `return_date` date NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teacher') NOT NULL DEFAULT 'teacher',
  `subject_group` varchar(255) DEFAULT NULL COMMENT 'Tổ chuyên môn: Toán, Lý, Hóa, Sinh, Văn, Sử, Địa, GDCD, Tin, Ngoại ngữ, Thể dục, Nghề',
  `phone` varchar(255) DEFAULT NULL,
  `school` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `subject_group`, `phone`, `school`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Quản Trị Viên', 'admin@qltb.local', '$2y$12$/Z6CM0VEQSVoyzqeO88CCOjvdsXpacN1pTQEFXwBe8H3ADE9L55zi', 'admin', NULL, '0901234567', 'Trường THPT ABC', NULL, '2026-03-29 23:54:36', '2026-03-29 23:54:36'),
(2, 'Nguyễn Văn An', 'teacher@qltb.local', '$2y$12$QuNYaL51Dv.i5qjhnXRXMO121dFe.uzFu8lXL5/XugnGQ6fX907QG', 'teacher', NULL, '0987654321', 'Trường THPT ABC', NULL, '2026-03-29 23:54:36', '2026-03-29 23:54:36'),
(3, 'Trần Thị Bích', 'teacher2@qltb.local', '$2y$12$r/aLfbbAuCRyQorny9MpI.48hopqkjfSgHd4KQcsD3gU7m9ObKJ2q', 'teacher', NULL, '0912345678', 'Trường THPT ABC', NULL, '2026-03-29 23:54:36', '2026-03-29 23:54:36'),
(4, 'Admin', 'admin@gmail.com', '$2y$12$A1jUNaxRvwdl4hFqEudM9e1dRZfbM5RUO/mMJPg63Lwjuc5PyPVf6', 'admin', NULL, NULL, NULL, NULL, '2026-03-30 05:04:38', '2026-03-30 05:04:38'),
(5, 'Nguyễn Minh Luân', 'minhluanngulac@gmail.com', '$2y$12$j7SXQ5cZN4AY1V3vmokLeuseWuQV.80.0XcSZ3t/U2AmFPSiPBjLK', 'teacher', 'Toán', NULL, NULL, NULL, '2026-03-30 05:06:36', '2026-03-30 05:06:36');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `borrow_details`
--
ALTER TABLE `borrow_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `borrow_details_borrow_request_id_foreign` (`borrow_request_id`),
  ADD KEY `borrow_details_device_id_foreign` (`device_id`);

--
-- Chỉ mục cho bảng `borrow_requests`
--
ALTER TABLE `borrow_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `borrow_requests_user_id_foreign` (`user_id`),
  ADD KEY `borrow_requests_approved_by_foreign` (`approved_by`);

--
-- Chỉ mục cho bảng `damages`
--
ALTER TABLE `damages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `damages_borrow_detail_id_foreign` (`borrow_detail_id`),
  ADD KEY `damages_device_id_foreign` (`device_id`),
  ADD KEY `damages_reported_by_foreign` (`reported_by`);

--
-- Chỉ mục cho bảng `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `devices_code_unique` (`code`);

--
-- Chỉ mục cho bảng `imports`
--
ALTER TABLE `imports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `imports_device_id_foreign` (`device_id`),
  ADD KEY `imports_imported_by_foreign` (`imported_by`);

--
-- Chỉ mục cho bảng `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Chỉ mục cho bảng `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `returns_borrow_request_id_foreign` (`borrow_request_id`),
  ADD KEY `returns_returned_by_foreign` (`returned_by`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `borrow_details`
--
ALTER TABLE `borrow_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `borrow_requests`
--
ALTER TABLE `borrow_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `damages`
--
ALTER TABLE `damages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `devices`
--
ALTER TABLE `devices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `imports`
--
ALTER TABLE `imports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `returns`
--
ALTER TABLE `returns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `borrow_details`
--
ALTER TABLE `borrow_details`
  ADD CONSTRAINT `borrow_details_borrow_request_id_foreign` FOREIGN KEY (`borrow_request_id`) REFERENCES `borrow_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `borrow_details_device_id_foreign` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `borrow_requests`
--
ALTER TABLE `borrow_requests`
  ADD CONSTRAINT `borrow_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `borrow_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `damages`
--
ALTER TABLE `damages`
  ADD CONSTRAINT `damages_borrow_detail_id_foreign` FOREIGN KEY (`borrow_detail_id`) REFERENCES `borrow_details` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `damages_device_id_foreign` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `damages_reported_by_foreign` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `imports`
--
ALTER TABLE `imports`
  ADD CONSTRAINT `imports_device_id_foreign` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `imports_imported_by_foreign` FOREIGN KEY (`imported_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `returns`
--
ALTER TABLE `returns`
  ADD CONSTRAINT `returns_borrow_request_id_foreign` FOREIGN KEY (`borrow_request_id`) REFERENCES `borrow_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `returns_returned_by_foreign` FOREIGN KEY (`returned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
