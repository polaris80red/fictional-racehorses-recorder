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
$input_id=filter_input(INPUT_POST,'race_course_id',FILTER_VALIDATE_INT);
$race_course=new RaceCourse();
if($input_id>0){
    $race_course->getDataById($pdo,$input_id);
}
$race_course->unique_name=filter_input(INPUT_POST,'unique_name');
$race_course->short_name=filter_input(INPUT_POST,'short_name');
$race_course->short_name_m=filter_input(INPUT_POST,'short_name_m');
$race_course->sort_number=intOrNull(filter_input(INPUT_POST,'sort_number'));
$race_course->show_in_select_box=filter_input(INPUT_POST,'show_in_select_box',FILTER_VALIDATE_INT);
$race_course->is_enabled=filter_input(INPUT_POST,'is_enabled',FILTER_VALIDATE_BOOL)?1:0;

$error_exists=false;
do{
    if(!(new FormCsrfToken())->isValid()){
        $error_exists=true;
        Elog::error($page->title.": CSRFトークンエラー|".__FILE__);
        $page->addErrorMsg("登録編集フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
        break;
    }
    if($input_id>0 && !$race_course->record_exists){
        $error_exists=true;
        $page->debug_dump_var[]=['POST'=>$_POST];
        $page->addErrorMsg("{$base_title}設定ID '{$input_id}' が指定されていますが該当する{$base_title}がありません");
    }
    if($race_course->unique_name===''){
        $error_exists=true;
        $page->debug_dump_var[]=['POST'=>$_POST];
        $page->addErrorMsg("{$base_title}設定名称未設定");
        break;
    }
    if($race_course->short_name===''){
        $error_exists=true;
        $page->debug_dump_var[]=['POST'=>$_POST];
        $page->addErrorMsg("{$base_title}設定略称未設定");
        break;
    }
}while(false);
if($error_exists){
    $page->printCommonErrorPage();
    exit;
}
if($race_course->record_exists){
    // 編集モード
    $result = $race_course->UpdateExec($pdo);
    if($result){
        redirect_exit("./list.php");
    }
}else{
    // 新規登録モード
    $result = $race_course->InsertExec($pdo);
    if($result){
        redirect_exit("./list.php");
    }
}
