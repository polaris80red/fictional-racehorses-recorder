SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `mst_world_story` (
  `id` int(11) NOT NULL COMMENT 'キー',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '名称',
  `guest_visible` tinyint(4) NOT NULL DEFAULT 1 COMMENT 'ログインユーザー以外でも表示',
  `config_json` text NOT NULL DEFAULT '' COMMENT '表示設定JSON',
  `sort_priority` int(11) NOT NULL DEFAULT 0 COMMENT '表示順優先度',
  `sort_number` int(11) DEFAULT NULL COMMENT '表示順補正',
  `is_read_only` tinyint(4) NOT NULL DEFAULT 0 COMMENT '読取り専用',
  `is_enabled` tinyint(4) NOT NULL DEFAULT 1 COMMENT '論理削除用',
  `created_by` int(11) DEFAULT NULL COMMENT '作成者',
  `updated_by` int(11) DEFAULT NULL COMMENT '最終更新者',
  `created_at` datetime DEFAULT NULL COMMENT '作成日時',
  `updated_at` datetime DEFAULT NULL COMMENT '最終更新日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='ストーリー設定';


ALTER TABLE `mst_world_story`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `mst_world_story`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'キー';
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
