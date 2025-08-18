<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="ワールド登録：処理実行";

$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$pdo=getPDO();
$input_world_id=filter_input(INPUT_POST,'world_id',FILTER_VALIDATE_INT);
$world=new World();
if($input_world_id>0){
    $world->getDataById($pdo,$input_world_id);
}
$world->name=filter_input(INPUT_POST,'name');
$world->use_exact_date=filter_input(INPUT_POST,'use_exact_date');
$world->sort_priority=filter_input(INPUT_POST,'sort_priority',FILTER_VALIDATE_INT);
$world->is_enabled=filter_input(INPUT_POST,'is_enabled',FILTER_VALIDATE_BOOL)?1:0;

$error_exists=false;
do{
    if(!(new FormCsrfToken())->isValid()){
        $error_exists=true;
        Elog::error($page->title.": CSRFトークンエラー|".__FILE__);
        $page->addErrorMsg("登録編集フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
        break;
    }
    if($input_world_id>0 && !$world->record_exists){
        $error_exists=true;
        $page->debug_dump_var[]=['POST'=>$_POST];
        $page->addErrorMsg("ワールドID '{$input_world_id}' が指定されていますが該当するワールドがありません");
    }
    if($world->name===''){
        $error_exists=true;
        $page->debug_dump_var[]=['POST'=>$_POST];
        $page->addErrorMsg('ワールド名未設定');
        break;
    }
}while(false);
if($error_exists){
    $page->printCommonErrorPage();
    exit;
}
if($world->record_exists){
    // 編集モード
    $result = $world->UpdateExec($pdo);
    if($result){
        redirect_exit("./list.php");
    }
}else{
    // 新規登録モード
    $result = $world->InsertExec($pdo);
    if($result){
        redirect_exit("./list.php");
    }
}
