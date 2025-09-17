SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `mst_race_grade` (
  `id` int(11) NOT NULL,
  `unique_name` varchar(5) NOT NULL COMMENT '連携用名称',
  `short_name` varchar(8) DEFAULT NULL,
  `search_grade` varchar(8) NOT NULL COMMENT 'レース条件用の格付名',
  `category` text NOT NULL COMMENT '結果画面などの大分類',
  `css_class` varchar(50) DEFAULT NULL,
  `sort_number` int(11) DEFAULT NULL,
  `show_in_select_box` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1:セレクトボックスに表示する',
  `is_enabled` tinyint(4) NOT NULL DEFAULT 1 COMMENT '論理削除用',
  `created_by` int(11) DEFAULT NULL COMMENT '作成者',
  `updated_by` int(11) DEFAULT NULL COMMENT '最終更新者',
  `created_at` datetime DEFAULT NULL COMMENT '作成日時',
  `updated_at` datetime DEFAULT NULL COMMENT '最終更新日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='レース格付マスタ';


ALTER TABLE `mst_race_grade`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `race_results_key` (`unique_name`);


ALTER TABLE `mst_race_grade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
