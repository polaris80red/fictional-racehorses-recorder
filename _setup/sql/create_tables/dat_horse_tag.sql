SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `dat_horse_tag` (
  `number` int(11) NOT NULL COMMENT 'サロゲートキー',
  `horse_id` varchar(32) NOT NULL COMMENT '競走馬ID',
  `tag_text` varchar(100) NOT NULL COMMENT 'タグ文字列',
  `is_enabled` tinyint(4) NOT NULL DEFAULT 1 COMMENT '論理削除フラグ',
  `created_by` int(11) DEFAULT NULL COMMENT '作成者',
  `updated_by` int(11) DEFAULT NULL COMMENT '最終更新者',
  `created_at` datetime DEFAULT NULL COMMENT '初回登録日時',
  `updated_at` datetime DEFAULT NULL COMMENT '最終更新日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='競走馬タグ';


ALTER TABLE `dat_horse_tag`
  ADD PRIMARY KEY (`number`),
  ADD KEY `horse_id` (`horse_id`),
  ADD KEY `tag_text` (`tag_text`);


ALTER TABLE `dat_horse_tag`
  MODIFY `number` int(11) NOT NULL AUTO_INCREMENT COMMENT 'サロゲートキー';
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
