SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


INSERT INTO `mst_race_special_results` (`id`, `unique_name`, `name`, `short_name_2`, `is_registration_only`, `is_excluded_from_race_count`, `sort_number`, `is_enabled`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, '中止', '競走中止', '中止', 0, 0, 1, 1, NULL, NULL, NULL, NULL),
(2, '除外', '競走除外', '除外', 0, 1, 2, 1, NULL, NULL, NULL, NULL),
(3, '取消', '出走取消', '取消', 1, 1, 3, 1, NULL, NULL, NULL, NULL),
(4, '非当', '非当選', '非当', 1, 1, 4, 1, NULL, NULL, NULL, NULL),
(5, '非抽', '非抽選', '非抽', 1, 1, 5, 1, NULL, NULL, NULL, NULL),
(6, '回避', '回避(出走投票せず)', '回避', 1, 1, 6, 1, NULL, NULL, NULL, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
