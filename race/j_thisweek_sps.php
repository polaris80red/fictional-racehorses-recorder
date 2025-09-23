<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
InAppUrl::init(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="スペシャル出馬表(紹介文)";
$session=new Session();
// 暫定でログイン＝編集可能
$page->is_editable=Session::isLoggedIn();

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
    "`frame_number` IS NULL",
    "`frame_number` ASC",
    "`horse_number` IS NULL",
    "`horse_number` ASC",
    "`horse`.`name_ja` ASC",
    "`horse`.`name_en` ASC",
]);
$table_data=$resultsGetter->getTableData();
$hasThisweek=$resultsGetter->hasThisweek;
$hasSps=$resultsGetter->hasSps;
if(!$hasSps){
    $page->error_return_url=InAppUrl::to('race/syutsuba.php',['race_id'=>$race_id]);
    $page->error_return_link_text="出馬表に戻る";
    $page->error_msgs[]="スペシャル出馬表紹介文が未登録のレースです";
    $page->error_msgs[]="入力ID：{$race_id}";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
<style>
p {font-size:90%;}
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php include (new TemplateImporter('race/race_page-content_header.inc.php'));?>
<?php foreach ($table_data as $data):?><?php
    $horse=$data->horseRow;
    $result=$data->resultRow;
    $sex_str=sex2String($result->sex?:$horse->sex);
    $age=$horse->birth_year==null?'':$race->year-$horse->birth_year;
    ?>
<section>
<p>
<?php if($page->is_editable): ?>
<a href="<?=InAppUrl::to(Routes::HORSE_RACE_RESULT_EDIT,['race_id'=>$race_id,'horse_id'=>$horse->horse_id,'edit_mode'=>1])?>">■</a>
<?php else: ?>■
<?php endif; ?>
<?php if(!empty($result->frame_number)): ?>
<span style="border:solid 1px #333; padding-left:0.3em; padding-right:0.3em; margin-right:0.3em;" class="<?=h("waku_".$result->frame_number)?>"> <?=h($result->frame_number."枠")?></span><?=h( empty($result->horse_number)?"":(str_pad($result->horse_number,2,"0",STR_PAD_LEFT)."番 "))?>
<?php endif; ?>
<?php
    $training_country=$data->trainingCountry;
    if(($race->is_jra==1 || $race->is_nar==1) && $training_country!='' && $training_country!='JPN'){
        echo "[外] ";
    }
    if($race->is_jra==1 && $result->is_affliationed_nar==1){
        echo "[地] ";
    }
    if($race->is_jra==0 && $race->is_nar==0){
        echo "<span style=\"font-family:monospace;\">[".h($training_country)."]</span> ";
    }
    echo '<a href="'.InAppUrl::to('horse/',['horse_id'=>$horse->horse_id]).'" style="text-decoration:none;">';
    print_h($horse->name_ja?:$horse->name_en);
    echo "</a><br>";
    echo "調教師：".h($data->trainerName?:"□□□□");
    print_h("（".($result->tc?:$horse->tc)."）");
?><br>
父：<?=h($horse->sire_name?:"□□□□□□")?><br>
母：<?=h($horse->mare_name?:"□□□□□□")?><br>
母の父：<?=h($horse->bms_name?:"□□□□□□")?><br>
<?=h($sex_str.$age."歳")?>
<?=h($horse->color?("/".$horse->color):'')?>
<?=h($result->handicap?(" ".$result->handicap."kg"):'')?>
</p>
<p>［紹介］<br><?=nl2br(h($result->jra_sps_comment?:"……"))?></p>
</section>
<hr>
<?php endforeach;?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>