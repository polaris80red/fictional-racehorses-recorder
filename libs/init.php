<?php
/*
 * 外部ファイルの読み込み
 * PDOのインスタンスを生成。
 *
 */
define('APP_ROOT_DIR', dirname(__DIR__));
require_once __DIR__.'/common.php';
require_once __DIR__.'/functions.php';

// 基本的な autoload の設定
require_once __DIR__.'/Class/MkTag/autoload.php';
require_once __DIR__.'/Class/MkSql/autoload.php';
require_once __DIR__.'/Class/SqlMake/autoload.php';
spl_autoload_register(function ($class_name) {
    // Classディレクトリ直下を探す
    $path=__DIR__.'/Class/'.$class_name . '.php';
    if(file_exists($path)){
        require_once $path;
        return;
    }
    // Traitディレクトリ直下
    $path=__DIR__.'/Trait/'.$class_name . '.php';
    if(file_exists($path)){
        require_once $path;
        return;
    }
    // Commonディレクトリのクラス
    $path=__DIR__.'/Class/Common/'.$class_name . '.php';
    if(file_exists($path)){
        require_once $path;
        return;
    }
    // tableディレクトリのクラス
    $path=__DIR__.'/Class/table/'.$class_name . '.php';
    if(file_exists($path)){
        require_once $path;
        return;
    }
    $path=__DIR__.'/Class/table/Row/'.$class_name . '.php';
    if(file_exists($path)){
        require_once $path;
        return;
    }
    // Searchディレクトリのクラス
    $path=__DIR__.'/Class/Search/'.$class_name . '.php';
    if(file_exists($path)){
        require_once $path;
        return;
    }
    // Umdbディレクトリのクラス
    $path=__DIR__.'/Class/Umdb/'.$class_name . '.php';
    if(file_exists($path)){
        require_once $path;
        return;
    }
});

// 設定のインポート
require_once __DIR__.'/Class/EnvConfigInitializer.php';
(function(){
    $cfg=[];
    // デフォルト設定をインポートする
    $defaults=(function(){
        require_once APP_ROOT_DIR.'/config.sample.inc.php';
        return $cfg;
    })();
    // ユーザー設定（config.inc.php）が存在すればインポートする
    $cfg=(function($user_config_path){
        if(!file_exists($user_config_path)){ return [];}
        require_once $user_config_path;
        if(!isset($cfg) || !is_array($cfg)){ return []; }
        return $cfg;
    })(APP_ROOT_DIR.'/config.inc.php');
    EnvConfigInitializer::initialize($cfg,$defaults);
    return;
})();
date_default_timezone_set("Asia/Tokyo");
define('PROCESS_STARTED_AT',(new DateTime())->format('Y-m-d H:i:s'));

ELog::setExportDir(LOG_DIR_PATH,LOG_FILE_PREFIX);
ELog::setLogLevel(LOG_LEVEL);

// ALLOW_REMOTE_ACCESS で許可されていない場合、Localhost以外からのアクセスを遮断する
(function(){
    if(ALLOW_REMOTE_ACCESS===true){
        return;
    }
    if(is_remote_access()){
        header('HTTP/1.1 403 Forbidden');
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'Access denied: 実行中のコンピューター以外からのアクセスは禁止されています。';
        exit;
    }
})();

// 外部ライブラリに関する設定のインポート
require_once APP_ROOT_DIR.'/vendor/VendorConfigInitializer.php';
(function(){
    $cfg=[];
    // デフォルト設定をインポートする
    $defaults=(function(){
        require_once APP_ROOT_DIR.'/vendor/vendor_config.sample.inc.php';
        return $cfg;
    })();
    // ユーザー設定（vendor_config.inc.php）が存在すればインポートする
    $cfg=(function($user_config_path){
        if(!file_exists($user_config_path)){ return [];}
        require_once $user_config_path;
        if(!isset($cfg) || !is_array($cfg)){ return []; }
        return $cfg;
    })(APP_ROOT_DIR.'/vendor/vendor_config.inc.php');
    VendorConfigInitializer::initialize($cfg,$defaults);
    return;
})();

require_once __DIR__.'/get_syutsuba_data.inc.php';

TemplateImporter::setDefalutDir(APP_ROOT_DIR."/templates");
TemplateImporter::setUserDir('user/templates');

#require_once DIR.'/libs/init.php';
define('DSN', 'mysql:dbname='.DB_NAME.';host='.DB_HOST.';charset='.DB_CHARSET);

/**
 * WEBアプリルートURLへの相対パスの定数を定義
 * @param $num アプリルートからの階層の深さ（/docroot/app_root/index.php なら0）
 */
function defineAppRootRelPath(int $num = 0){
    if($num===0){
        $to_app_root_path="./";
    } else {
        $to_app_root_path="";
        for($i=0; $i<$num; $i++){
            $to_app_root_path.="../";
        }
    }
    define('APP_ROOT_REL_PATH',$to_app_root_path);
    return;
}

/*
 * PDOインスタンス取得
 * @param void
 * @return object PDO
 *
 * */
function getPDO() {
    static $pdo = null;

    if ( is_null( $pdo ) ) {
        try{
            $pdo  = new PDO(
                DSN,
                DB_USER,
                DB_PASS,
                array(
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                )
            );
        }catch (PDOException $e) {
            echo('データベースに接続できませんでした。');
            print_r($e);
            exit;
            //echo 'Connection failed: ' . $e->getMessage()."\n";
        }
    }
    return $pdo;
}
