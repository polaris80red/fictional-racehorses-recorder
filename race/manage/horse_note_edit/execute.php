<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
InAppUrl::init(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース前後メモ一括編集｜登録実行";
if(!Session::is_logined()){ $page->exitToHome(); }

$page->error_return_url=$page->to_race_list_path;
$page->error_return_link_text="レース検索に戻る";

$pdo= getPDO();
if(!(new FormCsrfToken())->isValid()){
    ELog::error($page->title.": CSRFトークンエラー");
    $page->addErrorMsg("入力フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
    $page->printCommonErrorPage();
    exit;
}
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
<hr>
<form action="confirm.php" method="post">
<table>
<?php foreach ($table_data as $data):?>
    <?php
        $horse=$data->horseRow;
        $raceResult=$data->resultRow;
        $newResult= new RaceResults();
        $result = $newResult->setDataById($pdo,$race_id,$horse->horse_id);
        if(!$result){
            continue;
        }
        if(!isset($_POST['race'][$horse->horse_id])){
            // その馬のデータがなければスキップ
            continue;
        }else{
            $inputHorseResultRow=$_POST['race'][$horse->horse_id];
        }
        $input='';
        $changed=[];
        if(isset($inputHorseResultRow['race_previous_note'])){
            $input = mb_convert_kana(trim($inputHorseResultRow['race_previous_note']),'n');
            if((string)$newResult->race_previous_note != $input){
                $newResult->race_previous_note = $input;
                $changed['race_previous_note'] = $has_change = true;
            }
        }
        if(isset($inputHorseResultRow['race_after_note'])){
            $input = mb_convert_kana(trim($inputHorseResultRow['race_after_note']),'n');
            if((string)$newResult->race_after_note != $input){
                $newResult->race_after_note = $input;
                $changed['race_after_note'] = $has_change = true;
            }
        }
        if($has_change===true){
            $pdo->beginTransaction();
            try{
                $newResult->UpdateExec($pdo);
                $pdo->commit();
            }catch(Exception $e){
                $pdo->rollBack();
                $page->addErrorMsg("PDO_ERROR:".print_r($e,true));
                exit;
            }
            ELog::debug("race_note_edit| race:{$newResult->race_id},horse:{$newResult->horse_id}");        
        }
    ?>
    <tr class="">
        <th class="horse_name" style="text-align: left;padding-left:1em;" colspan="2"><?=$horse->name_ja?:$horse->name_en?></th>
    </tr>
    <tr>
        <th>前</th>
        <td class="<?=($changed['race_previous_note']??false)?'changed':''?>" style="min-width:200px;max-width:400px;">
            <?=h($newResult->race_previous_note)?>
            <input type="hidden" name="race[<?=h($horse->horse_id)?>][race_previous_note]" value="<?=h($newResult->race_previous_note)?>">
        </td>
    </tr>
    <tr>
        <th>後</th>
        <td class="<?=($changed['race_after_note']??false)?'changed':''?>">
            <?=h($newResult->race_after_note)?>
            <input type="hidden" name="race[<?=h($horse->horse_id)?>][race_after_note]" value="<?=h($newResult->race_after_note)?>">
        </td>
    </tr>
<?php endforeach;?>
</table>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>