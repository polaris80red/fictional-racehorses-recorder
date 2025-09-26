SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `mst_users` (
  `id` int(11) NOT NULL COMMENT 'サロゲートキー',
  `username` varchar(100) NOT NULL COMMENT 'ログインID',
  `password_hash` text NOT NULL COMMENT 'パスワードハッシュ値',
  `display_name` varchar(100) NOT NULL COMMENT '表示名',
  `role` int(11) DEFAULT NULL COMMENT '権限・役割',
  `login_enabled_from` datetime DEFAULT NULL COMMENT 'ログイン可能期間の開始日時',
  `login_enabled_until` datetime DEFAULT NULL COMMENT 'ログイン可能期間の終了日時',
  `login_url_token` varchar(100) DEFAULT NULL COMMENT '専用ログインURLのトークン',
  `last_login_at` datetime DEFAULT NULL COMMENT '最終ログイン日時',
  `failed_login_attempts` int(11) NOT NULL DEFAULT 0 COMMENT '連続ログイン失敗回数',
  `login_locked_until` datetime DEFAULT NULL COMMENT 'ログイン禁止の終了日時',
  `is_enabled` tinyint(4) NOT NULL DEFAULT 1 COMMENT '論理削除用フラグ',
  `created_by` int(11) DEFAULT NULL COMMENT '登録者',
  `updated_by` int(11) DEFAULT NULL COMMENT '最終更新者',
  `created_at` datetime DEFAULT NULL COMMENT '登録日時',
  `updated_at` datetime DEFAULT NULL COMMENT '最終更新日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='ユーザー';


ALTER TABLE `mst_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);


ALTER TABLE `mst_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'サロゲートキー';
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
