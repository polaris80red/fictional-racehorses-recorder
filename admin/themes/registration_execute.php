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
if(!Session::isLoggedIn()){ $page->exitToHome(); }
if(!Session::currentUser()->canManageSystemSettings()){
    header("HTTP/1.1 403 Forbidden");
    $page->addErrorMsg('システム設定管理権限がありません');
}

$pdo=getPDO();
$inputId=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);

$editMode=($inputId>0);
$TableClass=Themes::class;
$TableRowClass=$TableClass::ROW_CLASS;

if($editMode){
    $page->title.="（編集）";
    $form_item=($TableClass)::getById($pdo,$inputId);
    if($form_item===false){
        $page->addErrorMsg("テーマID '{$inputId}' が指定されていますが該当するテーマがありません");
    }
}else{
    $form_item=new ($TableRowClass)();
}

$form_item->name=filter_input(INPUT_POST,'name');
$form_item->dir_name=filter_input(INPUT_POST,'dir_name');
$form_item->sort_priority=filter_input(INPUT_POST,'sort_priority',FILTER_VALIDATE_INT);
$form_item->sort_number=intOrNull(filter_input(INPUT_POST,'sort_number'));

$form_item->is_enabled=filter_input(INPUT_POST,'is_enabled',FILTER_VALIDATE_BOOL)?1:0;

do{
    if(!(new FormCsrfToken())->isValid()){
        ELog::error($page->title.": CSRFトークンエラー|".__FILE__);
        $page->addErrorMsg("登録編集フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
        break;
    }
    if(!$form_item->validate()){
        $page->addErrorMsgArray($form_item->errorMessages);
        break;
    }
    $path=APP_ROOT_DIR.'/themes/'.$form_item->dir_name;
    if(!is_dir($path)){
        $page->addErrorMsg("指定された[ {$form_item->dir_name} ]ディレクトリが存在しません。");
        break;
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
if($editMode){
    // 編集モード
    $result = ($TableClass)::UpdateFromRowObj($pdo,$form_item);
    if($result){
        redirect_exit("./list.php");
    }
}else{
    // 新規登録モード
    $result = ($TableClass)::InsertFromRowObj($pdo,$form_item);
    if($result){
        redirect_exit("./list.php");
    }
}
