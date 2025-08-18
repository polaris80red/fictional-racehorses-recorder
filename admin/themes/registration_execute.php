<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="テーマ";
$page->title="{$base_title}設定登録：処理実行";

$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$pdo=getPDO();
$input_id=filter_input(INPUT_POST,'theme_id',FILTER_VALIDATE_INT);
$theme=new Themes();
if($input_id>0){
    $theme->getDataById($pdo,$input_id);
}
$theme->name=filter_input(INPUT_POST,'name');
$theme->dir_name=filter_input(INPUT_POST,'dir_name');
$theme->sort_priority=filter_input(INPUT_POST,'sort_priority',FILTER_VALIDATE_INT);
$theme->sort_number=intOrNull(filter_input(INPUT_POST,'sort_number'));

$theme->is_enabled=filter_input(INPUT_POST,'is_enabled',FILTER_VALIDATE_BOOL)?1:0;

$error_exists=false;
do{
    if(!(new FormCsrfToken())->isValid()){
        $error_exists=true;
        Elog::error($page->title.": CSRFトークンエラー|".__FILE__);
        $page->addErrorMsg("登録編集フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
        break;
    }
    if($input_id>0 && !$theme->record_exists){
        $error_exists=true;
        $page->debug_dump_var[]=['POST'=>$_POST];
        $page->addErrorMsg("{$base_title}設定ID '{$input_id}' が指定されていますが該当する{$base_title}がありません");
    }
    if($theme->name===''){
        $error_exists=true;
        $page->debug_dump_var[]=['POST'=>$_POST];
        $page->addErrorMsg("{$base_title}設定名称未設定");
        break;
    }
    $path=APP_ROOT_REL_PATH.'themes/'.$theme->dir_name;
    if(!is_dir($path)){
        $error_exists=true;
        $page->debug_dump_var[]=['POST'=>$_POST];
        $page->addErrorMsg("指定された[ {$theme->dir_name} ]ディレクトリが存在しません。");
        break;
    }
}while(false);
if($error_exists){
    $page->printCommonErrorPage();
    exit;
}
if($theme->record_exists){
    // 編集モード
    $result = $theme->UpdateExec($pdo);
    if($result){
        redirect_exit("./list.php");
    }
}else{
    // 新規登録モード
    $result = $theme->InsertExec($pdo);
    if($result){
        redirect_exit("./list.php");
    }
}
