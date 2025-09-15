<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
InAppUrl::init(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース結果登録実行";
$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$race_id=(string)filter_input(INPUT_POST,'race_id');
$is_edit_mode=filter_input(INPUT_POST,'edit_mode')?1:0;
$horse_id=(string)filter_input(INPUT_POST,'horse_id')?:'';// 登録後に馬戦績登録時

if(!(new FormCsrfToken())->isValid()){
    ELog::error($page->title.": CSRFトークンエラー");
    $page->addErrorMsg("登録編集フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
    $page->printCommonErrorPage();
    exit;
}
# 対象取得
$race= new Race();
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
    $race->updated_at=PROCESS_STARTED_AT;
    if($is_edit_mode==1){
        $race->UpdateExec($pdo);
        $redirect_url=$page->getRaceResultUrl($race->race_id);
    }else{
        $race->created_at=PROCESS_STARTED_AT;
        $race->InsertExec($pdo);
        $redirect_url=$page->getRaceResultUrl($race->race_id);
        if($horse_id!==''){
            // 新規登録かつ競走馬ID指定の場合は個別結果登録画面に転送
            $redirect_url=InAppUrl::to(Routes::HORSE_RACE_RESULT_EDIT,['horse_id'=>$horse_id,'race_id'=>$race->race_id]);
        }
    }
    $pdo->commit();
    redirect_exit($redirect_url);
}catch(Exception $e){
    $pdo->rollBack();
    $page->addErrorMsg("Exception:".print_r($e,true));
    $page->printCommonErrorPage();
}
