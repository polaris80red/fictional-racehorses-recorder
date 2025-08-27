<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="設定実行結果";

$pdo=getPDO();
$setting->setByStdClass($_POST);
$save_to_file=filter_input(INPUT_POST,'save_to_file');
$story_id=filter_input(INPUT_POST,'story_id');
$story=null;
if($story_id>0){
    $story=new WorldStory($pdo,$story_id);
    $config_json=$story->config_json;
    /*
    if($story->world_id===0){ unset($config_json->world_id); }
    */
    if(is_object($config_json)){
        $setting->setByStdClass($config_json);
    }
}
// ストーリー設定への保存処理
$save_story_is_enabled=filter_input(INPUT_POST,'save_story_is_enabled',FILTER_VALIDATE_BOOL);
$save_story_id=filter_input(INPUT_POST,'save_story_id',FILTER_VALIDATE_INT);
if($save_story_is_enabled && $save_story_id>0 && Session::is_logined()){
    $save_target_story=new WorldStory();
    $result = $save_target_story->getDataById($pdo,$save_story_id);
    if($result!=false){
        $config_json_data=$setting->getSettingArray();
        if(isset($_POST['save_target']) && is_array($_POST['save_target'])){
            $diff_array=$_POST['save_target'];
            // 複数欄共通チェックボックスは比較用配列を差し替え
            if(!empty($diff_array['year_select_min_max_diff'])){
                unset($diff_array['year_select_min_max_diff']);
                $diff_array['year_select_max_diff']=1;
                $diff_array['year_select_min_diff']=1;
            }
            if(!empty($diff_array['race_search_org'])){
                unset($diff_array['race_search_org']);
                $diff_array['race_search_org_jra']=1;
                $diff_array['race_search_org_nar']=1;
                $diff_array['race_search_org_other']=1;
            }
            $config_json_data=array_intersect_key($config_json_data,$diff_array);
        }
        $save_target_story->config_json=$config_json_data;

        $save_target_story->updateConfigExec($pdo);
    }
}

// 設定反映
$setting->saveToSessionAll();
// ログイン中かつ保存する選択でなければデフォルト値には反映しない
//（ログアウトやセッションが切れるまでのみ有効）
if(SESSION::is_logined() && $save_to_file){
    (new ConfigTable($pdo))->setTimestamp(PROCESS_STARTED_AT)->saveAllParams($setting->getSettingArray());
}

// 年上限・下限が絶対値で設定によっては範囲外になりやすいので設定変更後の初期値に強制変換（暫定）
//unset($_SESSION['race_list']);
if(isset($_SESSION['race_list'])){
    if(isset($_SESSION['race_list']['max_year'])
        && isset($_SESSION['setting']['year_select_max_diff'])
        && isset($_SESSION['setting']['select_zero_year'])
    ){
        $_SESSION['race_list']['max_year']
        =$_SESSION['setting']['select_zero_year']+$_SESSION['setting']['year_select_max_diff'];
    }
    if(isset($_SESSION['race_list']['min_year'])
        && isset($_SESSION['setting']['year_select_min_diff'])
        && isset($_SESSION['setting']['select_zero_year'])
    ){
        $_SESSION['race_list']['min_year']
        =$_SESSION['setting']['select_zero_year']-$_SESSION['setting']['year_select_min_diff'];
    }
    if(isset($_SESSION['race_list']['year']) &&
        strval($_SESSION['race_list']['year'])!=='' &&
        isset($_SESSION['setting']['select_zero_year'])){
        $_SESSION['race_list']['year']=$_SESSION['setting']['select_zero_year'];
    }
}
header('Location: ./');
exit;