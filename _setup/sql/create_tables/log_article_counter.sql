SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `log_article_counter` (
  `id` int(11) NOT NULL COMMENT 'サロゲートキー',
  `article_type` varchar(50) NOT NULL COMMENT '記事などの種類',
  `article_id` varchar(100) NOT NULL COMMENT '記事などのキー（競走馬ID・レースID）',
  `view_count` int(11) NOT NULL DEFAULT 0 COMMENT '表示回数',
  `created_at` datetime NOT NULL COMMENT '新規登録日時',
  `updated_at` datetime NOT NULL COMMENT '最終更新日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `log_article_counter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_type_id` (`article_type`,`article_id`);


ALTER TABLE `log_article_counter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'サロゲートキー';
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
