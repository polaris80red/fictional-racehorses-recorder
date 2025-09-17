SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `dat_race` (
  `race_id` varchar(100) NOT NULL,
  `world_id` int(11) DEFAULT NULL COMMENT 'ワールドID',
  `race_course_name` varchar(32) NOT NULL,
  `race_number` int(11) DEFAULT NULL,
  `course_type` varchar(2) NOT NULL,
  `distance` int(11) NOT NULL,
  `race_name` varchar(100) NOT NULL,
  `race_short_name` varchar(20) NOT NULL DEFAULT '' COMMENT '略名',
  `caption` text NOT NULL DEFAULT '' COMMENT '補足情報',
  `grade` varchar(5) NOT NULL COMMENT 'グレード',
  `age_category_id` int(11) NOT NULL DEFAULT 0 COMMENT '年齢条件ID',
  `age` varchar(50) DEFAULT NULL COMMENT '年齢条件',
  `sex_category_id` int(11) NOT NULL DEFAULT 0 COMMENT '性別条件ID',
  `weather` varchar(10) DEFAULT NULL COMMENT '天候',
  `track_condition` text NOT NULL DEFAULT '' COMMENT '馬場状態',
  `note` text NOT NULL DEFAULT '' COMMENT '備考',
  `previous_note` text NOT NULL DEFAULT '' COMMENT 'レース前メモ',
  `after_note` text NOT NULL DEFAULT '' COMMENT 'レース後メモ',
  `number_of_starters` tinyint(4) DEFAULT NULL COMMENT '出走頭数',
  `is_jra` tinyint(4) NOT NULL DEFAULT 1 COMMENT '中央競馬',
  `is_nar` tinyint(4) NOT NULL DEFAULT 0 COMMENT '地方競馬',
  `date` date DEFAULT NULL,
  `is_tmp_date` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1:仮日付',
  `year` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `week_id` int(11) NOT NULL DEFAULT 0 COMMENT 'レース週ID',
  `is_enabled` tinyint(4) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL COMMENT '作成者',
  `updated_by` int(11) DEFAULT NULL COMMENT '最終更新者',
  `created_at` datetime DEFAULT NULL COMMENT '作成日時',
  `updated_at` datetime DEFAULT NULL COMMENT '最終更新日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='レース情報';


ALTER TABLE `dat_race`
  ADD UNIQUE KEY `race_id` (`race_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
