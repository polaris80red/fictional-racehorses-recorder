<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
InAppUrl::init(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース前後メモ一括編集｜登録実行";
if(!Session::isLoggedIn()){ $page->exitToHome(); }

$page->error_return_url=$page->to_race_list_path;
$page->error_return_link_text="レース検索に戻る";

$pdo= getPDO();
if(empty($_POST['race_id'])){
    $page->error_msgs[]="レースID未指定";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
$race_id=filter_input(INPUT_POST,'race_id');
# レース情報取得
$race = new Race($pdo, $race_id);
if(!$race->record_exists){
    $page->error_msgs[]="レース情報取得失敗";
    $page->error_msgs[]="入力ID：{$race_id}";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
// 暫定的な編集権限判定（TODO: 他の処理を行クラス使用に揃える）
if(!Session::currentUser()->canEditRace(Race::getByRaceId($pdo,$race_id))){
    header("HTTP/1.1 403 Forbidden");
    $page->addErrorMsg("レース情報の編集権限がありません");
    $page->printCommonErrorPage();
    exit;
}
if(!Session::currentUser()->canEditOtherHorse()){
    // ほかのユーザーの競走馬の記録を編集できる権限がない場合は一括編集不可
    header("HTTP/1.1 403 Forbidden");
    $page->addErrorMsg("競走馬情報の編集権限がありません");
    $page->printCommonErrorPage();
    exit;
}
$page->error_return_url=InAppUrl::to('race/syutsuba.php',['race_id'=>$race_id]);
$page->error_return_link_text="出馬表に戻る";
if(!(new FormCsrfToken())->isValid()){
    ELog::error($page->title.": CSRFトークンエラー");
    $page->addErrorMsg("入力フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
    $page->printCommonErrorPage();
    exit;
}
$week_data=RaceWeek::getById($pdo,$race->week_id);
$week_month=$week_data->month;
$turn=$week_data->umm_month_turn;

$resultsGetter=new RaceResultsGetter($pdo,$race_id,$race->year);
$resultsGetter->pageIsEditable=$page->is_editable;
$resultsGetter->addOrderParts([
    "`r_results`.`frame_number` IS NULL",
    "`r_results`.`frame_number` ASC",
    "`r_results`.`horse_number` IS NULL",
    "`r_results`.`horse_number` ASC",
    "`horse`.`name_en` ASC",
]);
$table_data=$resultsGetter->getTableData();
$hasThisweek=$resultsGetter->hasThisweek;
$hasSps=$resultsGetter->hasSps;
$mode_umm=false;
switch($setting->age_view_mode){
    case Setting::AGE_VIEW_MODE_UMAMUSUME:
    case Setting::AGE_VIEW_MODE_UMAMUSUME_S:
        $mode_umm=true;
}
$has_change=false;

// レース前後メモの更新処理
$prev_is_changed = $after_is_changed = false;
$input_previous_note=(string)filter_input(INPUT_POST,'previous_note');
if((string)$race->previous_note!==$input_previous_note){
    $race->previous_note=$input_previous_note;
    $prev_is_changed = $has_change = true;
}
$input_after_note=(string)filter_input(INPUT_POST,'after_note');
if((string)$race->after_note!==$input_after_note){
    $race->after_note=$input_after_note;
    $after_is_changed = $has_change = true;
}
if($prev_is_changed||$after_is_changed){
    $race->updated_by=Session::currentUser()->getId();
    $race->updated_at=PROCESS_STARTED_AT;
    $race->UpdateExec($pdo);
}

// レース個別結果の前後メモの更新処理
$additionalData=[];
foreach($table_data as $key => $data){
    $has_change=false;
    $horse=$data->horseRow;
    $raceResult=$data->resultRow;
    $newResult= new RaceResults();
    $addData=new stdClass;
    $result = $newResult->setDataById($pdo,$race_id,$horse->horse_id);
    if(!$result){
        continue;
    }
    if(!isset($_POST['race'][$horse->horse_id])){
        // 送信データにその行の馬のデータが存在しない場合はスキップ
        continue;
    }
    $inputHorseResultRow=$_POST['race'][$horse->horse_id];
    $input='';
    $changed=[];
    if(isset($inputHorseResultRow['race_previous_note'])){
        $input = mb_convert_kana(trim($inputHorseResultRow['race_previous_note']),'n');
        if((string)$newResult->race_previous_note != $input){
            $newResult->race_previous_note = $input;
            $changed['race_previous_note'] = $has_change = true;
            if($input!==''){
                // 前メモの追加がある場合は元が前メモ0件でも有無フラグをオンにする
                $resultsGetter->hasPreviousNote=true;
            }
        }
    }
    if(isset($inputHorseResultRow['race_after_note'])){
        $input = mb_convert_kana(trim($inputHorseResultRow['race_after_note']),'n');
        if((string)$newResult->race_after_note != $input){
            $newResult->race_after_note = $input;
            $changed['race_after_note'] = $has_change = true;
            if($input!==''){
                // 後メモの追加がある場合は元が後メモ0件でも有無フラグをオンにする
                $resultsGetter->hasAfterNote=true;
            }
        }
    }
    if($has_change===true){
        $pdo->beginTransaction();
        try{
            $newResult->updated_at=PROCESS_STARTED_AT;
            $newResult->UpdateExec($pdo);
            $pdo->commit();
        }catch(Exception $e){
            $pdo->rollBack();
            $page->addErrorMsg("PDO_ERROR:".print_r($e,true));
            exit;
        }
        ELog::debug("race_note_edit| race:{$newResult->race_id},horse:{$newResult->horse_id}");        
    }
    $addData->newResult=$newResult;
    $addData->changed=$changed;
    $additionalData[$key]=$addData;
}
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink("js/functions.js"); ?>
    <style>
        th {
            background-color: #EEE;
            font-size: 90%;
        }
        td {
            font-size: 90%;
        }
        td.changed{ background-color: yellow; }
    </style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php echo $page->title; ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php include (new TemplateImporter('race/race_page-content_header.inc.php'));?>
<?php
    $prev_tag=new MkTagA('前メモ');
    if($resultsGetter->hasPreviousNote||$race->previous_note){
        $prev_tag->href(InAppUrl::to('race/race_previous_note.php',['race_id'=>$race_id]));
        $prev_tag->title("レース前メモ");
    }
    $line[]=$prev_tag;
    $after_tag=new MkTagA('後メモ');
    if($resultsGetter->hasAfterNote||$race->after_note){
        $after_tag->href(InAppUrl::to('race/race_after_note.php',['race_id'=>$race_id]));
        $after_tag->title("レース後メモ");
    }
    $line[]=$after_tag;
?>
<?=implode('｜',$line)?>
<form action="confirm.php" method="post">
<table>
<tr>
    <th colspan="2">レース</th>
</tr>
<tr>
    <th>前</th>
    <td style="min-width:200px;max-width:400px;" class="<?=$prev_is_changed?'changed':''?>">
        <?=nl2br(h($race->previous_note))?>
        <input type="hidden" name="previous_note" value="<?=h($race->previous_note)?>">
    </td>
</tr>
<tr>
    <th>後</th>
    <td style="min-width:200px;max-width:400px;" class="<?=$after_is_changed?'changed':''?>">
        <?=nl2br(h($race->after_note))?>
        <input type="hidden" name="after_note" value="<?=h($race->after_note)?>">
    </td>
</tr>
<?php foreach ($table_data as $key => $data):?>
    <?php
        if(!isset($_POST['race'][$horse->horse_id])){
            // 送信データにその行の馬のデータが存在しない場合はスキップ
            continue;
        }
        $horse=$data->horseRow;
        $raceResult=$data->resultRow;
        $changed=$additionalData[$key]->changed;
        $newResult=$additionalData[$key]->newResult;
    ?>
    <tr class="">
        <th class="horse_name" style="text-align: left;padding-left:1em;" colspan="2"><?=$horse->name_ja?:$horse->name_en?></th>
    </tr>
    <tr>
        <th>前</th>
        <td class="<?=($changed['race_previous_note']??false)?'changed':''?>" style="min-width:200px;max-width:400px;">
            <?=nl2br(h($newResult->race_previous_note))?>
            <input type="hidden" name="race[<?=h($horse->horse_id)?>][race_previous_note]" value="<?=h($newResult->race_previous_note)?>">
        </td>
    </tr>
    <tr>
        <th>後</th>
        <td class="<?=($changed['race_after_note']??false)?'changed':''?>">
            <?=nl2br(h($newResult->race_after_note))?>
            <input type="hidden" name="race[<?=h($horse->horse_id)?>][race_after_note]" value="<?=h($newResult->race_after_note)?>">
        </td>
    </tr>
<?php endforeach;?>
</table>
</form>
<?=implode('｜',$line)?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>