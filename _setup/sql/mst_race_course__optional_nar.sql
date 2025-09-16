SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


INSERT INTO `mst_race_course` (`id`, `unique_name`, `short_name`, `short_name_m`, `sort_priority`, `sort_number`, `show_in_select_box`, `is_enabled`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(101, '帯広', '帯広', '', 0, 101, 0, 0, NULL, NULL, NULL, NULL),
(102, '門別', '門別', '', 0, 102, 0, 1, NULL, NULL, NULL, NULL),
(103, '盛岡', '盛岡', '', 0, 103, 0, 1, NULL, NULL, NULL, NULL),
(104, '水沢', '水沢', '', 0, 104, 0, 1, NULL, NULL, NULL, NULL),
(105, '浦和', '浦和', '', 0, 105, 0, 1, NULL, NULL, NULL, NULL),
(106, '船橋', '船橋', '', 0, 106, 0, 1, NULL, NULL, NULL, NULL),
(107, '大井', '大井', '', 0, 107, 0, 1, NULL, NULL, NULL, NULL),
(108, '川崎', '川崎', '', 0, 108, 0, 1, NULL, NULL, NULL, NULL),
(109, '金沢', '金沢', '', 0, 109, 0, 1, NULL, NULL, NULL, NULL),
(110, '笠松', '笠松', '', 0, 110, 0, 1, NULL, NULL, NULL, NULL),
(111, '名古屋', '名古屋', '', 0, 111, 0, 1, NULL, NULL, NULL, NULL),
(112, '園田', '園田', '', 0, 112, 0, 1, NULL, NULL, NULL, NULL),
(113, '姫路', '姫路', '', 0, 113, 0, 1, NULL, NULL, NULL, NULL),
(114, '高知', '高知', '', 0, 114, 0, 1, NULL, NULL, NULL, NULL),
(115, '佐賀', '佐賀', '', 0, 115, 0, 1, NULL, NULL, NULL, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
