SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


INSERT INTO `mst_race_grade` (`id`, `unique_name`, `short_name`, `search_grade`, `category`, `css_class`, `sort_number`, `show_in_select_box`, `is_enabled`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'G1', 'ＧⅠ', 'G1', 'オープン', 'race_grade_1', 1, 1, 1, NULL, NULL, NULL, NULL),
(2, 'G2', 'ＧⅡ', 'G2', 'オープン', 'race_grade_2', 2, 1, 1, NULL, NULL, NULL, NULL),
(3, 'G3', 'ＧⅢ', 'G3', 'オープン', 'race_grade_3', 3, 1, 1, NULL, NULL, NULL, NULL),
(4, '重賞', '重賞', '重賞', 'オープン', 'race_grade_4', 4, 1, 1, NULL, NULL, NULL, NULL),
(5, 'L', 'L', 'L', 'オープン', 'race_grade_l', 5, 1, 1, NULL, NULL, NULL, NULL),
(6, 'OP', 'OP', 'OP', 'オープン', 'race_grade_op', 6, 1, 1, NULL, NULL, NULL, NULL),
(7, '3勝', '3勝', '3勝', '3勝クラス', 'race_grade_none', 7, 1, 1, NULL, NULL, NULL, NULL),
(8, '2勝', '2勝', '2勝', '2勝クラス', 'race_grade_none', 8, 1, 1, NULL, NULL, NULL, NULL),
(9, '1勝', '1勝', '1勝', '1勝クラス', 'race_grade_none', 9, 1, 1, NULL, NULL, NULL, NULL),
(10, '未勝', '未勝', '未勝', '未勝利', 'race_grade_none', 10, 1, 1, NULL, NULL, NULL, NULL),
(11, '新馬', '新馬', '新馬', '新馬', 'race_grade_none', 11, 1, 1, NULL, NULL, NULL, NULL),
(21, 'JG1', 'JGⅠ', 'G1', 'オープン', 'race_grade_1', 21, 0, 1, NULL, NULL, NULL, NULL),
(22, 'JG2', 'JGⅡ', 'G2', 'オープン', 'race_grade_2', 22, 0, 1, NULL, NULL, NULL, NULL),
(23, 'JG3', 'JGⅢ', 'G3', 'オープン', 'race_grade_3', 23, 0, 1, NULL, NULL, NULL, NULL),
(101, 'Jpn1', 'JpnⅠ', 'G1', 'オープン', 'race_grade_1', 101, 0, 1, NULL, NULL, NULL, NULL),
(102, 'Jpn2', 'JpnⅡ', 'G2', 'オープン', 'race_grade_2', 102, 0, 1, NULL, NULL, NULL, NULL),
(103, 'Jpn3', 'JpnⅢ', 'G3', 'オープン', 'race_grade_3', 103, 0, 1, NULL, NULL, NULL, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
