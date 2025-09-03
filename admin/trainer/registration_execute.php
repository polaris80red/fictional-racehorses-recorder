<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="調教師マスタ";
$page->title="{$base_title}｜登録：処理実行";

$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$pdo=getPDO();
$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);

$TableClass=Trainer::class;
$TableRowClass=$TableClass::ROW_CLASS;

$form_item=new ($TableRowClass)();
$check_form_item=false;
if($id>0){
    $check_form_item=($TableClass)::getById($pdo,$id);
    $form_item->id=$id;
}
$form_item->unique_name=(string)filter_input(INPUT_POST,'unique_name');
$form_item->name=filter_input(INPUT_POST,'name');
$form_item->short_name_10=filter_input(INPUT_POST,'short_name_10');
$form_item->affiliation_name=filter_input(INPUT_POST,'affiliation_name');
$form_item->is_anonymous=filter_input(INPUT_POST,'is_anonymous',FILTER_VALIDATE_BOOL)?1:0;
$form_item->is_enabled=filter_input(INPUT_POST,'is_enabled',FILTER_VALIDATE_BOOL)?1:0;

do{
    if(!(new FormCsrfToken())->isValid()){
        ELog::error($page->title.": CSRFトークンエラー|".__FILE__);
        $page->addErrorMsg("登録編集フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
        break;
    }
    if($form_item->unique_name==''){
        $page->addErrorMsg("キー名が入力されていません");
    }
    if(mb_strlen($form_item->short_name_10,'UTF-8')>10){
        $page->addErrorMsg("略名は10文字以内で設定してください");
    }
    if($id>0 && $check_form_item===false){
        $page->debug_dump_var[]=['POST'=>$_POST];
        $page->addErrorMsg("{$base_title}設定ID '{$id}' が指定されていますが該当する{$base_title}がありません");
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
if($check_form_item!=false){
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
