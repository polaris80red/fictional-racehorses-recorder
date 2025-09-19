<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="ストーリー";
$page->title="{$base_title}設定登録：処理実行";

$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$pdo=getPDO();
$inputId=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
$editMode=($inputId>0);
$TableClass=WorldStory::class;
$TableRowClass=$TableClass::ROW_CLASS;

if($editMode){
    $page->title.="（編集）";
    $story=($TableClass)::getById($pdo,$inputId);
    if($story===false){
        $page->addErrorMsg("ID '{$inputId}' が指定されていますが該当する設定がありません");
    }
}else{
    $story=new ($TableRowClass)();
}
$story->name=filter_input(INPUT_POST,'name');
$story->guest_visible=filter_input(INPUT_POST,'guest_visible',FILTER_VALIDATE_BOOL)?1:0;
$story->sort_priority=filter_input(INPUT_POST,'sort_priority',FILTER_VALIDATE_INT);

$story->sort_number=intOrNull(filter_input(INPUT_POST,'sort_number'));

$story->is_read_only=filter_input(INPUT_POST,'is_read_only',FILTER_VALIDATE_BOOL)?1:0;
$story->is_enabled=filter_input(INPUT_POST,'is_enabled',FILTER_VALIDATE_BOOL)?1:0;

$story->setConfig(json_decode(filter_input(INPUT_POST,'config_json')));

$save_to_session=filter_input(INPUT_POST,'save_to_session',FILTER_VALIDATE_BOOL);
$save_to_defaults=filter_input(INPUT_POST,'save_to_defaults',FILTER_VALIDATE_BOOL);

do{
    if(!(new FormCsrfToken())->isValid()){
        ELog::error($page->title.": CSRFトークンエラー|".__FILE__);
        $page->addErrorMsg("登録編集フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
        break;
    }
    if($story->name===''){
        $page->debug_dump_var[]=['POST'=>$_POST];
        $page->addErrorMsg("{$base_title}設定名称未設定");
        break;
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
$setting->setByStdClass($story->config_json);
// 現在のセッションへの反映処理
if($save_to_session){
    $setting->saveToSessionAll();
}
// デフォルト設定への反映処理
if($save_to_defaults){
    (new ConfigTable($pdo))->setTimestamp(PROCESS_STARTED_AT)->saveAllParams($setting->getSettingArray());
}
if($editMode){
    // 編集モード
    $result = ($TableClass)::UpdateFromRowObj($pdo,$story);
    if($result){
        redirect_exit("./list.php");
    }
}else{
    // 新規登録モード
    $result = ($TableClass)::InsertFromRowObj($pdo,$story);
    if($result){
        redirect_exit("./list.php");
    }
}
