<?php
// データベース接続情報
$cfg['DB_HOST']     ='localhost';
$cfg['DB_NAME']     ='umdb';
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

// 表示設定のリンクをログイン中でない閲覧者に表示するかどうか
$cfg['SHOW_DISPLAY_SETTINGS_FOR_GUESTS']=true;
/**   SHOW_DISPLAY_SETTINGS_FOR_GUESTS
 *  true    :常にリンクを表示します
 *  false   :ログインしていないときはリンクを表示しません。
 *           また、セッションにキャッシュした設定を使用せず、毎回ファイルまたはデータベースを読み込みます。
 */

// 表示回数カウントのオン・オフ
$cfg['ENABLE_ACCESS_COUNTER']=false;
/**   ENABLE_ACCESS_COUNTER
 *  true    :競走馬ページなどの表示回数を記録します
 *  false   :記録しません（独自のカウンターを設置する場合など）
 */

// 読み取り専用モード（簡易的なWEB公開用設定）
$cfg['READONLY_MODE']=false;
/**   READONLY_MODE
 *  true    :画面からの編集等を行えない閲覧専用モードです。
 *              ALLOW_REMOTE_EDITOR_LOGIN が true でもログイン不可になります。
 *              SHOW_LOGIN_LINK が true でもログインページへのリンクを表示しません。
 *  false   :制限のない通常の状態です
 */

// 親サイトリンクを表示する
$cfg['SHOW_PARENT_SITE_LINK']=false;
// 親サイトリンクの文字列
$cfg['PARENT_SITE_LINK_TEXT']='[メインサイト]';
// 親サイトリンクのURL
$cfg['PARENT_SITE_URL']='/';

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


// デフォルトの表示設定の保存・読み込み先
$cfg['DISPLAY_CONFIG_SOURCE'] = 'db';
/**   SITE_CONFIG_MODE
 *  db  :データベースに保存・取得します
 *  json:jsonファイルに保存・取得します
 *      同じWEBサイト領域に、特定のワールド用のサブサイトを作成する場合などに
 *      データベースの共通設定と別の設定を使うために使用します。
 */

// デフォルトの表示設定のソースがファイルの場合のパス
$cfg['DISPLAY_CONFIG_JSON_PATH'] = $cfg['STORAGE_DIR'].'/display_settings.json';

// 同ドメインに複数設置する際のセッションの識別キー
$cfg['APP_INSTANCE_KEY'] = '';
/**   APP_INSTANCE_KEY
 *      空の場合は自動的にアプリルートのディレクトリ名を使用します
 *      同じドメインに複数設置し、
 *      なおかつ /xxx/yyy と /zzz/yyy のようにアプリルート名が被る場合に別の名前を設定してください
 */

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

// レコードIDへの使用を追加で禁止する文字
$cfg['ITEM_ID_FORBIDDEN_CHARS']='_%';
