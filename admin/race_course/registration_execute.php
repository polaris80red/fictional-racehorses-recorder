<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="競馬場マスタ";
$page->title="{$base_title}設定登録：処理実行";

$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$pdo=getPDO();
$id=filter_input(INPUT_POST,'race_course_id',FILTER_VALIDATE_INT);
$edit_target_race_course=false;
$race_course=new RaceCourseRow();
if($id>0){
    $edit_target_race_course=RaceCourse::getById($pdo,$id);
    $race_course->id=$id;
}
$race_course->unique_name=filter_input(INPUT_POST,'unique_name');
$race_course->short_name=filter_input(INPUT_POST,'short_name');
$race_course->short_name_m=filter_input(INPUT_POST,'short_name_m');
$race_course->show_in_select_box=filter_input(INPUT_POST,'show_in_select_box',FILTER_VALIDATE_INT);
$race_course->sort_priority=filter_input(INPUT_POST,'sort_priority');
$race_course->sort_number=filter_input(INPUT_POST,'sort_number');
if($race_course->sort_number===''){
    $race_course->sort_number=null;
}else{
    $race_course->sort_number=(int)$race_course->sort_number;
}
$race_course->is_enabled=filter_input(INPUT_POST,'is_enabled',FILTER_VALIDATE_BOOL)?1:0;

do{
    if(!(new FormCsrfToken())->isValid()){
        ELog::error($page->title.": CSRFトークンエラー|".__FILE__);
        $page->addErrorMsg("登録編集フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
        break;
    }
    if($id>0 && $edit_target_race_course===false){
        $page->debug_dump_var[]=['POST'=>$_POST];
        $page->addErrorMsg("{$base_title}設定ID '{$input_id}' が指定されていますが該当する{$base_title}がありません");
    }
    if($race_course->unique_name===''){
        $page->debug_dump_var[]=['POST'=>$_POST];
        $page->addErrorMsg("{$base_title}設定名称未設定");
        break;
    }
    if(!$id && false!==RaceCourse::getByUniqueName($pdo,$race_course->unique_name)){
        $page->addErrorMsg("キー名 '{$race_course->unique_name}' は既に存在します");
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
if($edit_target_race_course!=false){
    // 編集モード
    $result = RaceCourse::UpdateFromRowObj($pdo,$race_course);
    if($result){
        redirect_exit("./list.php");
    }
}else{
    // 新規登録モード
    $result = RaceCourse::InsertFromRowObj($pdo,$race_course);
    if($result){
        redirect_exit("./list.php");
    }
}
