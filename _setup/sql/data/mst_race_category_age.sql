SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


INSERT INTO `mst_race_category_age` (`id`, `search_id`, `name`, `short_name_2`, `name_umamusume`, `sort_number`, `is_enabled`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(20, 20, '2歳', '2歳', 'ジュニア', 1, 1, NULL, NULL, NULL, NULL),
(21, 21, '2歳以上', '2上', 'ジュニア以上', NULL, 1, NULL, NULL, NULL, NULL),
(30, 30, '3歳', '3歳', 'クラシック', 2, 1, NULL, NULL, NULL, NULL),
(31, 31, '3歳以上', '3上', 'クラシック・シニア', 3, 1, NULL, NULL, NULL, NULL),
(40, 40, '4歳', '4歳', '香港クラシック', NULL, 1, NULL, NULL, NULL, NULL),
(41, 41, '4歳以上', '4上', 'シニア', 4, 1, NULL, NULL, NULL, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
