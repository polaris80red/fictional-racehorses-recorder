<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="騎手マスタ";
$page->title="{$base_title}｜登録：処理実行";

$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }
if(!Session::currentUser()->canManageMaster()){
    header("HTTP/1.1 403 Forbidden");
    $page->addErrorMsg('マスタ管理権限がありません');
}
$pdo=getPDO();
$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);

$editMode=($id>0);
$TableClass=Jockey::class;
$TableRowClass=$TableClass::ROW_CLASS;

if($editMode){
    $page->title.="（編集）";
    $form_item=($TableClass)::getById($pdo,$id);
    if($form_item===false){
        $page->addErrorMsg("ID '{$id}' が指定されていますが該当するレコードがありません");
    }
}else{
    $form_item=new ($TableRowClass)();
}
$form_item->unique_name=(string)filter_input(INPUT_POST,'unique_name');
$form_item->name=filter_input(INPUT_POST,'name');
$form_item->short_name_10=filter_input(INPUT_POST,'short_name_10');
$form_item->affiliation_name=filter_input(INPUT_POST,'affiliation_name');
$form_item->trainer_name=filter_input(INPUT_POST,'trainer_name');
$form_item->is_anonymous=filter_input(INPUT_POST,'is_anonymous',FILTER_VALIDATE_BOOL)?1:0;
$form_item->is_enabled=filter_input(INPUT_POST,'is_enabled',FILTER_VALIDATE_BOOL)?1:0;

do{
    if(!(new FormCsrfToken())->isValid()){
        ELog::error($page->title.": CSRFトークンエラー|".__FILE__);
        $page->addErrorMsg("登録編集フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
        break;
    }
    if(!$form_item->validate()){
        $page->addErrorMsgArray($form_item->errorMessages);
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
