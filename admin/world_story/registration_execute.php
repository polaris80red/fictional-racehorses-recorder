<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="ストーリー";
$page->title="{$base_title}設定登録：処理実行";

$pdo=getPDO();
$input_id=filter_input(INPUT_POST,'story_id',FILTER_VALIDATE_INT);
$story=new WorldStory();
if($input_id>0){
    $story->getDataById($pdo,$input_id);
}
$story->name=filter_input(INPUT_POST,'name');
$story->sort_priority=filter_input(INPUT_POST,'sort_priority',FILTER_VALIDATE_INT);

$story->sort_number=intOrNull(filter_input(INPUT_POST,'sort_number'));

$story->is_read_only=filter_input(INPUT_POST,'is_read_only',FILTER_VALIDATE_BOOL)?1:0;
$story->is_enabled=filter_input(INPUT_POST,'is_enabled',FILTER_VALIDATE_BOOL)?1:0;

$error_exists=false;
do{
    if($input_id>0 && !$story->record_exists){
        $error_exists=true;
        $page->debug_dump_var[]=['POST'=>$_POST];
        $page->addErrorMsg("{$base_title}設定ID '{$input_id}' が指定されていますが該当する{$base_title}がありません");
    }
    if($story->name===''){
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
if($story->record_exists){
    // 編集モード
    $result = $story->UpdateExec($pdo);
    if($result){
        redirect_exit("./list.php");
    }
}else{
    // 新規登録モード
    $result = $story->InsertExec($pdo);
    if($result){
        redirect_exit("./list.php");
    }
}
