-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: localhost
-- 生成日時: 2025 年 7 月 21 日 09:02
-- サーバのバージョン： 10.4.28-MariaDB
-- PHP のバージョン: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `gs_db_leveltest3`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `admin_teacher`
--

CREATE TABLE `admin_teacher` (
  `id` int(11) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `language_code` varchar(10) DEFAULT NULL,
  `role` enum('管理者','先生') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `admin_teacher`
--

INSERT INTO `admin_teacher` (`id`, `user_id`, `name`, `email`, `password_hash`, `language_code`, `role`) VALUES
(1, 'ADM001', '近岡 明美', 'a.chikaoka@cecgaigo.jp', '88d39c135f39c46b5160a1620ac1cc2c3b2fa5cda85c34c80f3e1f3c9734c6d6', NULL, '管理者'),
(2, 'ADM002', '星野 大河', 'hoshino@cecgaigo.jp', '88d39c135f39c46b5160a1620ac1cc2c3b2fa5cda85c34c80f3e1f3c9734c6d6', NULL, '管理者'),
(3, 'TCH001', '山田 花子', 'hanako.yamada@example.com', '88d39c135f39c46b5160a1620ac1cc2c3b2fa5cda85c34c80f3e1f3c9734c6d6', 'ja', '先生'),
(4, 'TCH002', '佐藤 健', 'takeshi.sato@example.com', '88d39c135f39c46b5160a1620ac1cc2c3b2fa5cda85c34c80f3e1f3c9734c6d6', 'vi', '先生'),
(5, 'TCH003', 'アリ・ファティマ', 'fatima.ali@example.com', '88d39c135f39c46b5160a1620ac1cc2c3b2fa5cda85c34c80f3e1f3c9734c6d6', 'ar', '先生'),
(6, 'TCH004', '鈴木 真央', 'mao.suzuki@example.com', '88d39c135f39c46b5160a1620ac1cc2c3b2fa5cda85c34c80f3e1f3c9734c6d6', 'zh', '先生'),
(7, 'TCH005', 'ジョン・スミス', 'john.smith@example.com', '88d39c135f39c46b5160a1620ac1cc2c3b2fa5cda85c34c80f3e1f3c9734c6d6', 'en', '先生'),
(8, 'TCH006', 'キム・ソヨン', 'soyeon.kim@example.com', '88d39c135f39c46b5160a1620ac1cc2c3b2fa5cda85c34c80f3e1f3c9734c6d6', 'ko', '先生'),
(9, 'TCH007', 'マリア・ゴメス', 'maria.gomez@example.com', '88d39c135f39c46b5160a1620ac1cc2c3b2fa5cda85c34c80f3e1f3c9734c6d6', 'es', '先生'),
(10, 'TCH008', 'グエン・ラン', 'lan.nguyen@example.com', '88d39c135f39c46b5160a1620ac1cc2c3b2fa5cda85c34c80f3e1f3c9734c6d6', 'vi', '先生'),
(11, 'TCH009', 'アミール・レザ', 'amir.reza@example.com', '88d39c135f39c46b5160a1620ac1cc2c3b2fa5cda85c34c80f3e1f3c9734c6d6', 'fa', '先生'),
(12, 'TCH010', 'リ・ウェイ', 'wei.li@example.com', '88d39c135f39c46b5160a1620ac1cc2c3b2fa5cda85c34c80f3e1f3c9734c6d6', 'zh-TW', '先生');

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `admin_teacher`
--
ALTER TABLE `admin_teacher`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `admin_teacher`
--
ALTER TABLE `admin_teacher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
