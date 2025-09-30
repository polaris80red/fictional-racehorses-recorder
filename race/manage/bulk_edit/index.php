<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
InAppUrl::init(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース個別結果一括編集";
if(!Session::isLoggedIn()){ $page->exitToHome(); }

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
$race = Race::getByRaceId($pdo, $race_id);
if(!$race){
    $page->error_msgs[]="レース情報取得失敗";
    $page->error_msgs[]="入力ID：{$race_id}";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
if(!Session::currentUser()->canEditOtherHorse()){
    // ほかのユーザーの競走馬の記録を編集できる権限がない場合は一括編集不可
    header("HTTP/1.1 403 Forbidden");
    $page->addErrorMsg("編集権限がありません");
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
    <title><?=h($page->renderTitle())?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?=$page->renderBaseStylesheetLinks()?>
    <?=$page->renderJqueryResource()?>
    <?=$page->renderScriptLink("js/functions.js")?>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php include (new TemplateImporter('race/race_page-content_header.inc.php'));?>
<form action="confirm.php" method="post">
<table class="race_results">
<tr>
    <th>枠</th>
    <th>馬<br>番</th>
    <th>着<br>順</th>
    <th style="min-width:12em;">馬名</th>
    <th><?=h($mode_umm?"級":"性齢")?></th>
    <th>負担<br>重量</th>
    <th>騎手</th>
    <th>タイム</th>
    <th>着差</th>
    <th colspan="4">通過順位</th>
    <th>上り</th>
    <th>所属<br>上書</th>
    <th>調教師<br>上書</th>
    <th>馬体重</th>
    <th>単勝</th>
    <th>人気</th>
    <th>賞金</th>
    <th>収得</th>
</tr>
<?php foreach ($table_data as $data):?>
    <?php
        $horse=$data->horseRow;
        $raceResult=$data->resultRow;
    ?>
    <tr class="">
        <td class="in_input">
            <input type="number" name="race[<?=h($horse->horse_id)?>][frame_number]" style="width:2em;" value="<?=h($raceResult->frame_number)?>" placeholder="枠">
        </td>
        <td class="in_input">
            <input type="number" name="race[<?=h($horse->horse_id)?>][horse_number]" style="width:2.5em;" value="<?=h($raceResult->horse_number)?>" placeholder="番">
        </td>
        <td class="in_input">
            <input type="text" name="race[<?=h($horse->horse_id)?>][result]" style="width:3em;" value="<?=h($raceResult->result_text?:$raceResult->result_number)?>" placeholder="着順">
        </td>
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
        <td class="horse_name">　<?=implode(' ',[$marks,$a_tag,$country])?></td>
        <?php
            $age_sex_str='';
            if($setting->age_view_mode===Setting::AGE_VIEW_MODE_DEFAULT){
                // 通常表記の場合
                $age_sex_str.=$data->sexStr;
            }
            $age_sex_str.=$setting->getAgeSexSpecialFormat($data->age,$data->sex);
        ?>
        <td class="sex_<?=h($data->sex)?>"><?=h($age_sex_str)?></td>
        <td class="in_input">
            <input type="text" name="race[<?=h($horse->horse_id)?>][handicap]" style="width:2.5em;" value="<?=h($raceResult->handicap)?>" placeholder="斤">
        </td>
        <td class="in_input">
            <input type="text" name="race[<?=h($horse->horse_id)?>][jockey_name]" style="width:8em;" value="<?=h($raceResult->jockey_name)?>" placeholder="騎手">
        </td>
        <td class="in_input">
            <input type="text" name="race[<?=h($horse->horse_id)?>][time]" style="width:3.5em;" value="<?=h($raceResult->time)?>" placeholder="タイム">
        </td>
        <td class="in_input">
            <input type="text" name="race[<?=h($horse->horse_id)?>][margin]" style="width:3em;" value="<?=h($raceResult->margin)?>" placeholder="着差">
        </td>
        <td class="in_input">
            <input type="number" name="race[<?=h($horse->horse_id)?>][corner_1]" style="width:2.5em;" value="<?=h($raceResult->corner_1?:'')?>" placeholder="1角">
        </td>
        <td class="in_input">
            <input type="number" name="race[<?=h($horse->horse_id)?>][corner_2]" style="width:2.5em;" value="<?=h($raceResult->corner_2?:'')?>" placeholder="2角">
        </td>
        <td class="in_input">
            <input type="number" name="race[<?=h($horse->horse_id)?>][corner_3]" style="width:2.5em;" value="<?=h($raceResult->corner_3?:'')?>" placeholder="3角">
        </td>
        <td class="in_input">
            <input type="number" name="race[<?=h($horse->horse_id)?>][corner_4]" style="width:2.5em;" value="<?=h($raceResult->corner_4?:'')?>" placeholder="4角">
        </td>
        <td class="in_input">
            <input type="text" name="race[<?=h($horse->horse_id)?>][f_time]" style="width:2.5em;" value="<?=h($raceResult->f_time)?>" placeholder="上り">
        </td>
        <td class="in_input">
            <input type="text" name="race[<?=h($horse->horse_id)?>][tc]" style="width:2.5em;" value="<?=h($raceResult->tc)?>" placeholder="所属">
        </td>
        <td class="in_input">
            <input type="text" name="race[<?=h($horse->horse_id)?>][trainer_name]" style="width:8em;" value="<?=h($raceResult->trainer_name)?>" placeholder="調教師">
        </td>
        <td class="in_input">
            <input type="number" name="race[<?=h($horse->horse_id)?>][h_weight]" style="width:3.5em;" value="<?=h($raceResult->h_weight)?>" placeholder="体重">
        </td>
        <td class="in_input">
            <input type="text" name="race[<?=h($horse->horse_id)?>][odds]" style="width:2.5em;" value="<?=h($raceResult->odds)?>" placeholder="単勝">
        </td>
        <td class="in_input">
            <input type="number" name="race[<?=h($horse->horse_id)?>][favourite]" style="width:2.5em;" value="<?=h($raceResult->favourite)?>" placeholder="">
        </td>
        <td class="in_input">
            <input type="number" name="race[<?=h($horse->horse_id)?>][earnings]" style="width:4em;" value="<?=h($raceResult->earnings)?>" placeholder="万円">
        </td>
        <td class="in_input">
            <input type="number" name="race[<?=h($horse->horse_id)?>][syuutoku]" style="width:4em;" value="<?=h($raceResult->syuutoku?:'')?>" placeholder="万円">
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