SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


INSERT INTO `mst_race_course` (`id`, `unique_name`, `short_name`, `short_name_m`, `sort_priority`, `sort_number`, `show_in_select_box`, `is_enabled`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(202, '豪州', '豪', 'ｵｰｽﾄﾗﾘｱ', 0, NULL, 0, 1, NULL, NULL, NULL, NULL),
(207, 'バーレーン', '巴', 'バーレーン', 0, NULL, 0, 1, NULL, NULL, NULL, NULL),
(224, 'フランス', '仏', 'フランス', 0, NULL, 0, 1, NULL, NULL, NULL, NULL),
(225, '英国', '英', 'イギリス', 0, NULL, 0, 1, NULL, NULL, NULL, NULL),
(231, '香港', '香港', '', 0, NULL, 0, 1, NULL, NULL, NULL, NULL),
(235, 'アイルランド', '愛', 'アイルランド', 0, NULL, 0, 1, NULL, NULL, NULL, NULL),
(242, '韓国', '韓', '韓国', 0, NULL, 0, 1, NULL, NULL, NULL, NULL),
(243, 'サウジ', 'サウ', 'サウジ', 0, NULL, 0, 1, NULL, NULL, NULL, NULL),
(263, 'カタール', '華', 'カタール', 0, NULL, 0, 1, NULL, NULL, NULL, NULL),
(283, 'UAE', 'ア首', 'UAE', 0, NULL, 0, 1, NULL, NULL, NULL, NULL),
(286, '米国', '米', 'アメリカ', 0, NULL, 0, 1, NULL, NULL, NULL, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
