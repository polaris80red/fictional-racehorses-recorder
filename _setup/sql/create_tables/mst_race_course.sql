SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `mst_race_course` (
  `id` int(11) NOT NULL COMMENT 'キー',
  `unique_name` varchar(32) NOT NULL COMMENT 'レース結果連携用名称',
  `short_name` varchar(5) DEFAULT NULL COMMENT '略名',
  `short_name_m` varchar(10) DEFAULT NULL COMMENT 'SP出馬表向け(国名2文字～)',
  `sort_priority` int(11) NOT NULL DEFAULT 0 COMMENT '表示順優先度',
  `sort_number` int(11) DEFAULT NULL COMMENT '表示順補正',
  `show_in_select_box` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1:セレクトボックスに表示する',
  `is_enabled` tinyint(4) NOT NULL DEFAULT 1 COMMENT '論理削除用',
  `created_by` int(11) DEFAULT NULL COMMENT '作成者',
  `updated_by` int(11) DEFAULT NULL COMMENT '最終更新者',
  `created_at` datetime DEFAULT NULL COMMENT '作成日時',
  `updated_at` datetime DEFAULT NULL COMMENT '最終更新日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='競馬場マスタ';


ALTER TABLE `mst_race_course`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name` (`unique_name`);


ALTER TABLE `mst_race_course`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'キー';
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
