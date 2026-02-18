ALTER TABLE `dat_race_results`
ADD `jra_thisweek_horse_26_title` TEXT NULL DEFAULT NULL COMMENT '今週の注目レース2026年フォーマットの見出し' AFTER `jra_thisweek_horse_sort_number`,
ADD `jra_thisweek_horse_26_1` TEXT NULL DEFAULT NULL COMMENT '今週の注目レース2026年フォーマットの本文' AFTER `jra_thisweek_horse_26_title`;
