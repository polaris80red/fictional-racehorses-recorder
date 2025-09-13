<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
InAppUrl::init(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="出馬表";
$session=new Session();
// 暫定でログイン＝編集可能
$page->is_editable=Session::is_logined();
// ログイン中でも強制的にプレビュー表示にできるパラメータ
$is_preview=filter_input(INPUT_GET,'preview',FILTER_VALIDATE_BOOL);
if($is_preview){
    $page->is_editable=false;
}

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
if(ENABLE_ACCESS_COUNTER){
    ArticleCounter::countup($pdo,ArticleCounter::TYPE_RACE_SYUTSUBA_SIMPLE,$race_id);
}
$session->latest_race=[
    'id'=>$race_id,
    'year'=>$race->year,
    'name'=>$race->race_short_name?:$race->race_name
];
$session->login_return_url='race/syutsuba_simple.php?race_id='.$race_id;
$race_access_history=(new RaceAccessHistory())->set($race_id)->saveToSession();

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
.race_results td:nth-child(1){ text-align:center; }
.race_results td:nth-child(2){ text-align:center; }
.race_results td:nth-child(4){ text-align:center; }
.race_results td.col_favourite{ text-align:center; }
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
<?php
$empty_row_2="<td>&nbsp;</td><td></td><td class=\"horse_name\"></td><td></td><td></td><td></td><td></td>";
?><table class="race_results">
<tr>
<th>枠</th><th>馬番</th>
<th style="min-width:12em;">馬名</th>
<th><?=h($mode_umm?"級":"性齢")?></th>
<th>負担<br>重量</th>
<?php if(!$mode_umm): ?><th>騎手</th><?php endif; ?>
<th>所属</th>
<?php if(!$mode_umm): ?><th>調教師</th><?php endif; ?>
<?php if(!$mode_umm): ?><th>馬体重</th><?php endif; ?>
<th>人気</th>
<?php if($page->is_editable): ?><th>編</th><?php endif; ?>
</tr><?php
$i=0;
$latest_horse_exists=false;
foreach ($table_data as $data) {
    $i++;
    $horse=$data->horseRow;
    $raceResult=$data->resultRow;
    if($horse->horse_id==($session->latest_horse['id']??'')){
        $latest_horse_exists=true;
    }
    // 特別登録のみのデータはスキップ
    if($data->specialResultRow->is_registration_only){
        continue;
    }
?><tr class="">
<td class="waku_<?=$raceResult->frame_number?>"><?=$raceResult->frame_number?></td>
<td><?=$raceResult->horse_number?></td>
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
<td class="horse_name"><?=implode(' ',[$marks,$a_tag,$country])?></td>
<?php
    $age_sex_str='';
    if($setting->age_view_mode===Setting::AGE_VIEW_MODE_DEFAULT){
        // 通常表記の場合
        $age_sex_str.=$data->sexStr;
    }
    $age_sex_str.=$setting->getAgeSexSpecialFormat($data->age,$data->sex);
?>
<td class="sex_<?=h($data->sex)?>"><?=h($age_sex_str)?></td>
<td><?=h($raceResult->handicap)?></td>
<?php if(!$mode_umm): ?>
    <td style="<?=$data->jockeyRow->is_anonymous?'color:#999;':''?>"><?=h($data->jockeyName??'')?></td>
<?php endif; ?>
<td><?=h($data->tc)?></td>
<?php if(!$mode_umm): ?>
    <td style="<?=$data->trainerRow->is_anonymous?'color:#999;':''?>">
        <?=h($data->trainerName??'')?>
    </td>
<?php endif; ?>
<?php if(!$mode_umm): ?><td><?php /* 馬体重 */ ?></td><?php endif; ?>
<td class="col_favourite favourite_<?=h($raceResult->favourite)?>"><?=h($raceResult->favourite)?></td>
<?php
    if(!empty($horse->horse_id)){
        $url=InAppUrl::to(Routes::HORSE_RACE_RESULT_EDIT,['race_id'=>$race->race_id,'horse_id'=>$horse->horse_id,'edit_mode'=>1]);
    }
?>
<?php if($page->is_editable): ?>
<td><a href="<?=h($url)?>" title="編集">編</a></td>
<?php endif; ?>
</tr>
<?php } ?></table>
<hr>
<a href="<?=h($page->getRaceNameSearchUrl($race->race_name))?>" style="">他年度の<?=h($race->race_name)?>を検索</a>
<?php if($page->is_editable): ?>
    <?php include (new TemplateImporter('race/race_page-edit_menu.inc.php'));?>
<?php endif; ?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>