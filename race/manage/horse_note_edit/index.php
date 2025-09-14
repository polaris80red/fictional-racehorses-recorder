<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
InAppUrl::init(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース前後メモ一括編集";
if(!Session::is_logined()){ $page->exitToHome(); }

$page->error_return_url=$page->to_race_list_path;
$page->error_return_link_text="レース検索に戻る";

$pdo= getPDO();

$is_edit_mode = false;
if(filter_input(INPUT_GET,'mode')==='edit'){
    $is_edit_mode = true;
}
$is_edit_mode=true;
if(empty($_GET['race_id'])){
    $page->error_msgs[]="レースID未指定";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
$race_id=filter_input(INPUT_GET,'race_id');
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
    ?>
    <tr class="">
        <?php
            $is_affliationed_nar=0;
            if($raceResult->is_affliationed_nar===null){
                $is_affliationed_nar=$horse->is_affliationed_nar;
            }else{
                $is_affliationed_nar=$raceResult->is_affliationed_nar;
            }
            $marks=new Imploader('');
            if(($race->is_jra==1 || $race->is_nar==1)){
                // 中央競馬または地方競馬の場合、調教国・生産国でカク外・マル外マークをつける
                if($data->trainingCountry!='' && $data->trainingCountry!='JPN'){
                    // 外国調教馬にカク外表記
                    $marks->add("[外]");
                }else{
                    // 中央競馬の場合のみ地方所属馬と元地方所属馬のカク地・マル地マーク
                    if($race->is_jra){
                        if($is_affliationed_nar==1){
                            $marks->add("[地]");
                        }else if($is_affliationed_nar==2){
                            $marks->add("(地)");
                        }
                    }
                    // 外国産馬のマル外表記
                    if($horse->breeding_country!='' && $horse->breeding_country!='JPN'){
                        $marks->add("(外)");
                    }
                }
            }
            $a_tag=new MkTagA($horse->name_ja?:$horse->name_en);
            $a_tag->href($page->to_app_root_path.'horse/?horse_id='.$horse->horse_id);
            $country=($race->is_jra==0 && $race->is_nar==0)?" <span>(".h($data->trainingCountry).")</span> ":'';
        ?>
        <th class="horse_name" style="text-align: left;padding-left:1em;" colspan="2"><?=implode(' ',[$marks,$a_tag,$country])?></th>
    </tr>
    <tr>
        <th>前</th>
        <td class="in_input">
            <textarea name="race[<?=h($horse->horse_id)?>][race_previous_note]" style="min-width:400px;min-height: 2.5em;"><?=h($raceResult->race_previous_note)?></textarea>
        </td>
    </tr>
    <tr>
        <th>後</th>
        <td class="in_input">
            <textarea name="race[<?=h($horse->horse_id)?>][race_after_note]" style="min-width:400px;min-height: 2.5em;"><?=h($raceResult->race_after_note)?></textarea>
        </td>
    </tr>
<?php endforeach;?>
</table>
<input type="hidden" name="race_id" value="<?=$race_id?>">
<input type="submit" value="登録内容確認">
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>