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

$editMode=($id>0);
$TableClass=RaceSpecialResults::class;
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
