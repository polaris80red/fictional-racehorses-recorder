SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


INSERT INTO `mst_race_week` (`id`, `name`, `month`, `month_grouping`, `umm_month_turn`, `sort_number`, `is_enabled`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, '中山金杯・京都金杯 まで', 1, 11, 1, 1, 1, NULL, NULL, NULL, NULL),
(2, 'シンザン記念', 1, 12, 1, 2, 1, NULL, NULL, NULL, NULL),
(3, '日経新春杯', 1, 13, 1, 3, 1, NULL, NULL, NULL, NULL),
(4, 'アメリカJCC', 1, 14, 2, 4, 1, NULL, NULL, NULL, NULL),
(5, '根岸S・シルクロードS', 1, 15, 2, 5, 1, NULL, NULL, NULL, NULL),
(6, '東京新聞杯・きさらぎ賞', 2, 21, 1, 6, 1, NULL, NULL, NULL, NULL),
(7, '京都記念', 2, 22, 1, 7, 1, NULL, NULL, NULL, NULL),
(8, 'フェブラリーS', 2, 23, 2, 8, 1, NULL, NULL, NULL, NULL),
(9, '中山記念・チューリップ賞', 2, 25, 2, 9, 1, NULL, NULL, NULL, NULL),
(10, '弥生賞・フィリーズR', 3, 31, 1, 10, 1, NULL, NULL, NULL, NULL),
(11, '金鯱賞', 3, 32, 1, 11, 1, NULL, NULL, NULL, NULL),
(12, '阪神大賞典', 3, 33, 2, 12, 1, NULL, NULL, NULL, NULL),
(13, '高松宮記念', 3, 34, 2, 13, 1, NULL, NULL, NULL, NULL),
(14, '大阪杯', 3, 35, 2, 14, 1, NULL, NULL, NULL, NULL),
(15, '桜花賞', 4, 41, 1, 15, 1, NULL, NULL, NULL, NULL),
(16, '皐月賞・中山GJ', 4, 42, 1, 16, 1, NULL, NULL, NULL, NULL),
(17, '青葉賞・フローラS', 4, 43, 2, 17, 1, NULL, NULL, NULL, NULL),
(18, '天皇賞（春）', 4, 45, 2, 18, 1, NULL, NULL, NULL, NULL),
(19, 'NHKマイルC', 5, 51, 1, 19, 1, NULL, NULL, NULL, NULL),
(20, 'ヴィクトリアM', 5, 52, 1, 20, 1, NULL, NULL, NULL, NULL),
(21, 'オークス', 5, 53, 2, 21, 1, NULL, NULL, NULL, NULL),
(22, '東京優駿・目黒記念', 5, 55, 2, 22, 1, NULL, NULL, NULL, NULL),
(23, '安田記念', 6, 61, 1, 23, 1, NULL, NULL, NULL, NULL),
(24, '宝塚記念（2025）', 6, 62, 1, 24, 1, NULL, NULL, NULL, NULL),
(25, '府中牝馬S・しらさぎS', 6, 63, 2, 25, 1, NULL, NULL, NULL, NULL),
(26, '函館記念・ラジオNIKKEI賞', 6, 64, 2, 26, 1, NULL, NULL, NULL, NULL),
(27, '北九州記念', 7, 70, 1, 27, 1, NULL, NULL, NULL, NULL),
(28, '七夕賞', 7, 72, 1, 28, 1, NULL, NULL, NULL, NULL),
(29, '小倉記念', 7, 73, 2, 29, 1, NULL, NULL, NULL, NULL),
(30, '関屋記念', 7, 74, 2, 30, 1, NULL, NULL, NULL, NULL),
(31, 'アイビスSD', 7, 75, 2, 31, 1, NULL, NULL, NULL, NULL),
(32, 'CBC賞', 8, 81, 1, 32, 1, NULL, NULL, NULL, NULL),
(33, '札幌記念', 8, 82, 1, 33, 1, NULL, NULL, NULL, NULL),
(34, 'キーンランドC', 8, 83, 2, 34, 1, NULL, NULL, NULL, NULL),
(35, '新潟記念', 8, 84, 2, 35, 1, NULL, NULL, NULL, NULL),
(36, '紫苑S', 9, 90, 1, 36, 1, NULL, NULL, NULL, NULL),
(37, 'セントライト記念・ローズS', 9, 91, 1, 37, 1, NULL, NULL, NULL, NULL),
(38, '神戸新聞杯・オールカマー', 9, 92, 1, 38, 1, NULL, NULL, NULL, NULL),
(39, 'スプリンターズS（2025）', 9, 93, 2, 39, 1, NULL, NULL, NULL, NULL),
(40, '毎日王冠・京都大賞典', 9, 95, 2, 40, 1, NULL, NULL, NULL, NULL),
(41, 'アイルランドT・スワンS', 10, 101, 1, 41, 1, NULL, NULL, NULL, NULL),
(42, '秋華賞', 10, 102, 2, 42, 1, NULL, NULL, NULL, NULL),
(43, '菊花賞', 10, 103, 2, 43, 1, NULL, NULL, NULL, NULL),
(44, '天皇賞（秋）', 10, 105, 2, 44, 1, NULL, NULL, NULL, NULL),
(45, 'アルゼンチン共和国杯', 11, 111, 1, 45, 1, NULL, NULL, NULL, NULL),
(46, 'エリザベス女王杯', 11, 112, 1, 46, 1, NULL, NULL, NULL, NULL),
(47, 'マイルCS', 11, 113, 2, 47, 1, NULL, NULL, NULL, NULL),
(48, 'ジャパンカップ', 11, 114, 2, 48, 1, NULL, NULL, NULL, NULL),
(49, 'チャンピオンズC', 12, 120, 1, 49, 1, NULL, NULL, NULL, NULL),
(50, '阪神ジュベナイルF', 12, 121, 1, 50, 1, NULL, NULL, NULL, NULL),
(51, '朝日杯FS', 12, 122, 1, 51, 1, NULL, NULL, NULL, NULL),
(52, '有馬記念・中山大障害 以降', 12, 123, 2, 52, 1, NULL, NULL, NULL, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
