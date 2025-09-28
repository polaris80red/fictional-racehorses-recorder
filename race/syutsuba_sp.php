<?php
/**
 * スペシャル出馬表風フォーマット
 */
session_start();
require_once dirname(__DIR__).'/libs/init.php';
InAppUrl::init(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="出馬表";
$session=new Session();
// 暫定でログイン＝編集可能
$page->is_editable=Session::isLoggedIn();
// ログイン中でも強制的にプレビュー表示にできるパラメータ
$is_preview=filter_input(INPUT_GET,'preview',FILTER_VALIDATE_BOOL);
if($is_preview){
    $page->is_editable=false;
}

$page->error_return_url=$page->to_race_list_path;
$page->error_return_link_text="レース検索に戻る";

$pdo= getPDO();

if(empty($_GET['race_id'])){
    $page->error_msgs[]="レースID未指定";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
$race_id=filter_input(INPUT_GET,'race_id');
$show_registration_only=(bool)filter_input(INPUT_GET,'show_registration_only');
# レース情報取得
$race = Race::getByRaceId($pdo, $race_id);
if(!$race){
    $page->error_msgs[]="レース情報取得失敗";
    $page->error_msgs[]="入力ID：{$race_id}";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
if(ENABLE_ACCESS_COUNTER){
    ArticleCounter::countup($pdo,ArticleCounter::TYPE_RACE_SYUTSUBA_SP,$race_id);
}
$week_data=RaceWeek::getById($pdo,$race->week_id);
$week_month=$week_data->month;
$turn=$week_data->umm_month_turn;

$rr_count=4;
$syutsuba_getter=new SyutsubaTableGetter($pdo);
$table_data=$syutsuba_getter->getSyutsubaData($race, $rr_count);
$hasThisweek=$syutsuba_getter->hasThisweek;
$hasSps=$syutsuba_getter->hasSps;

?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
<style>
td.race_result_column{
    min-width:135px;
}
td:nth-child(1){
    padding:0;
    padding-left:2px;
    padding-right:2px;
}
td:nth-child(-n+2){
    text-align:center;
}
td:nth-child(4){
    font-size:90%;
    text-align:center;
}
table.syutsuba.sps .ib.grade{
    display: inline-block;
    min-width:25px;
    text-align:center;
    padding-left:0.3em;
    padding-right:0.3em;
}
table.syutsuba.sps .result_number {
	font-size:1.75em;
    padding-bottom: 2px;
	font-weight:bold;
    padding-top:0.1em;
    width:3rem;
    text-align:center;
}
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
<?php include (new TemplateImporter('race/race-syutsuba_sp_table.inc.php'));?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>
