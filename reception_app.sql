-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: db_server:3306
-- 生成日時: 2026 年 4 月 16 日 00:33
-- サーバのバージョン： 10.4.34-MariaDB-1:10.4.34+maria~ubu2004
-- PHP のバージョン: 8.2.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `reception_app`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `announcements`
--

CREATE TABLE `announcements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL COMMENT 'お知らせタイトル',
  `content` text NOT NULL COMMENT 'お知らせ内容',
  `type` varchar(255) NOT NULL DEFAULT 'info' COMMENT '種別（info, warning, error）',
  `start_date` date NOT NULL COMMENT '表示開始日',
  `end_date` date NOT NULL COMMENT '表示終了日',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '有効フラグ',
  `display_order` int(11) NOT NULL DEFAULT 0 COMMENT '表示順',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `appointments`
--

CREATE TABLE `appointments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reception_number` varchar(4) NOT NULL COMMENT '受付番号（4桁）',
  `company_name` varchar(255) NOT NULL COMMENT '会社名',
  `visitor_name` varchar(255) NOT NULL COMMENT '訪問者氏名',
  `visitor_email` varchar(255) DEFAULT NULL COMMENT '訪問者メールアドレス',
  `visitor_phone` varchar(255) DEFAULT NULL COMMENT '訪問者電話番号',
  `staff_member_id` bigint(20) UNSIGNED NOT NULL,
  `visit_date` date NOT NULL COMMENT '訪問予定日',
  `visit_time` time NOT NULL COMMENT '訪問予定時刻',
  `purpose` text DEFAULT NULL COMMENT '訪問目的',
  `qr_code` varchar(255) DEFAULT NULL COMMENT 'QRコードデータ',
  `is_checked_in` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'チェックイン済みフラグ',
  `checked_in_at` timestamp NULL DEFAULT NULL COMMENT 'チェックイン日時',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `send_flg` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'メール送信済みフラグ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- テーブルのデータのダンプ `appointments`
--

INSERT INTO `appointments` (`id`, `reception_number`, `company_name`, `visitor_name`, `visitor_email`, `visitor_phone`, `staff_member_id`, `visit_date`, `visit_time`, `purpose`, `qr_code`, `is_checked_in`, `checked_in_at`, `created_at`, `updated_at`, `deleted_at`, `send_flg`) VALUES
(1, '0309', '株式会社アキオカ', '村上飛羽', 'to-murakami@akioka-ltd.jp', '00000000000', 91, '2025-10-07', '12:06:00', 'test', 'qr-codes/qr_0309.svg', 1, '2025-10-06 12:17:08', '2025-10-06 12:04:44', '2025-11-13 17:57:30', '2025-11-13 17:57:30', 1),
(2, '7006', '株式会社アキオカ', '村上飛羽', 'to-murakami@akioka-ltd.jp', '00000000000', 91, '2025-10-08', '09:49:00', 'test', 'qr-codes/qr_7006.svg', 1, '2025-10-08 10:46:50', '2025-10-08 09:47:41', '2025-11-13 17:57:28', '2025-11-13 17:57:28', 1),
(3, '0838', '株式会社アキオカ', 'test', 'to-murakami@akioka-ltd.jp', '00000000000', 91, '2025-11-07', '13:43:00', 'テスト打ち合わせ', 'qr-codes/qr_0838.svg', 0, NULL, '2025-11-06 13:41:23', '2025-11-13 17:57:25', '2025-11-13 17:57:25', 0),
(4, '7765', 'システムプロダクト様', '片岡様', 'ykataoka@sysproduct.com', '070-3775-6003', 43, '2025-11-17', '15:10:00', '電気炉NW盤移設の下見', 'qr-codes/qr_7765.svg', 0, NULL, '2025-11-12 16:26:11', '2025-11-12 16:26:11', NULL, 0);

-- --------------------------------------------------------

--
-- テーブルの構造 `deliveries`
--

CREATE TABLE `deliveries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `delivery_type` varchar(255) NOT NULL,
  `document_image` varchar(255) NOT NULL,
  `sealed_document_image` varchar(255) DEFAULT NULL,
  `qr_code_url` varchar(255) DEFAULT NULL,
  `qr_code_file_path` varchar(255) DEFAULT NULL,
  `received_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `staff_member_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- テーブルのデータのダンプ `deliveries`
--

INSERT INTO `deliveries` (`id`, `delivery_type`, `document_image`, `sealed_document_image`, `qr_code_url`, `qr_code_file_path`, `received_at`, `created_at`, `updated_at`, `staff_member_id`) VALUES
(1, '納品書', 'delivery_documents/gfuGT9XDLVwMspc1Yjo4Gy7V76q5GjeIcJzjCaF3.jpg', NULL, 'https://unnagging-needingly-archimedes.ngrok-free.dev/delivery/1', 'qr-codes/qr_delivery_1.svg', '2025-10-06 02:49:45', '2025-10-06 11:49:42', '2025-10-06 11:49:42', NULL),
(2, '納品書', 'delivery_documents/b5rLxlPLrdF3k6VX23VUNZA0kRBLB9DkMzlhnfQC.jpg', NULL, 'https://unnagging-needingly-archimedes.ngrok-free.dev/delivery/2', 'qr-codes/qr_delivery_2.svg', '2025-10-06 02:53:15', '2025-10-06 11:53:13', '2025-10-06 11:53:13', NULL),
(3, '納品書', 'delivery_documents/QaiHPaCWTO935AXc1rJrqFKI6uzT1QVnQ5F8YU6y.jpg', NULL, 'https://unnagging-needingly-archimedes.ngrok-free.dev/delivery/3', 'qr-codes/qr_delivery_3.svg', '2025-10-06 02:57:43', '2025-10-06 11:57:41', '2025-10-06 11:57:41', NULL),
(4, '納品書', 'delivery_documents/fj347rcDP0tZeSfsAO5ciKNQ41r0gOl1WLM5MbxN.jpg', NULL, 'https://unnagging-needingly-archimedes.ngrok-free.dev/delivery/4', 'qr-codes/qr_delivery_4.svg', '2025-10-06 03:07:04', '2025-10-06 12:07:01', '2025-10-06 12:07:01', NULL),
(5, '納品書', 'delivery_documents/bScbR7qlYngYLwrf8rD2HvbkhjDoLnmhsiFpdC5k.jpg', NULL, 'http://127.0.0.1:8000/delivery/5', NULL, '2025-10-07 06:23:14', '2025-10-07 15:23:14', '2025-10-07 15:23:14', NULL),
(6, '納品書', 'delivery_documents/JkHsD52rzYX7ISNjLHEyiRjBJI1aSKEFZVcIq7g1.jpg', NULL, 'https://unnagging-needingly-archimedes.ngrok-free.dev/delivery/6', 'qr-codes/qr_delivery_6.svg', '2025-10-07 22:56:56', '2025-10-08 07:56:56', '2025-10-08 07:56:56', NULL),
(7, '納品書', 'delivery_documents/d75bs6cq6dVFeRPl55tb9MWqO896s7k0O6QNFaf9.jpg', NULL, 'http://127.0.0.1:8000/delivery/7', 'qr-codes/qr_delivery_7.svg', '2025-10-07 23:06:37', '2025-10-08 08:06:37', '2025-10-08 08:06:37', NULL),
(8, '納品書', 'delivery_documents/XX3Ea7pGIe9H0362PCZH4by25Q0r5za2xgotiJQT.jpg', NULL, 'http://127.0.0.1:8000/delivery/8', 'qr-codes/qr_delivery_8.svg', '2025-10-08 00:24:12', '2025-10-08 09:24:11', '2025-10-08 09:24:11', NULL),
(9, '納品書', 'delivery_documents/PqEFL9T4cge5nd2h5zaFOjHxke3I6wz5fO0VstIH.jpg', 'sealed_documents/sealed_9_1759885657.jpg', 'http://127.0.0.1/delivery/9', 'qr-codes/qr_delivery_9.svg', '2025-10-08 01:07:38', '2025-10-08 10:00:03', '2025-10-08 10:07:37', NULL),
(10, '納品書', 'delivery_documents/aePseO5QUIheEHTEYBQrhJKUo5mqVDw8uPvLNcTC.jpg', 'sealed_documents/sealed_10_1759893065.jpg', 'http://127.0.0.1:8000/delivery/10', 'qr-codes/qr_delivery_10.svg', '2025-10-08 03:11:05', '2025-10-08 10:37:27', '2025-10-08 12:11:05', NULL),
(11, '納品書', 'delivery_documents/Qf2aHwEFumsN8Gzhxcb8RQ8QivXUYO4f5XbtuMsb.jpg', 'sealed_documents/sealed_11_1759896518.jpg', 'http://127.0.0.1:8000/delivery/11', 'qr-codes/qr_delivery_11.svg', '2025-10-08 04:08:38', '2025-10-08 13:05:00', '2025-10-08 13:08:38', NULL),
(12, '納品書', 'delivery_documents/MAyD18xCNvLO5sZF3fuw1iMOqXlLgUcxwRglww9G.jpg', NULL, 'https://akioka-reception.cloud/delivery/12', 'qr-codes/qr_delivery_12.svg', '2025-10-08 11:48:20', '2025-10-08 20:48:20', '2025-10-08 20:48:20', NULL),
(13, '納品書', 'delivery_documents/rHHGHDovB21h1egtUqtkfXBz2a1HpA8ELb1VKWvr.jpg', NULL, 'https://akioka-reception.cloud/delivery/13', 'qr-codes/qr_delivery_13.svg', '2025-10-08 13:01:04', '2025-10-08 22:01:04', '2025-10-08 22:01:04', NULL),
(14, '納品書', 'delivery_documents/3dxJR3LZXJHQp2oBGSCTsnfzJkW0cbGOORRtVOSS.jpg', NULL, 'http://127.0.0.1:8000/delivery/14', 'qr-codes/qr_delivery_14.svg', '2025-10-09 00:23:42', '2025-10-09 09:23:40', '2025-10-09 09:23:41', NULL),
(15, '受領書', 'delivery_documents/FAelmBEBJxvinzRC3WdUGiIyvplqYhe62ASU1unb.jpg', NULL, 'http://127.0.0.1:8000/delivery/15', 'qr-codes/qr_delivery_15.svg', '2025-10-09 00:26:07', '2025-10-09 09:26:06', '2025-10-09 09:26:06', NULL),
(16, '納品書', 'delivery_documents/JQ9PnCkxj7NIxAgH2ripDX6c54PBx36L2qtmwhYG.jpg', NULL, 'http://127.0.0.1:8000/delivery/16', 'qr-codes/qr_delivery_16.svg', '2025-10-09 00:57:30', '2025-10-09 09:57:30', '2025-10-09 09:57:30', NULL),
(17, '納品書', 'delivery_documents/MSGnQwE8LGRbtYtANvgaO831pY31ATlFtknTSVdq.jpg', NULL, 'https://akioka-reception.cloud/delivery/17', 'qr-codes/qr_delivery_17.svg', '2025-10-09 01:49:27', '2025-10-09 10:49:27', '2025-10-09 10:49:27', NULL),
(18, '受領書', 'delivery_documents/EGMN2JYvQnnjH4GKZqkiPolRw7bDm1rv6DFRnzNi.jpg', NULL, 'https://akioka-reception.cloud/delivery/18', 'qr-codes/qr_delivery_18.svg', '2025-10-09 04:48:14', '2025-10-09 13:48:14', '2025-10-09 13:48:14', NULL),
(19, '納品書', 'delivery_documents/GFMmR8NiPFZhghPm0gqZEmnH0HhNUuQIA2H0KsjA.jpg', NULL, 'https://akioka-reception.cloud/delivery/19', 'qr-codes/qr_delivery_19.svg', '2025-10-16 04:46:33', '2025-10-16 13:46:33', '2025-10-16 13:46:33', NULL),
(20, '納品書', 'delivery_documents/XoSW4KnnfLz5wwMqfYsGNiO7ZtE3G4wdMaZ2auTc.jpg', NULL, 'https://akioka-reception.cloud/delivery/20', 'qr-codes/qr_delivery_20.svg', '2025-10-16 05:18:30', '2025-10-16 14:18:30', '2025-10-16 14:18:30', NULL),
(21, '納品書', 'delivery_documents/DTNpbTmUG3dDI0TOgYavzcRr9KrDEft7knd3z37Z.jpg', NULL, 'https://unnagging-needingly-archimedes.ngrok-free.dev/delivery/21', 'qr-codes/qr_delivery_21.svg', '2025-11-19 04:04:53', '2025-11-19 13:04:52', '2025-11-19 13:04:53', NULL),
(22, '納品書', 'delivery_documents/cN2Tk8d4MbEc1xrSOmBm7b4d7tnBqB426rgX8hSl.jpg', NULL, 'https://akioka-reception.cloud/delivery/22', 'qr-codes/qr_delivery_22.svg', '2025-11-19 05:11:11', '2025-11-19 14:11:11', '2025-11-19 14:11:11', NULL),
(23, '納品書', 'delivery_documents/g0qwDTZ1Z6fXwZAd4n0OhH0QbWZSreO7IDCS1HsF.jpg', NULL, 'https://akioka-reception.cloud/delivery/23', 'qr-codes/qr_delivery_23.svg', '2025-11-19 05:20:10', '2025-11-19 14:20:10', '2025-11-19 14:20:10', NULL),
(24, '納品書', 'delivery_documents/MxjcgcRmfytePVPCRAPXRfN4wlxIqusqTCdZ4Heh.jpg', NULL, 'https://akioka-reception.cloud/delivery/24', 'qr-codes/qr_delivery_24.svg', '2025-11-19 07:56:20', '2025-11-19 16:56:20', '2025-11-19 16:56:20', NULL),
(25, '納品書', 'delivery_documents/9uXFDHMce15UWGGKjBzOhGNxMXiXz15hdAATk73T.jpg', NULL, 'https://akioka-reception.cloud/delivery/25', 'qr-codes/qr_delivery_25.svg', '2025-11-19 08:48:49', '2025-11-19 17:48:49', '2025-11-19 17:48:49', NULL),
(26, '納品書', 'delivery_documents/VLerrfTAniTuIVWzBfoWpFA1JyLqPtD1ms71858G.jpg', NULL, 'https://akioka-reception.cloud/delivery/26', 'qr-codes/qr_delivery_26.svg', '2025-11-19 08:49:07', '2025-11-19 17:49:07', '2025-11-19 17:49:07', NULL),
(27, '納品書', 'delivery_documents/S7khgWCK3s6Tw6frNYYpHplck2Fj4eqEjvArxW4O.jpg', NULL, 'https://akioka-reception.cloud/delivery/27', 'qr-codes/qr_delivery_27.svg', '2025-11-19 09:04:31', '2025-11-19 18:04:30', '2025-11-19 18:04:31', NULL),
(28, '納品書', 'delivery_documents/HxlSIWBrFBQBJvNCdiDICYtXWavhBwpVXfiYlbpu.jpg', NULL, 'https://akioka-reception.cloud/delivery/28', 'qr-codes/qr_delivery_28.svg', '2025-11-19 09:11:21', '2025-11-19 18:11:21', '2025-11-19 18:11:21', NULL),
(29, '納品書', 'delivery_documents/QtqvLHWCrsa4M4O3AupspH8i0e720U6CCXK59VpT.jpg', NULL, 'https://akioka-reception.cloud/delivery/29', 'qr-codes/qr_delivery_29.svg', '2025-11-19 09:21:01', '2025-11-19 18:21:01', '2025-11-19 18:21:01', NULL),
(30, '納品書', 'delivery_documents/SFUotL6FyT5niJjNqmE3VzD2oWzbYo6SSNL2F7YS.jpg', NULL, 'https://akioka-reception.cloud/delivery/30', 'qr-codes/qr_delivery_30.svg', '2025-11-19 09:47:35', '2025-11-19 18:47:35', '2025-11-19 18:47:35', NULL),
(31, '納品書', 'delivery_documents/HmEFFA0D4cGOFUF4C6paSGfg8bLjC3MQzN4Z5Mjw.jpg', NULL, 'https://akioka-reception.cloud/delivery/31', 'qr-codes/qr_delivery_31.svg', '2025-11-19 10:08:27', '2025-11-19 19:08:27', '2025-11-19 19:08:27', NULL),
(32, '納品書', 'delivery_documents/jdMml7fuMizjLHAcI4PsyhD5zADo1xm6zgn4nYlK.jpg', NULL, 'https://akioka-reception.cloud/delivery/32', 'qr-codes/qr_delivery_32.svg', '2025-11-19 10:25:17', '2025-11-19 19:25:17', '2025-11-19 19:25:17', NULL),
(33, '納品書', 'delivery_documents/1qiRRvMnjo0AmxdsYh6g3RjXH7uSy1YTwbcM56ar.jpg', NULL, 'https://akioka-reception.cloud/delivery/33', 'qr-codes/qr_delivery_33.svg', '2025-11-19 10:37:48', '2025-11-19 19:37:48', '2025-11-19 19:37:48', NULL),
(34, '納品書', 'delivery_documents/awB8bbZCufqsMA7DjadFBud0XF0i8Idf1NRgvd96.jpg', 'sealed_documents/sealed_34_1763711650.jpg', 'https://akioka-reception.cloud/delivery/34', 'qr-codes/qr_delivery_34.svg', '2025-11-21 07:54:10', '2025-11-19 19:38:18', '2025-11-21 16:54:10', NULL),
(35, '納品書', 'delivery_documents/sn129ywkmFTd2jSfQBX9fya7rHmbRmq7AWSZlrXu.jpg', 'sealed_documents/sealed_35_1764724135.jpg', 'https://akioka-reception.cloud/delivery/35', 'qr-codes/qr_delivery_35.svg', '2025-12-03 01:08:55', '2025-12-03 10:07:21', '2025-12-03 10:08:55', NULL);

-- --------------------------------------------------------

--
-- テーブルの構造 `delivery_initial_order`
--

CREATE TABLE `delivery_initial_order` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `delivery_id` bigint(20) UNSIGNED NOT NULL,
  `initial_order_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `failed_jobs`
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
-- テーブルの構造 `interview_phones`
--

CREATE TABLE `interview_phones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `department_name` varchar(255) NOT NULL COMMENT '部署名',
  `contact_person` varchar(255) NOT NULL COMMENT '担当者名',
  `phone_number` varchar(255) NOT NULL COMMENT '電話番号',
  `extension_number` varchar(255) DEFAULT NULL COMMENT '内線番号',
  `notes` text DEFAULT NULL COMMENT '備考',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '有効フラグ',
  `display_order` int(11) NOT NULL DEFAULT 0 COMMENT '表示順',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- テーブルのデータのダンプ `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2025_09_30_152015_create_staff_members_table', 1),
(6, '2025_09_30_152020_create_visitors_table', 1),
(7, '2025_09_30_152023_create_deliveries_table', 1),
(8, '2025_09_30_152026_create_pickups_table', 1),
(9, '2025_10_01_100100_add_teams_webhook_url_to_staff_members_table', 1),
(10, '2025_10_01_115422_create_sessions_table', 1),
(11, '2025_10_02_080835_create_appointments_table', 1),
(13, '2025_10_02_081051_create_announcements_table', 1),
(14, '2025_10_03_183358_update_appointments_staff_member_id_to_users_table', 1),
(15, '2025_10_03_183901_remove_foreign_key_from_appointments_staff_member_id', 1),
(16, '2025_10_03_184154_update_visitors_staff_member_id_to_users_table', 1),
(17, '2025_10_03_184402_remove_foreign_key_from_visitors_staff_member_id', 1),
(18, '2025_10_03_191252_add_send_flg_to_appointments_table', 1),
(19, '2025_10_06_095912_remove_company_name_from_deliveries_and_pickups_tables', 1),
(20, '2025_10_06_100526_fix_sealed_image_columns_in_deliveries_and_pickups_tables', 1),
(21, '2025_10_06_100803_fix_nullable_fields_in_deliveries_and_pickups_tables', 1),
(22, '2025_10_06_101538_add_qr_code_file_path_to_deliveries_and_pickups_tables', 1),
(23, '2025_10_06_111028_create_notification_settings_table', 1),
(24, '2025_10_06_111039_create_notification_recipients_table', 1),
(25, '2025_10_06_113716_add_user_id_to_staff_members_table', 1),
(26, '2025_10_06_113959_simplify_staff_members_table', 1),
(27, '2025_10_02_081015_create_interview_phones_table', 2),
(29, '2025_11_06_000001_create_facilities_table', 3),
(30, '2025_11_06_000002_create_schedule_events_table', 3),
(31, '2025_11_06_000003_create_schedule_participants_table', 3),
(32, '2025_11_06_141900_create_user_schedules_table', 4),
(33, '2025_11_06_154431_create_project_groups_table', 5),
(34, '2025_11_06_154449_create_project_group_user_table', 5),
(35, '2025_11_21_164341_create_delivery_initial_order_table', 6);

-- --------------------------------------------------------

--
-- テーブルの構造 `notification_recipients`
--

CREATE TABLE `notification_recipients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `notification_setting_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ユーザーID（akioka_db.usersテーブル参照）',
  `notification_type` enum('phone','email','teams') NOT NULL,
  `notification_data` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- テーブルのデータのダンプ `notification_recipients`
--

INSERT INTO `notification_recipients` (`id`, `notification_setting_id`, `user_id`, `notification_type`, `notification_data`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 5, 91, 'teams', 'to-murakami@akioka-ltd.jp', 1, '2025-10-06 11:47:50', '2025-10-06 11:56:39'),
(3, 6, 91, 'teams', 'to-murakami@akioka-ltd.jp', 1, '2025-10-06 11:47:50', '2025-10-06 11:56:39'),
(4, 7, 91, 'phone', '09061827735', 1, '2025-10-09 09:08:55', '2025-10-09 09:08:55'),
(5, 7, 91, 'email', 'to-murakami@akioka-ltd.jp', 1, '2025-10-09 09:08:55', '2025-10-09 09:08:55');

-- --------------------------------------------------------

--
-- テーブルの構造 `notification_settings`
--

CREATE TABLE `notification_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `trigger_event` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- テーブルのデータのダンプ `notification_settings`
--

INSERT INTO `notification_settings` (`id`, `name`, `description`, `trigger_event`, `is_active`, `settings`, `created_at`, `updated_at`) VALUES
(5, '納品・集荷通知', '配送業者受付時の通知', 'delivery_received', 1, NULL, '2025-10-06 11:47:50', '2025-10-06 11:47:50'),
(6, '集荷伝票通知', '集荷伝票受付時の通知', 'pickup_received', 1, NULL, '2025-10-06 11:47:50', '2025-10-06 11:47:50'),
(7, '面接受付通知', '面接受付時に担当者へ電話とTeams通知を送信', 'visitor_checkin', 1, '{\"auto_call\":true,\"call_delay\":3000}', '2025-10-09 09:08:55', '2025-10-09 09:08:55');

-- --------------------------------------------------------

--
-- テーブルの構造 `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `pickups`
--

CREATE TABLE `pickups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slip_image` varchar(255) NOT NULL,
  `sealed_slip_image` varchar(255) DEFAULT NULL,
  `qr_code_url` varchar(255) DEFAULT NULL,
  `qr_code_file_path` varchar(255) DEFAULT NULL,
  `picked_up_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `staff_member_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- テーブルのデータのダンプ `pickups`
--

INSERT INTO `pickups` (`id`, `slip_image`, `sealed_slip_image`, `qr_code_url`, `qr_code_file_path`, `picked_up_at`, `created_at`, `updated_at`, `staff_member_id`) VALUES
(1, 'pickup_slips/bZpyyIjAQhIQOIBZ6FlxH45zEpahaR5bMthPzyHD.jpg', NULL, 'https://unnagging-needingly-archimedes.ngrok-free.dev/pickup/1', 'qr-codes/qr_pickup_1.svg', '2025-10-06 03:08:42', '2025-10-06 12:08:40', '2025-10-06 12:08:40', NULL);

-- --------------------------------------------------------

--
-- テーブルの構造 `sessions`
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
-- テーブルのデータのダンプ `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('2K0crqNszk1EVaJUynec3bEeeJzsCjq9F61edSOJ', NULL, '45.156.129.133', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMVJ2NjhzWk9UZDVBWmM3V0I5RjVyRTlVeHN1SXRJZFAzZVpuRW5BcyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzA6Imh0dHBzOi8vYWtpb2thLXJlY2VwdGlvbi5jbG91ZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1764723279),
('AxJsBCifUoqcYhSDDtNDoT3JMZG6DUA6RwpmdICu', NULL, '104.199.26.108', 'python-requests/2.32.5', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZXY0Qmo0d3VFRjJyVnZMR0ViM2EzRVEyM0VuQmN1S0V2anVsNWxabyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjI6Imh0dHBzOi8vMTMzLjExNy43NS4xMzYiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1764722224),
('BvGDrXx318E9WJyJpGsQeke8Yd38gyHcXKAX1Fud', NULL, '65.49.1.127', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidkp6ZmxJQVBkZW9EalBHbk12UHZaVFNUa3JudjBFbGIweFJsNzR1VyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzA6Imh0dHBzOi8vYWtpb2thLXJlY2VwdGlvbi5jbG91ZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1764720250),
('e46p8QYTrdwsuCPxL4CKjLNfyEnIfJIsCd4RDAeR', NULL, '153.156.188.172', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.1 Safari/605.1.15', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZFhndEhLUHNzNzNxUlNMdHUzUk95SUZIeU5wMVFremYyQmk4VmtROSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHBzOi8vYWtpb2thLXJlY2VwdGlvbi5jbG91ZC8/dj0yMDI1MTAwOSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1764723187),
('emEum88XhVNA2vOWwekHUpxtkRxJApZv0p7qoKQh', NULL, '162.216.150.49', 'Hello from Palo Alto Networks, find out more about our scans in https://docs-cortex.paloaltonetworks.com/r/1/Cortex-Xpanse/Scanning-activity', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQ2NJWGhaUW13TVJaTWxtZHBYdW8wcjMxbVlJSUN6MkxTRUZGVTN3MSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjI6Imh0dHBzOi8vMTMzLjExNy43NS4xMzYiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1764718208),
('ExSbwLMJLFcnILFuMccw36W90t7JMZtb9pUyMLY8', NULL, '47.89.154.16', 'Go-http-client/1.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidFcwajBYQmZQU2RVUjZjSTZLWWliTEVGNkE2WnFndVlQRVA5ejZ5SiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Njc6Imh0dHBzOi8vMTMzLjExNy43NS4xMzYvP2Rucz05c3NCQUFBQkFBQUFBQUFBQjJWNFlXMXdiR1VEWTI5dEFBQUJBQUUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1764718077),
('F0s2dCpHnfDR1ylRZTSVG3qLgpAgSQSa6XC1D6kG', NULL, '47.89.154.16', 'Go-http-client/1.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZ0lJc2gyZ3B2Q3Q1TXJvejVCTzhNREFpa01pcE55eEp4UzNIQkxHWSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHBzOi8vMTMzLjExNy43NS4xMzYvP25hbWU9ZXhhbXBsZS5jb20mdHlwZT1BIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1764718076),
('gms7IIiS8dEwGCXOEmLdK0OFqxL0TQIL3PQziGv2', NULL, '185.242.226.113', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.190 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQjNYY2duYmJ0Z0c2bFF4TDVibmFvUHlaa01VdWV1VXRvMkdLVzJlViI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzA6Imh0dHBzOi8vYWtpb2thLXJlY2VwdGlvbi5jbG91ZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1764717178),
('Jf0McKCKRR2KMDQkkf0kCkoEbgrBx1cfFsuMPGWZ', NULL, '47.89.154.16', 'Go-http-client/1.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYWxRTzB4VlFXVjVud3ZVRFhDNkx0dWdUNmtPa1E2U0hSQXZjTWFnNiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Njc6Imh0dHBzOi8vMTMzLjExNy43NS4xMzYvP2Rucz1QZGdCQUFBQkFBQUFBQUFBQjJWNFlXMXdiR1VEWTI5dEFBQUJBQUUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1764718075),
('joeUBXscrDtRuOO4rzasFLVGgsZdbghCncRD8yDj', NULL, '47.89.154.16', 'Go-http-client/1.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNFp1VHBPVHZTMjVDcUphWXVLS2hQd1IxTlptNHg4azQ0dkFCbXI3WSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHBzOi8vMTMzLjExNy43NS4xMzYvP25hbWU9ZXhhbXBsZS5jb20mdHlwZT1BIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1764718078),
('jtQC0KHPcMcuUThauFgaQQJMDUhm3NU4F6y1LWXf', NULL, '49.51.72.76', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNFNNdVd5czI2aTlXdzRjQ2o0QlVQdUg4OTdTWkNuZ2xZUXVzS2J3YSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzA6Imh0dHBzOi8vYWtpb2thLXJlY2VwdGlvbi5jbG91ZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1764724716),
('nSuUAnPBdxl2vlunkgVU17kNqcXd8HcyPUNWe46P', NULL, '153.156.188.172', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YToyOntzOjIwOiJsb2NhbFN0b3JhZ2VfdXNlcl9pZCI7aTo5MTtzOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1764724055),
('ogn8b4HLrxAmdMVfmTqB98KVn3gvQ0qtDcU0yw9i', NULL, '191.7.89.242', '', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoib2ZtYXBvbks0ZkpESTllc1RSb2xzSmc1VzNyeFJUamlYeWRGZHVyayI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjI6Imh0dHBzOi8vMTMzLjExNy43NS4xMzYiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1764725287),
('PE0AMo9eTuswC4DtqIhLNmOVjq17GRvedGPIs6j2', NULL, '92.203.108.195', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSnRWYVp5eDA3TENVSVhqOVJYeHdJaG02eWVFVnB4Y2lXUkVybHR6ZCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDU6Imh0dHBzOi8vYWtpb2thLXJlY2VwdGlvbi5jbG91ZC9kZWxpdmVyeS8zNS9xciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1764724041),
('Q66zHLRb6wmfkUltTbHtlkK3TFeiMhCNBo2hqq5s', NULL, '153.156.188.172', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUk9Mek1hSW42QnhnRUxQSnNjNkJwbmpHMGJQVEJQb2FrNklwZ0xNYiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDU6Imh0dHBzOi8vYWtpb2thLXJlY2VwdGlvbi5jbG91ZC9kZWxpdmVyeS8zNS9xciI7fXM6MjA6ImxvY2FsU3RvcmFnZV91c2VyX2lkIjtzOjI6IjkxIjt9', 1764724135),
('SbaEkwROGN8GCeVMzfyXptrVMWYeKa4yULv2CvEk', NULL, '45.156.129.48', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidUY0RENNTG1WYnFUTVc2WDN2bDRRRkRVaVRZcmk0SFFXVmh2NnhFNSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjI6Imh0dHBzOi8vMTMzLjExNy43NS4xMzYiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1764711714),
('SUtW3m6l3icxECQBjKongTKCWGapEJSR1RYPm39H', NULL, '43.131.26.226', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVEU0MEtUNmI1TDFEN3FmRXBpVld3V0ozeGZyNHRudGUxTHV2NndhYiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzA6Imh0dHBzOi8vYWtpb2thLXJlY2VwdGlvbi5jbG91ZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1764722068),
('t3leag4NqUtkgtYnIiQKRRmS0BlkEJCU6pU2tz41', NULL, '54.86.228.47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVnBYSFZXZU53YWdDQzh1ZjVDRkd1SXZsVVZsZ0tWZ1ZtZUlpd1BkNiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjI6Imh0dHBzOi8vMTMzLjExNy43NS4xMzYiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1764712652),
('wXtBf6T3psPbGUNqgdx4fmrQkiAQy3vC01rqduPs', NULL, '101.36.123.67', 'Mozilla/5.0 (Windows NT 7_1_1; Win64; x64) AppleWebKit/593.50 (KHTML, like Gecko) Chrome/85.0.1719 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNjF1VjVuMlRzN21ZS29Ga1dKdk5XbzBpajRMWFNBUXJxeXk5S1E4WCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzA6Imh0dHBzOi8vYWtpb2thLXJlY2VwdGlvbi5jbG91ZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1764715200);

-- --------------------------------------------------------

--
-- テーブルの構造 `staff_members`
--

CREATE TABLE `staff_members` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'ユーザーID（akioka_db.usersテーブル参照）',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- テーブルのデータのダンプ `staff_members`
--

INSERT INTO `staff_members` (`id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2025-10-06 11:42:37', '2025-10-06 11:42:37'),
(2, 91, '2025-10-06 11:45:33', '2025-10-06 11:45:33');

-- --------------------------------------------------------

--
-- テーブルの構造 `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `emp_no` varchar(6) NOT NULL COMMENT '社員No',
  `name` varchar(255) NOT NULL COMMENT '氏名',
  `password` text NOT NULL COMMENT 'パスワード',
  `email` varchar(255) DEFAULT NULL COMMENT 'メールアドレス',
  `gender_flg` tinyint(4) NOT NULL DEFAULT 0 COMMENT '性別 0:男性 1:女性',
  `group_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'グループID',
  `position_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '役職ID',
  `process_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'プロセスID',
  `is_admin` tinyint(4) NOT NULL DEFAULT 0 COMMENT '管理者フラグ',
  `dispatch_flg` tinyint(4) NOT NULL DEFAULT 0 COMMENT '派遣フラグ',
  `part_flg` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'パートフラグ',
  `always_order_flg` tinyint(4) NOT NULL DEFAULT 0 COMMENT '常時発注フラグ',
  `duty_flg` tinyint(4) NOT NULL DEFAULT 0 COMMENT '当直フラグ',
  `fax_folder_name` varchar(255) DEFAULT NULL COMMENT 'FAXフォルダ名',
  `del_flg` tinyint(4) NOT NULL DEFAULT 0 COMMENT '削除フラグ',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `visitors`
--

CREATE TABLE `visitors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `visitor_name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `business_card_image` varchar(255) DEFAULT NULL,
  `staff_member_id` bigint(20) UNSIGNED DEFAULT NULL,
  `group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `visitor_type` varchar(255) NOT NULL,
  `number_of_people` int(11) NOT NULL DEFAULT 1,
  `purpose` text DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `reception_number` int(11) DEFAULT NULL,
  `check_in_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `check_out_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- テーブルのデータのダンプ `visitors`
--

INSERT INTO `visitors` (`id`, `company_name`, `visitor_name`, `phone`, `business_card_image`, `staff_member_id`, `group_id`, `visitor_type`, `number_of_people`, `purpose`, `qr_code`, `reception_number`, `check_in_time`, `check_out_time`, `created_at`, `updated_at`) VALUES
(1, '株式会社アキオカ', '村上飛羽', NULL, NULL, 91, NULL, 'appointment', 1, NULL, NULL, 309, '2025-10-06 12:04:58', NULL, '2025-10-06 12:04:58', '2025-10-06 12:04:58'),
(2, '株式会社アキオカ', '村上飛羽', NULL, NULL, 91, NULL, 'appointment', 1, NULL, NULL, 309, '2025-10-06 12:17:08', NULL, '2025-10-06 12:17:08', '2025-10-06 12:17:08'),
(3, '77', 'ま', NULL, NULL, NULL, 7, 'other', 5, 'み', NULL, NULL, '2025-10-08 10:12:07', NULL, '2025-10-08 10:12:07', '2025-10-08 10:12:07'),
(4, '株式会社アキオカ', '村上飛羽', NULL, NULL, 91, NULL, 'appointment', 1, NULL, NULL, 7006, '2025-10-08 10:46:50', NULL, '2025-10-08 10:46:50', '2025-10-08 10:46:50'),
(5, '株式会社あきおか', '6', NULL, NULL, NULL, 7, 'other', 5, 'は', NULL, NULL, '2025-10-08 11:07:00', NULL, '2025-10-08 11:07:00', '2025-10-08 11:07:00'),
(6, 'ははは株式会社ひひ', 'みみみ', NULL, NULL, NULL, 7, 'other', 5, 'あああああ', NULL, NULL, '2025-10-08 13:07:18', NULL, '2025-10-08 13:07:18', '2025-10-08 13:07:18'),
(7, 'よ', 'ま', NULL, NULL, NULL, 7, 'other', 5, 'ふ', NULL, NULL, '2025-10-08 22:01:32', NULL, '2025-10-08 22:01:32', '2025-10-08 22:01:32'),
(8, '6', '6', NULL, NULL, NULL, 7, 'other', 2, '6', NULL, NULL, '2025-10-09 08:06:22', NULL, '2025-10-09 08:06:22', '2025-10-09 08:06:22'),
(9, '5', '6', NULL, NULL, NULL, 7, 'other', 2, '6', NULL, NULL, '2025-10-09 08:40:21', NULL, '2025-10-09 08:40:21', '2025-10-09 08:40:21');

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `appointments_reception_number_unique` (`reception_number`),
  ADD KEY `appointments_staff_member_id_foreign` (`staff_member_id`);

--
-- テーブルのインデックス `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deliveries_staff_member_id_foreign` (`staff_member_id`);

--
-- テーブルのインデックス `delivery_initial_order`
--
ALTER TABLE `delivery_initial_order`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `delivery_initial_order_delivery_id_initial_order_id_unique` (`delivery_id`,`initial_order_id`),
  ADD KEY `delivery_initial_order_initial_order_id_index` (`initial_order_id`);

--
-- テーブルのインデックス `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- テーブルのインデックス `interview_phones`
--
ALTER TABLE `interview_phones`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `notification_recipients`
--
ALTER TABLE `notification_recipients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notification_recipients_notification_setting_id_is_active_index` (`notification_setting_id`,`is_active`),
  ADD KEY `notification_recipients_user_id_notification_type_index` (`user_id`,`notification_type`);

--
-- テーブルのインデックス `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notification_settings_trigger_event_is_active_index` (`trigger_event`,`is_active`);

--
-- テーブルのインデックス `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`email`);

--
-- テーブルのインデックス `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- テーブルのインデックス `pickups`
--
ALTER TABLE `pickups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pickups_staff_member_id_foreign` (`staff_member_id`);

--
-- テーブルのインデックス `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- テーブルのインデックス `staff_members`
--
ALTER TABLE `staff_members`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_emp_no_unique` (`emp_no`);

--
-- テーブルのインデックス `visitors`
--
ALTER TABLE `visitors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `visitors_staff_member_id_foreign` (`staff_member_id`);

--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- テーブルの AUTO_INCREMENT `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- テーブルの AUTO_INCREMENT `delivery_initial_order`
--
ALTER TABLE `delivery_initial_order`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `interview_phones`
--
ALTER TABLE `interview_phones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- テーブルの AUTO_INCREMENT `notification_recipients`
--
ALTER TABLE `notification_recipients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- テーブルの AUTO_INCREMENT `notification_settings`
--
ALTER TABLE `notification_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- テーブルの AUTO_INCREMENT `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `pickups`
--
ALTER TABLE `pickups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- テーブルの AUTO_INCREMENT `staff_members`
--
ALTER TABLE `staff_members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- テーブルの AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `visitors`
--
ALTER TABLE `visitors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- ダンプしたテーブルの制約
--

--
-- テーブルの制約 `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_staff_member_id_foreign` FOREIGN KEY (`staff_member_id`) REFERENCES `staff_members` (`id`);

--
-- テーブルの制約 `delivery_initial_order`
--
ALTER TABLE `delivery_initial_order`
  ADD CONSTRAINT `delivery_initial_order_delivery_id_foreign` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`id`) ON DELETE CASCADE;

--
-- テーブルの制約 `notification_recipients`
--
ALTER TABLE `notification_recipients`
  ADD CONSTRAINT `notification_recipients_notification_setting_id_foreign` FOREIGN KEY (`notification_setting_id`) REFERENCES `notification_settings` (`id`) ON DELETE CASCADE;

--
-- テーブルの制約 `pickups`
--
ALTER TABLE `pickups`
  ADD CONSTRAINT `pickups_staff_member_id_foreign` FOREIGN KEY (`staff_member_id`) REFERENCES `staff_members` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
