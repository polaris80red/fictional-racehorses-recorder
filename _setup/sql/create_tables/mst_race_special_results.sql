SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `mst_race_special_results` (
  `id` int(11) NOT NULL COMMENT 'サロゲートキー',
  `unique_name` varchar(8) NOT NULL COMMENT 'キー用ユニーク名',
  `name` text NOT NULL DEFAULT '',
  `short_name_2` varchar(2) NOT NULL COMMENT '2文字略称',
  `is_registration_only` tinyint(4) NOT NULL DEFAULT 1 COMMENT '登録のみで未出走',
  `is_excluded_from_race_count` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1:レース数にカウントしない',
  `sort_number` int(11) DEFAULT NULL COMMENT '表示順補正値',
  `is_enabled` tinyint(4) NOT NULL DEFAULT 1 COMMENT '論理削除用フラグ',
  `created_by` int(11) DEFAULT NULL COMMENT '作成者',
  `updated_by` int(11) DEFAULT NULL COMMENT '最終更新者',
  `created_at` datetime DEFAULT NULL COMMENT '作成日時',
  `updated_at` datetime DEFAULT NULL COMMENT '最終更新日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='レース特殊結果マスタ';


ALTER TABLE `mst_race_special_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name` (`unique_name`);


ALTER TABLE `mst_race_special_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'サロゲートキー';
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
