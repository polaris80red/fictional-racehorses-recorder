SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


INSERT INTO `mst_race_course` (`id`, `unique_name`, `short_name`, `short_name_m`, `sort_priority`, `sort_number`, `show_in_select_box`, `is_enabled`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, '東京', '東京', '', 1000, 1, 1, 1, NULL, NULL, NULL, NULL),
(2, '中山', '中山', '', 1000, 2, 1, 1, NULL, NULL, NULL, NULL),
(3, '京都', '京都', '', 1000, 3, 1, 1, NULL, NULL, NULL, NULL),
(4, '阪神', '阪神', '', 1000, 4, 1, 1, NULL, NULL, NULL, NULL),
(5, '中京', '中京', '', 1000, 5, 1, 1, NULL, NULL, NULL, NULL),
(6, '札幌', '札幌', '', 1000, 6, 1, 1, NULL, NULL, NULL, NULL),
(7, '函館', '函館', '', 1000, 7, 1, 1, NULL, NULL, NULL, NULL),
(8, '福島', '福島', '', 1000, 8, 1, 1, NULL, NULL, NULL, NULL),
(9, '新潟', '新潟', '', 1000, 9, 1, 1, NULL, NULL, NULL, NULL),
(10, '小倉', '小倉', '', 1000, 10, 1, 1, NULL, NULL, NULL, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
