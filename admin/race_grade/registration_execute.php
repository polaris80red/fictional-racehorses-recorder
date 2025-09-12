<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="レース格付マスタ";
$page->title="{$base_title}｜設定登録：処理実行";

$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$pdo=getPDO();
$id=filter_input(INPUT_POST,'race_grade_id',FILTER_VALIDATE_INT);
$race_grade=new RaceGradeRow();
$check_race_grade=false;
if($id>0){
    $check_race_grade=RaceGrade::getById($pdo,$id);
    $race_grade->id=$id;
}
$race_grade->unique_name=filter_input(INPUT_POST,'unique_name');
$race_grade->short_name=filter_input(INPUT_POST,'short_name');
$race_grade->search_grade=filter_input(INPUT_POST,'search_grade');
$race_grade->category=filter_input(INPUT_POST,'category');
$race_grade->css_class=filter_input(INPUT_POST,'css_class');
$race_grade->show_in_select_box=filter_input(INPUT_POST,'show_in_select_box',FILTER_VALIDATE_INT);
$race_grade->sort_number=filter_input(INPUT_POST,'sort_number');
if($race_grade->sort_number===''){
    $race_grade->sort_number=null;
}else{
    $race_grade->sort_number=(int)$race_grade->sort_number;
}
$race_grade->is_enabled=filter_input(INPUT_POST,'is_enabled',FILTER_VALIDATE_BOOL)?1:0;

$error_exists=false;
do{
    if(!(new FormCsrfToken())->isValid()){
        $error_exists=true;
        ELog::error($page->title.": CSRFトークンエラー|".__FILE__);
        $page->addErrorMsg("登録編集フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
        break;
    }
    if($id>0 && $check_race_grade===false){
        $error_exists=true;
        $page->debug_dump_var[]=['POST'=>$_POST];
        $page->addErrorMsg("{$base_title}設定ID '{$id}' が指定されていますが該当する{$base_title}がありません");
    }
    if($race_grade->unique_name===''){
        $error_exists=true;
        $page->debug_dump_var[]=['POST'=>$_POST];
        $page->addErrorMsg("{$base_title}設定名称未設定");
        break;
    }
}while(false);
if($error_exists){
    $page->printCommonErrorPage();
    exit;
}
if($check_race_grade!=false){
    // 編集モード
    $result = RaceGrade::UpdateFromRowObj($pdo,$race_grade);
    if($result){
        redirect_exit("./list.php");
    }
}else{
    // 新規登録モード
    $result = RaceGrade::InsertFromRowObj($pdo,$race_grade);
    if($result){
        redirect_exit("./list.php");
    }
}
