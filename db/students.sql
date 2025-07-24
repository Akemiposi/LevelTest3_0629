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
-- テーブルの構造 `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `school` varchar(100) NOT NULL,
  `grade` varchar(10) NOT NULL,
  `class` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `language_code` varchar(10) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `teacher_id` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `students`
--

INSERT INTO `students` (`id`, `school`, `grade`, `class`, `name`, `gender`, `language_code`, `student_id`, `teacher_id`) VALUES
(1, 'ひまわり幼稚園', '年長', '1', 'サラ・アフマド', '女', 'ar', 'STU001', 'TCH003'),
(2, 'ひまわり幼稚園', '年中', '2', 'グエン・アン', '男', 'vi', 'STU002', 'TCH002'),
(3, 'ひまわり幼稚園', '年少', '1', 'キム・ミナ', '女', 'ko', 'STU003', 'TCH006'),
(4, 'さくら小学校', '1', '1', '田中はると', '男', 'ja', 'STU004', 'TCH001'),
(5, 'さくら小学校', '2', '2', 'チャン・ミン', '女', 'zh', 'STU005', 'TCH004'),
(6, 'さくら小学校', '3', '3', 'マリア・ロペス', '女', 'es', 'STU006', 'TCH007'),
(7, 'さくら小学校', '4', '1', 'アハメド・ユーセフ', '男', 'fa', 'STU007', 'TCH009'),
(8, 'みどり小学校', '5', '2', 'リ・シャオミン', '男', 'zh-TW', 'STU008', 'TCH004'),
(9, 'みどり小学校', '6', '1', 'アイシャ・ハーン', '女', 'ur', 'STU009', 'TCH010'),
(10, 'つばさ中学校', '1', '3', 'スミス・ジョン', '男', 'en', 'STU010', 'TCH005'),
(11, 'つばさ中学校', '2', '2', 'ローラ・デュボア', '女', 'fr', 'STU011', 'TCH010'),
(12, 'つばさ中学校', '3', '1', 'パウロ・サントス', '男', 'pt', 'STU012', 'TCH010'),
(13, 'さつき中学校', '1', '1', 'ヌグロホ・リアン', '男', 'id', 'STU013', 'TCH010'),
(14, 'さつき中学校', '2', '3', 'エミリア・ポポフ', '女', 'ru', 'STU014', 'TCH010'),
(15, 'さつき中学校', '3', '2', 'アミール・サイード', '男', 'hi', 'STU015', 'TCH010'),
(16, 'すみれ幼稚園', '年長', '2', 'アイ・レイナ', '女', 'my', 'STU016', 'TCH010'),
(17, 'すみれ幼稚園', '年中', '1', 'ジャン・ティエン', '男', 'zh', 'STU017', 'TCH004'),
(18, 'すみれ幼稚園', '年少', '1', 'アリサ・リー', '女', 'ja', 'STU018', 'TCH001'),
(19, 'あおば小学校', '1', '1', 'タナカ・ケンタ', '男', 'ja', 'STU019', 'TCH001'),
(20, 'あおば小学校', '2', '2', 'マイ・グエン', '女', 'vi', 'STU020', 'TCH002');

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
