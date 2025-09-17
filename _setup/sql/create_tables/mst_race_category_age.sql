SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `mst_race_category_age` (
  `id` int(11) NOT NULL COMMENT 'キー',
  `search_id` int(11) NOT NULL DEFAULT 0 COMMENT '検索チェックボックスの対応（20:2歳, 30:3歳, 31:3上, 41:4上）',
  `name` varchar(100) NOT NULL COMMENT '名称',
  `short_name_2` varchar(2) NOT NULL COMMENT '2文字略名',
  `name_umamusume` text NOT NULL DEFAULT '',
  `sort_number` int(11) DEFAULT NULL COMMENT '表示順補正',
  `is_enabled` tinyint(4) NOT NULL DEFAULT 1 COMMENT '論理削除',
  `created_by` int(11) DEFAULT NULL COMMENT '作成者',
  `updated_by` int(11) DEFAULT NULL COMMENT '最終更新者',
  `created_at` datetime DEFAULT NULL COMMENT '作成日時',
  `updated_at` datetime DEFAULT NULL COMMENT '最終更新日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='レース年齢条件マスタ';


ALTER TABLE `mst_race_category_age`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `mst_race_category_age`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'キー';
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
