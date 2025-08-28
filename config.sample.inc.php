<?php
// データベース接続情報
$cfg['DB_HOST']     ='localhost';
$cfg['DB_NAME']     ='test';
$cfg['DB_USER']     ='root';
$cfg['DB_PASS']     ='';
$cfg['DB_CHARSET']  ='utf8mb4';

// サイト名
$cfg['SITE_NAME']  ='UMDB';

// リモートIPでのアクセスの許可（公開設定）
$cfg['ALLOW_REMOTE_ACCESS']=false;
/**   ALLOW_REMOTE_ACCESS
 *  false   :localhost（実行しているコンピュータのIP）以外からのアクセスではサイトを表示しません
 *  true    :リモートIPアドレスからのアクセスを許可します（サーバーに設置して公開する場合に設定します）
 */

// リモートIPでの登録編集ログインの許可
$cfg['ALLOW_REMOTE_EDITOR_LOGIN']=false;
/**   ALLOW_REMOTE_EDITOR_LOGIN
 *  false   :localhost以外からのアクセスでは登録編集などのためのログインはできません
 *  true    :リモートIPアドレスからのログインを許可します
 */

// 検索のインデックス拒否
$cfg['FORCE_NOINDEX']=true;
/**   FORCE_NOINDEX
 *  true    :noindex・nofollowをメタタグに設定して全体をインデックス拒否します
 *  false   :基本的にはnoindex・nofollowを設定しません
 */

// ログインリンクをフロントに表示するかどうか
$cfg['SHOW_LOGIN_LINK']=true;
/**   SHOW_LOGIN_LINK
 *  true    :リンクを表示します
 *  false   :ログインするには「アプリのルート/admin/」にアクセスしてください
 */

// 管理ユーザー向けにphpMyAdmin画面へのリンクを表示する（XAMPPによるローカル環境向け）
$cfg['SHOW_PHPMYADMIN_LINK'] =true;
$cfg['PHPMYADMIN_URL'] ='/phpmyadmin/';

// 各種ファイル保存先
$cfg['STORAGE_DIR']             =APP_ROOT_DIR.'/storage';

// ログファイル設定
$cfg['LOG_DIR_PATH']            =$cfg['STORAGE_DIR'].'/logs';
$cfg['LOG_FILE_PREFIX'] ='UMDB';
$cfg['LOG_LEVEL']=0;
/**   LOG_LEVEL
 *      0:ログを出力しない
 *     -1:全て出力 */

$cfg['ADMINISTRATOR_USER'] ='admin';
$cfg['ADMINISTRATOR_PASS'] ='';
/**
 * パスワードをpassword_hashで処理したハッシュ値を記載します。
 * ADMINISTRATOR_PASS が空の場合は、IDのみ入力でログインします。
 * 下記コメントアウト例（adminで一致します）のように設定ファイル上でpassword_hash処理をしても動作しますが、
 * 実際にWEBに公開する場合はPHPファイル自体が流出した際を考慮し、ハッシュ値自体を記載することを推奨します。
 *  ※ ALLOW_REMOTE_EDITOR_LOGIN を有効にしている場合、パスワードなしでのログインはできません。
 */
//$cfg['ADMINISTRATOR_PASS'] =password_hash('admin',PASSWORD_DEFAULT);

$cfg['ANNONYMOUS_HORSE_NAME'] ='□□□□□□';

$cfg['EDIT_MENU_TOGGLE']=false;
/**   EDIT_MENU_TOGGLE 競走馬・レースページの編集メニューの開閉機能
 *      true:   初期値は閉じる
 *      false:  常に表示
 */

$cfg['AUTO_ID_DATE_PART_FORMAT']='ymd';
/**   AUTO_ID_DATE_PART_FORMAT 自動IDに日付をつける場合の日付部分のフォーマット
 *      ymd: 年2桁月2桁日2桁（推奨）
 *      Ymd: 年4桁月2桁日2桁
 */

$cfg['AUTO_ID_DATE_NUMBER_SEPARATOR']='-';
/** AUTO_ID_DATE_NUMBER_SEPARATOR
 *      日付部分と自動採番した数値の間に挟む文字列
 */

$cfg['AUTO_ID_NUMBER_MIN_LENGTH']=4;
/**   AUTO_ID_NUMBER_MIN_LENGTH
 *      自動採番した数値を使う区間の最低長さ（この設定より短い場合は左を0で埋める）
 */

$cfg['AUTO_ID_RESET_MODE']='d';
/**   AUTO_ID_RESET_MODE
 *      d: 最後の登録と日が変わったら、1からに戻る
 *      m: 最後の登録と月が変わったら、1からに戻る
 *      y: 最後の登録と年が変わったら、1からに戻る
 * */
