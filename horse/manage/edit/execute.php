<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
defineAppRootRelPath(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="競走馬登録実行";
$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }
$currentUser=Session::currentUser();

$horse_id=(string)filter_input(INPUT_POST,'horse_id');
$is_edit_mode=filter_input(INPUT_POST,'edit_mode')?1:0;

if(!(new FormCsrfToken())->isValid()){
    ELog::error($page->title.": CSRFトークンエラー");
    $page->addErrorMsg("登録編集フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
    $page->printCommonErrorPage();
    exit;
}
$pdo= getPDO();
// 既存データ取得
$horse= Horse::getByHorseId($pdo,$horse_id);
if(!$horse){ 
    $is_edit_mode=0;
    $horse=new HorseRow();
}else{
    $is_edit_mode=1;
}
$horse->setFromPost();
$horse->validate();
if($horse->hasErrors){
    $page->addErrorMsgArray($horse->errorMessages);
    $page->printCommonErrorPage();
    exit;
}
if(!$is_edit_mode){
    (new SurrogateKeyGenerator($pdo))->autoReset();
}
$horse->updated_by=$currentUser->getId();
$horse->updated_at=PROCESS_STARTED_AT;
$pdo->beginTransaction();
try{
    if($is_edit_mode){
        Horse::UpdateFromRowObj($pdo,$horse);
    }else{
        $horse->created_by=$horse->updated_by;
        $horse->created_at=$horse->updated_at;
        Horse::InsertFromRowObj($pdo,$horse);
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
