SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `dat_horse` (
  `horse_id` varchar(100) NOT NULL COMMENT 'ID',
  `world_id` int(11) NOT NULL DEFAULT 0 COMMENT 'ワールドID',
  `name_ja` varchar(18) NOT NULL COMMENT '日本語名',
  `name_en` varchar(18) NOT NULL DEFAULT '' COMMENT '英字名',
  `birth_year` int(11) DEFAULT NULL COMMENT '生年',
  `sex` int(11) NOT NULL DEFAULT 0 COMMENT '性別',
  `color` varchar(3) DEFAULT NULL COMMENT '毛色',
  `tc` varchar(10) NOT NULL DEFAULT '' COMMENT '所属',
  `trainer_name` varchar(32) DEFAULT NULL COMMENT '調教師（unique_name）',
  `training_country` varchar(3) DEFAULT 'JPN' COMMENT '調教国',
  `owner_name` varchar(50) DEFAULT NULL COMMENT '馬主',
  `breeder_name` varchar(50) DEFAULT NULL COMMENT '生産者',
  `breeding_country` varchar(3) DEFAULT NULL COMMENT '生産国',
  `is_affliationed_nar` tinyint(4) NOT NULL DEFAULT 0 COMMENT '[地]地方所属',
  `sire_id` varchar(100) NOT NULL COMMENT '父ID',
  `sire_name` varchar(18) NOT NULL DEFAULT '' COMMENT '父名',
  `mare_id` varchar(100) NOT NULL COMMENT '母ID',
  `mare_name` varchar(18) NOT NULL DEFAULT '' COMMENT '母名',
  `bms_name` varchar(18) NOT NULL DEFAULT '' COMMENT '母父名',
  `is_sire_or_dam` tinyint(4) NOT NULL DEFAULT 0 COMMENT '種牡馬または繁殖牝馬',
  `meaning` varchar(100) NOT NULL DEFAULT '' COMMENT '馬名意味',
  `note` text NOT NULL COMMENT '備考',
  `profile` text DEFAULT NULL COMMENT 'プロフィール',
  `is_enabled` tinyint(4) NOT NULL DEFAULT 1 COMMENT '論理削除用',
  `created_by` int(11) DEFAULT NULL COMMENT '作成者',
  `updated_by` int(11) DEFAULT NULL COMMENT '最終更新者',
  `created_at` datetime DEFAULT NULL COMMENT '作成日時',
  `updated_at` datetime DEFAULT NULL COMMENT '最終更新日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='競走馬データ';


ALTER TABLE `dat_horse`
  ADD UNIQUE KEY `horse_id` (`horse_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
