<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース結果登録実行";
$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$race_id=(string)filter_input(INPUT_POST,'race_id');
$is_edit_mode=filter_input(INPUT_POST,'edit_mode')?1:0;

if(!(new FormCsrfToken())->isValid()){
    Elog::error($page->title.": CSRFトークンエラー");
    $page->addErrorMsg("登録編集フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
    $page->printCommonErrorPage();
    exit;
}
# 対象取得
$race= new RaceResults();
$pdo= getPDO();
$race->setDataById($pdo,$race_id);
if($is_edit_mode==0 && $race->record_exists){
    $page->addErrorMsg('新規モードで重複IDあり');
    $page->printCommonErrorPage();
    exit;
}

if($race->setDataByPost()==false){
    $page->debug_dump_var[]=$race;
    $page->addErrorMsgArray($race->error_msgs);
    $page->printCommonErrorPage();
    exit;
}

if(!$is_edit_mode){
    (new SurrogateKeyGenerator($pdo))->autoReset();
}
$pdo->beginTransaction();
try{
    // テーブル1つのみ・trancate実行可能性があるためbeginTransactionを行わない。
    if($is_edit_mode==1){
        $race->UpdateExec($pdo);
    }else{
        $race->InsertExec($pdo);
    }
    $pdo->commit();
    redirect_exit($page->getRaceResultUrl($race->race_id));
}catch(Exception $e){
    $pdo->rollBack();
    $page->addErrorMsg("Exception:".print_r($e,true));
    $page->printCommonErrorPage();
}
