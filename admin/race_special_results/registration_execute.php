<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="レース特殊結果マスタ";
$page->title="{$base_title}｜設定登録：処理実行";

$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$pdo=getPDO();
$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);

$TableClass=RaceSpecialResults::class;
$TableRowClass=$TableClass::ROW_CLASS;

$form_item=new ($TableRowClass)();
$check_form_item=false;
if($id>0){
    $check_form_item=($TableClass)::getById($pdo,$id);
    $form_item->id=$id;
}
$form_item->unique_name=filter_input(INPUT_POST,'unique_name');
$form_item->name=filter_input(INPUT_POST,'name');
$form_item->short_name_2=filter_input(INPUT_POST,'short_name_2');
$form_item->is_registration_only=filter_input(INPUT_POST,'is_registration_only');
$form_item->is_excluded_from_race_count=filter_input(INPUT_POST,'is_excluded_from_race_count');
$form_item->sort_number=filter_input(INPUT_POST,'sort_number');
if($form_item->sort_number===''){
    $form_item->sort_number=null;
}else{
    $form_item->sort_number=(int)$form_item->sort_number;
}
$form_item->is_enabled=filter_input(INPUT_POST,'is_enabled',FILTER_VALIDATE_BOOL)?1:0;

$error_exists=false;
do{
    if(!(new FormCsrfToken())->isValid()){
        $error_exists=true;
        ELog::error($page->title.": CSRFトークンエラー|".__FILE__);
        $page->addErrorMsg("登録編集フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
        break;
    }
    if($id>0 && $check_form_item===false){
        $error_exists=true;
        $page->debug_dump_var[]=['POST'=>$_POST];
        $page->addErrorMsg("{$base_title}設定ID '{$id}' が指定されていますが該当する{$base_title}がありません");
    }
}while(false);
if($error_exists){
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
