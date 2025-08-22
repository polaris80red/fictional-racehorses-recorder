<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
defineAppRootRelPath(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="競走馬登録実行";
$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$horse_id=(string)filter_input(INPUT_POST,'horse_id');
$is_edit_mode=filter_input(INPUT_POST,'edit_mode')?1:0;

$is_error=false;
/*
if(empty($horse_id)){
    $is_error=true;
    $page->error_msgs[]="競走馬ID未入力";
}
*/
if(!(new FormCsrfToken())->isValid()){
    Elog::error($page->title.": CSRFトークンエラー");
    $page->addErrorMsg("登録編集フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
    $page->printCommonErrorPage();
    exit;
}
$pdo= getPDO();
// 既存データ取得
$horse= new Horse();
$horse->setDataById($pdo,$horse_id);
if(!$horse->record_exists){
    $is_edit_mode=0;
    $horse->horse_id=$horse_id;
}else{
    $is_edit_mode=1;
}
if($horse->setDataByPost()==false){
    $page->debug_dump_var[]=$horse;
    $page->addErrorMsgArray($horse->error_msgs);
    $page->printCommonErrorPage();
    exit;
}
if(!$is_edit_mode){
    (new SurrogateKeyGenerator($pdo))->autoReset();
}
$pdo->beginTransaction();
try{
    if($is_edit_mode){
        $horse->UpdateExec($pdo);
    }else{
        $horse->InsertExec($pdo);
    }

    // タグの登録更新処理
    $horse_tags=HorseTag::TagsStrToArray(filter_input(INPUT_POST,'horse_tags'));
    (new HorseTag($pdo))->updateHorseTags($horse->horse_id,$horse_tags,PROCESS_STARTED_AT);

    $pdo->commit();
    redirect_exit("{$page->to_app_root_path}horse/?horse_id={$horse->horse_id}");
}catch(Exception $e){
    ELog::error("{$page->title}|",$e);
    $pdo->rollBack();
    $page->addErrorMsg("Exception:".print_r($e,true));
    $page->printCommonErrorPage();
}
