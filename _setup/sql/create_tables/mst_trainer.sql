SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `mst_trainer` (
  `id` int(11) NOT NULL COMMENT 'サロゲートキー',
  `unique_name` varchar(32) NOT NULL COMMENT '連携用キー名',
  `name` text DEFAULT NULL COMMENT '表示氏名',
  `short_name_10` varchar(10) DEFAULT NULL COMMENT '出馬表等10字以内略',
  `affiliation_name` varchar(10) DEFAULT NULL COMMENT '所属',
  `is_anonymous` tinyint(4) NOT NULL DEFAULT 0 COMMENT '1:通常閲覧時は非表示の管理用のレコード',
  `is_enabled` tinyint(4) NOT NULL DEFAULT 1 COMMENT '論理削除フラグ',
  `created_by` int(11) DEFAULT NULL COMMENT '作成者',
  `updated_by` int(11) DEFAULT NULL COMMENT '最終更新者',
  `created_at` datetime DEFAULT NULL COMMENT '作成日時',
  `updated_at` datetime DEFAULT NULL COMMENT '最終更新日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `mst_trainer`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name` (`unique_name`);


ALTER TABLE `mst_trainer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'サロゲートキー';
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
