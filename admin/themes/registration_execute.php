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
$inputId=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
$editMode=($inputId>0);
$TableClass=Themes::class;
$TableRowClass=$TableClass::ROW_CLASS;

$theme=($TableClass)::getById($pdo,$inputId);
if($editMode){
    $page->title.="（編集）";
}
if($editMode && $theme===false){
    $page->addErrorMsg("テーマID '{$inputId}' が指定されていますが該当するテーマがありません");
}
if($theme===false){
    $theme=new ($TableRowClass)();
}
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
$theme->name=filter_input(INPUT_POST,'name');
$theme->dir_name=filter_input(INPUT_POST,'dir_name');
$theme->sort_priority=filter_input(INPUT_POST,'sort_priority',FILTER_VALIDATE_INT);
$theme->sort_number=intOrNull(filter_input(INPUT_POST,'sort_number'));

$theme->is_enabled=filter_input(INPUT_POST,'is_enabled',FILTER_VALIDATE_BOOL)?1:0;

$error_exists=false;
do{
    if(!(new FormCsrfToken())->isValid()){
        ELog::error($page->title.": CSRFトークンエラー|".__FILE__);
        $page->addErrorMsg("登録編集フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
        break;
    }
    if($theme->name===''){
        $page->debug_dump_var[]=['POST'=>$_POST];
        $page->addErrorMsg("{$base_title}設定名称未設定");
        break;
    }
    $path=APP_ROOT_REL_PATH.'themes/'.$theme->dir_name;
    if(!is_dir($path)){
        $page->debug_dump_var[]=['POST'=>$_POST];
        $page->addErrorMsg("指定された[ {$theme->dir_name} ]ディレクトリが存在しません。");
        break;
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
if($editMode){
    // 編集モード
    $result = ($TableClass)::UpdateFromRowObj($pdo,$theme);
    if($result){
        redirect_exit("./list.php");
    }
}else{
    // 新規登録モード
    $result = ($TableClass)::InsertFromRowObj($pdo,$theme);
    if($result){
        redirect_exit("./list.php");
    }
}
