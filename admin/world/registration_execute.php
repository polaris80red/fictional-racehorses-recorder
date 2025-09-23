<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="ワールド登録：処理実行";

$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }

$pdo=getPDO();
$inputId=filter_input(INPUT_POST,'world_id',FILTER_VALIDATE_INT);
$editMode=($inputId>0);
$TableClass=World::class;
$TableRowClass=$TableClass::ROW_CLASS;

if($editMode){
    $page->title.="（編集）";
    $world=($TableClass)::getById($pdo,$inputId);
    if($world===false){
        $page->addErrorMsg("ワールドID '{$inputId}' が指定されていますが該当するワールドがありません");
    }
}else{
    $world=new ($TableRowClass)();
}
$world->name=filter_input(INPUT_POST,'name');
$world->guest_visible=filter_input(INPUT_POST,'guest_visible',FILTER_VALIDATE_BOOL)?1:0;
$world->auto_id_prefix=filter_input(INPUT_POST,'auto_id_prefix');
$world->sort_priority=filter_input(INPUT_POST,'sort_priority',FILTER_VALIDATE_INT);
$sort_number=(string)filter_input(INPUT_POST,'sort_number');
$world->sort_number = $sort_number===''?null:(int)$sort_number;
$world->is_enabled=filter_input(INPUT_POST,'is_enabled',FILTER_VALIDATE_BOOL)?1:0;

do{
    if(!(new FormCsrfToken())->isValid()){
        ELog::error($page->title.": CSRFトークンエラー|".__FILE__);
        $page->addErrorMsg("登録編集フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
        break;
    }
    if(!$world->validate()){
        $page->addErrorMsgArray($world->errorMessages);
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
if($editMode){
    // 編集モード
    $result = ($TableClass)::UpdateFromRowObj($pdo,$world);
    if($result){
        redirect_exit("./list.php");
    }
}else{
    // 新規登録モード
    $result = ($TableClass)::InsertFromRowObj($pdo,$world);
    if($result){
        redirect_exit("../world_story/form.php?".http_build_query(['world_id'=>$pdo->lastInsertId()]));
    }
}
