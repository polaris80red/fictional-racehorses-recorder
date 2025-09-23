<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
InAppUrl::init(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="ユーザーアカウント";
$page->title="{$base_title}登録：処理実行";

if(!Session::isLoggedIn()){ $page->exitToHome(); }
$currentUser=Session::currentUser();
if(!$currentUser->canUserManage()){
    $page->setErrorReturnLink('管理画面に戻る',InAppUrl::to('admin/'));
    $page->error_msgs[]="ユーザー管理には管理者権限が必要です。";
    header("HTTP/1.1 403 Forbidden");
    $page->printCommonErrorPage();
    exit;
}

$pdo=getPDO();
$inputId=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT)?:null;

$editMode=($inputId>0);
$TableClass=Users::class;
$TableRowClass=$TableClass::ROW_CLASS;

if($editMode){
    $page->title.="（編集）";
    $form_item=($TableClass)::getById($pdo,$inputId);
    if($form_item===false){
        $page->addErrorMsg("ID '{$inputId}' が指定されていますが該当するレコードがありません");
    }
}else{
    $form_item=new ($TableRowClass)();
}

$form_item->username=filter_input(INPUT_POST,'username');
$password=(string)filter_input(INPUT_POST,'password');
if($password!==''){
    $form_item->password_hash=password_hash($password,PASSWORD_DEFAULT);
}
$form_item->display_name=filter_input(INPUT_POST,'display_name');
$form_item->role=filter_input(INPUT_POST,'role',FILTER_VALIDATE_INT);
$login_enabled_until=(string)filter_input(INPUT_POST,'login_enabled_until');
$form_item->login_enabled_until=null;
$datetime=$login_enabled_until===''?false:DateTime::createFromFormat('Y-m-d H:i:s',$login_enabled_until);
if($datetime){
    $form_item->login_enabled_until=$datetime->format('Y-m-d H:i:s');
}
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
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
$form_item->updated_by=$currentUser->getId();
$form_item->updated_at=PROCESS_STARTED_AT;
if($editMode){
    // 編集モード
    $result = ($TableClass)::UpdateFromRowObj($pdo,$form_item);
    if($result){
        redirect_exit("./list.php");
    }
}else{
    // 新規登録モード
    $form_item->created_by = $form_item->updated_by;
    $form_item->created_at = $form_item->updated_at;
    $result = ($TableClass)::InsertFromRowObj($pdo,$form_item);
    if($result){
        redirect_exit("./list.php");
    }
}
