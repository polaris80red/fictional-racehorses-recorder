<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
InAppUrl::init(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース前メモ";
$session=new Session();
// 暫定でログイン＝編集可能
$page->is_editable=Session::is_logined();

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
    ArticleCounter::countup($pdo,'race_previous_note',$race_id);
}
$week_data=RaceWeek::getById($pdo,$race->week_id);
$week_month=$week_data->month;
$turn=$week_data->umm_month_turn;

$resultsGetter=new RaceResultsGetter($pdo,$race_id,$race->year);
$resultsGetter->addOrderParts([
    "`r_results`.`frame_number` IS NULL",
    "`r_results`.`frame_number` ASC",
    "`r_results`.`horse_number` IS NULL",
    "`r_results`.`horse_number` ASC",
    "`horse`.`name_ja` ASC",
    "`horse`.`name_en` ASC",
]);
$table_data=$resultsGetter->getTableData();
$hasThisweek=$resultsGetter->hasThisweek;
$hasSps=$resultsGetter->hasSps;
if(!$resultsGetter->hasPreviousNote && !$race->previous_note){
    $page->error_return_url=InAppUrl::to('race/syutsuba.php',['race_id'=>$race_id]);
    $page->error_return_link_text="出馬表に戻る";
    $page->error_msgs[]="レース前メモが未登録のレースです";
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
<?php if($race->previous_note): ?>
■ レース前メモ<br>
<?=nl2br(h($race->previous_note))?>
<hr>
<?php endif;?>
<?php $i=0; ?>
<?php foreach ($table_data as $data): ?>
    <?php
        $result = $data->resultRow;
        $horse = $data->horseRow;
    ?>
    <?php if($result->race_previous_note==''){ continue; }?>
    <?php if($i!==0): ?><hr><?php endif;?>
    <section>
        <?php if($page->is_editable): ?>
        <a href="<?=InAppUrl::to(Routes::HORSE_RACE_RESULT_EDIT,['race_id'=>$race_id,'horse_id'=>$horse->horse_id,'edit_mode'=>1])?>">■</a>
        <?php else: ?>■<?php endif; ?>
        <a href="<?=h(InAppUrl::to('horse/',['horse_id'=>$horse->horse_id]))?>" style="text-decoration:none;"><?=$horse->name_ja?:$horse->name_en?></a>
        <br>
        <?=nl2br(h($result->race_previous_note?:"……"))?>
    </section>
    <?php $i++; ?>
<?php endforeach; ?>
<?php if($page->is_editable): ?>
<hr>
[ <a href="<?=InAppUrl::to('race/manage/note_edit',['race_id'=>$race_id])?>">一括編集</a> ]
<?php endif;?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>