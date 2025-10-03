<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
InAppUrl::init(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース結果登録実行";
$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }

$race_id=(string)filter_input(INPUT_POST,'race_id');
$is_edit_mode=filter_input(INPUT_POST,'edit_mode')?1:0;
$horse_id=(string)filter_input(INPUT_POST,'horse_id')?:'';// 登録後に馬戦績登録時

if(!(new FormCsrfToken())->isValid()){
    ELog::error($page->title.": CSRFトークンエラー");
    $page->addErrorMsg("登録編集フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
    $page->printCommonErrorPage();
    exit;
}
$pdo= getPDO();
do{
    $race=false;
    if($is_edit_mode==1 && $race_id===''){
        $page->addErrorMsg("編集モードですが対象が指定されていません");
    }
    if($race_id!=''){
        $race=Race::getByRaceId($pdo,$race_id);
        if($is_edit_mode==0 && $race!==false){
            $page->addErrorMsg('新規モードで重複IDあり');
            break;
        }
        if($is_edit_mode==1){
            if($race===false){
                $page->addErrorMsg("編集対象の取得に失敗");
                break;
            }else if(!Session::currentUser()->canEditRace($race)){
                $page->addErrorMsg("編集権限がありません");
                break;
            }
        }
    }
    if($race===false){
        $race=new RaceRow();
    }
    $race->setFromPost();
    if(!$race->validate()){
        $page->addErrorMsgArray($race->errorMessages);
    }
}while(false);
$page->renderErrorsAndExitIfAny();

if(!$is_edit_mode){
    (new SurrogateKeyGenerator($pdo))->autoReset();
}
$pdo->beginTransaction();
try{
    $race->updated_by=Session::currentUser()->getId();
    $race->updated_at=PROCESS_STARTED_AT;
    if($is_edit_mode==1){
        Race::UpdateFromRowObj($pdo,$race);
        $redirect_url=$page->getRaceResultUrl($race->race_id);
    }else{
        $race->created_by=$race->updated_by;
        $race->created_at=$race->updated_at;
        Race::InsertFromRowObj($pdo,$race);
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
