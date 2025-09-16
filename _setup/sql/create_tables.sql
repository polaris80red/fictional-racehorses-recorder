SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `dat_horse` (
  `horse_id` varchar(32) NOT NULL COMMENT 'ID',
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
  `sire_id` varchar(32) NOT NULL DEFAULT '' COMMENT '父ID',
  `sire_name` varchar(18) NOT NULL DEFAULT '' COMMENT '父名',
  `mare_id` varchar(32) NOT NULL DEFAULT '' COMMENT '母ID',
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

CREATE TABLE `dat_race` (
  `race_id` varchar(32) NOT NULL,
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
  `age` varchar(2) DEFAULT NULL COMMENT '年齢条件',
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

CREATE TABLE `dat_race_results` (
  `number` int(11) NOT NULL COMMENT 'サロゲートキー',
  `race_id` varchar(32) NOT NULL,
  `horse_id` varchar(32) NOT NULL,
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

CREATE TABLE `log_article_counter` (
  `id` int(11) NOT NULL COMMENT 'サロゲートキー',
  `article_type` varchar(50) NOT NULL COMMENT '記事などの種類',
  `article_id` varchar(32) NOT NULL COMMENT '記事などのキー（競走馬ID・レースID）',
  `view_count` int(11) NOT NULL DEFAULT 0 COMMENT '表示回数',
  `created_at` datetime NOT NULL COMMENT '新規登録日時',
  `updated_at` datetime NOT NULL COMMENT '最終更新日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `mst_affiliation` (
  `id` int(11) NOT NULL COMMENT 'ID',
  `unique_name` varchar(10) NOT NULL COMMENT 'キー名称',
  `sort_number` int(11) DEFAULT NULL COMMENT '表示順補正値',
  `show_in_select_box` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1:セレクトボックスに表示する',
  `is_enabled` tinyint(4) NOT NULL DEFAULT 1 COMMENT '論理削除用',
  `created_by` int(11) DEFAULT NULL COMMENT '作成者',
  `updated_by` int(11) DEFAULT NULL COMMENT '最終更新者',
  `created_at` datetime DEFAULT NULL COMMENT '作成日時',
  `updated_at` datetime DEFAULT NULL COMMENT '最終更新日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='所属マスタ';

CREATE TABLE `mst_jockey` (
  `id` int(11) NOT NULL COMMENT 'サロゲートキー',
  `unique_name` varchar(32) NOT NULL COMMENT '連携用キー名',
  `name` text DEFAULT NULL COMMENT '表示氏名',
  `short_name_10` varchar(10) DEFAULT NULL COMMENT '出馬表等10字以内略',
  `affiliation_name` varchar(10) DEFAULT NULL COMMENT '所属',
  `trainer_name` varchar(32) DEFAULT NULL COMMENT '厩舎',
  `is_anonymous` tinyint(4) NOT NULL DEFAULT 0 COMMENT '1:通常閲覧時は非表示の管理用のレコード',
  `is_enabled` tinyint(4) NOT NULL DEFAULT 1 COMMENT '論理削除フラグ',
  `created_by` int(11) DEFAULT NULL COMMENT '作成者',
  `updated_by` int(11) DEFAULT NULL COMMENT '最終更新者',
  `created_at` datetime DEFAULT NULL COMMENT '作成日時',
  `updated_at` datetime DEFAULT NULL COMMENT '最終更新日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

CREATE TABLE `mst_race_category_sex` (
  `id` int(11) NOT NULL COMMENT 'キー',
  `name` varchar(100) NOT NULL COMMENT '名称',
  `short_name_3` varchar(3) NOT NULL DEFAULT '' COMMENT '3文字略称',
  `umm_category` varchar(16) DEFAULT NULL COMMENT '擬人化用',
  `sort_number` int(11) DEFAULT NULL COMMENT '表示順補正',
  `is_enabled` tinyint(4) NOT NULL DEFAULT 1 COMMENT '論理削除用',
  `created_by` int(11) DEFAULT NULL COMMENT '作成者',
  `updated_by` int(11) DEFAULT NULL COMMENT '最終更新者',
  `created_at` datetime DEFAULT NULL COMMENT '作成日時',
  `updated_at` datetime DEFAULT NULL COMMENT '最終更新日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='レース性別条件マスタ';

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

CREATE TABLE `mst_race_week` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `month` tinyint(4) NOT NULL,
  `month_grouping` int(11) NOT NULL,
  `umm_month_turn` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'ウマ娘での所属月内の前後（0予備、1前半、2後半、3後半予備）',
  `sort_number` int(11) DEFAULT NULL,
  `is_enabled` tinyint(4) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL COMMENT '作成者',
  `updated_by` int(11) DEFAULT NULL COMMENT '最終更新者',
  `created_at` datetime DEFAULT NULL COMMENT '作成日時',
  `updated_at` datetime DEFAULT NULL COMMENT '最終更新日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='レース開催週マスタ';

CREATE TABLE `mst_themes` (
  `id` int(11) NOT NULL COMMENT 'id',
  `name` varchar(32) NOT NULL COMMENT '名称',
  `dir_name` text NOT NULL COMMENT 'テーマディレクトリ名',
  `sort_priority` int(11) NOT NULL DEFAULT 0 COMMENT '表示順優先度',
  `sort_number` int(11) DEFAULT NULL COMMENT '表示順補正',
  `is_enabled` tinyint(4) NOT NULL DEFAULT 1 COMMENT '論理削除用',
  `created_by` int(11) DEFAULT NULL COMMENT '作成者',
  `updated_by` int(11) DEFAULT NULL COMMENT '最終更新者',
  `created_at` datetime DEFAULT NULL COMMENT '作成日時',
  `updated_at` datetime DEFAULT NULL COMMENT '最終更新日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='テーママスタ';

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

CREATE TABLE `mst_world` (
  `id` int(11) NOT NULL COMMENT 'キー',
  `name` text NOT NULL COMMENT '名称',
  `guest_visible` tinyint(4) NOT NULL DEFAULT 1 COMMENT 'ログインしていないユーザーにも表示する',
  `auto_id_prefix` text NOT NULL DEFAULT '' COMMENT '自動IDの接頭語',
  `sort_priority` int(11) NOT NULL DEFAULT 0 COMMENT '表示順優先度',
  `is_enabled` tinyint(4) NOT NULL DEFAULT 1 COMMENT '論理削除用',
  `created_by` int(11) DEFAULT NULL COMMENT '作成者',
  `updated_by` int(11) DEFAULT NULL COMMENT '最終更新者',
  `created_at` datetime DEFAULT NULL COMMENT '作成日時',
  `updated_at` datetime DEFAULT NULL COMMENT '最終更新日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='ワールド';

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

CREATE TABLE `sys_config` (
  `id` int(11) NOT NULL,
  `config_key` varchar(100) NOT NULL,
  `config_value` varchar(100) NOT NULL DEFAULT '',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='デフォルト表示設定';

CREATE TABLE `sys_surrogate_key_generator` (
  `id` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='ユニークキー採番テーブル';


ALTER TABLE `dat_horse`
  ADD UNIQUE KEY `horse_id` (`horse_id`);

ALTER TABLE `dat_horse_tag`
  ADD PRIMARY KEY (`number`),
  ADD KEY `horse_id` (`horse_id`),
  ADD KEY `tag_text` (`tag_text`);

ALTER TABLE `dat_race`
  ADD UNIQUE KEY `race_id` (`race_id`);

ALTER TABLE `dat_race_results`
  ADD PRIMARY KEY (`number`),
  ADD UNIQUE KEY `race_results_horse` (`race_id`,`horse_id`),
  ADD KEY `race_results_id` (`race_id`),
  ADD KEY `horse_id` (`horse_id`);

ALTER TABLE `log_article_counter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_type_id` (`article_type`,`article_id`);

ALTER TABLE `mst_affiliation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name` (`unique_name`);

ALTER TABLE `mst_jockey`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name` (`unique_name`);

ALTER TABLE `mst_race_category_age`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mst_race_category_sex`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mst_race_course`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name` (`unique_name`);

ALTER TABLE `mst_race_grade`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `race_results_key` (`unique_name`);

ALTER TABLE `mst_race_special_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name` (`unique_name`);

ALTER TABLE `mst_race_week`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mst_themes`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mst_trainer`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name` (`unique_name`);

ALTER TABLE `mst_world`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mst_world_story`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `sys_config`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `sys_surrogate_key_generator`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `dat_horse_tag`
  MODIFY `number` int(11) NOT NULL AUTO_INCREMENT COMMENT 'サロゲートキー';

ALTER TABLE `dat_race_results`
  MODIFY `number` int(11) NOT NULL AUTO_INCREMENT COMMENT 'サロゲートキー';

ALTER TABLE `log_article_counter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'サロゲートキー';

ALTER TABLE `mst_affiliation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID';

ALTER TABLE `mst_jockey`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'サロゲートキー';

ALTER TABLE `mst_race_category_age`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'キー';

ALTER TABLE `mst_race_course`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'キー';

ALTER TABLE `mst_race_grade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `mst_race_special_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'サロゲートキー';

ALTER TABLE `mst_themes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id';

ALTER TABLE `mst_trainer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'サロゲートキー';

ALTER TABLE `mst_world`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'キー';

ALTER TABLE `mst_world_story`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'キー';

ALTER TABLE `sys_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `sys_surrogate_key_generator`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
