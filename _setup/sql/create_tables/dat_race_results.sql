SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `dat_race_results` (
  `number` int(11) NOT NULL COMMENT 'サロゲートキー',
  `race_id` varchar(100) NOT NULL,
  `horse_id` varchar(100) NOT NULL,
  `result_number` int(11) DEFAULT NULL,
  `result_order` int(11) DEFAULT NULL COMMENT '着順表示順',
  `result_before_demotion` tinyint(4) NOT NULL DEFAULT 0 COMMENT '降着馬の場合のみ入線順',
  `result_text` varchar(8) NOT NULL COMMENT '結果値代替テキスト',
  `frame_number` tinyint(4) DEFAULT NULL COMMENT '枠番',
  `horse_number` tinyint(4) DEFAULT NULL COMMENT '馬番',
  `jockey_name` varchar(32) DEFAULT NULL COMMENT '騎手(ユニーク名)',
  `handicap` varchar(4) DEFAULT '' COMMENT '斤量(kg)',
  `time` varchar(10) DEFAULT NULL COMMENT 'タイム',
  `margin` varchar(5) NOT NULL DEFAULT '' COMMENT '着差',
  `corner_1` tinyint(4) DEFAULT NULL COMMENT 'コーナー通過順位1',
  `corner_2` tinyint(4) DEFAULT NULL COMMENT 'コーナー通過順位2',
  `corner_3` tinyint(4) DEFAULT NULL COMMENT 'コーナー通過順位3',
  `corner_4` tinyint(4) DEFAULT NULL COMMENT 'コーナー通過順位4',
  `f_time` varchar(4) DEFAULT NULL COMMENT '上り3f(平地)／平均1f(障害)',
  `h_weight` int(11) DEFAULT NULL COMMENT '馬体重',
  `odds` varchar(6) DEFAULT NULL COMMENT 'オッズ',
  `favourite` tinyint(4) DEFAULT NULL COMMENT '単勝人気',
  `earnings` int(11) DEFAULT NULL COMMENT '本賞金(万円)',
  `syuutoku` int(11) DEFAULT NULL COMMENT '収得賞金(万円)',
  `sex` int(11) NOT NULL DEFAULT 0 COMMENT '性別',
  `tc` varchar(10) DEFAULT NULL COMMENT '所属',
  `trainer_name` varchar(32) DEFAULT NULL COMMENT '調教師（unique_name）',
  `training_country` varchar(10) DEFAULT 'JPN' COMMENT '調教国',
  `owner_name` varchar(50) DEFAULT NULL COMMENT 'レース時点の馬主名',
  `is_affliationed_nar` tinyint(4) NOT NULL DEFAULT 0 COMMENT '[地]地方所属',
  `non_registered_prev_race_number` int(11) NOT NULL DEFAULT 0 COMMENT '未登録の前走の数',
  `race_previous_note` text NOT NULL DEFAULT '' COMMENT 'レース前メモ',
  `race_after_note` text NOT NULL DEFAULT '' COMMENT 'レース後メモ',
  `jra_thisweek_horse_1` text DEFAULT NULL COMMENT '出走馬情報(火)(ここに注目)',
  `jra_thisweek_horse_2` text DEFAULT NULL COMMENT '出走馬情報(木)',
  `jra_thisweek_horse_sort_number` tinyint(4) DEFAULT NULL COMMENT '出走馬情報表示順',
  `jra_sps_comment` text DEFAULT NULL COMMENT 'スペシャル出馬表',
  `is_enabled` tinyint(4) NOT NULL DEFAULT 1 COMMENT '論理削除用フラグ',
  `created_by` int(11) DEFAULT NULL COMMENT '作成者',
  `updated_by` int(11) DEFAULT NULL COMMENT '最終更新者',
  `created_at` datetime DEFAULT NULL COMMENT '作成日時',
  `updated_at` datetime DEFAULT NULL COMMENT '最終更新日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='レース個別結果データ';


ALTER TABLE `dat_race_results`
  ADD PRIMARY KEY (`number`),
  ADD UNIQUE KEY `race_results_horse` (`race_id`,`horse_id`),
  ADD KEY `race_results_id` (`race_id`),
  ADD KEY `horse_id` (`horse_id`);


ALTER TABLE `dat_race_results`
  MODIFY `number` int(11) NOT NULL AUTO_INCREMENT COMMENT 'サロゲートキー';
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
