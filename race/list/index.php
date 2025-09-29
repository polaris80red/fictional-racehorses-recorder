<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting(); 
$page->setSetting($setting);
$page->title="レース検索";
$session=new Session();
// 暫定でログイン＝編集可能
$page->is_editable=Session::isLoggedIn();
$pdo= getPDO();

$search=(new RaceSearch())->setSetting($setting);
if(filter_input(INPUT_GET,'set_by_session',FILTER_VALIDATE_BOOL)){
    $search->setBySession();
}else{
    $search->setByUrl();
}
if($search->is_empty()){
    redirect_exit(APP_ROOT_REL_PATH."race/search.php");
}
$show_column_umm_turn=false;
$show_column_date=true;
if($setting->horse_record_date==='umm'){
    $show_column_umm_turn=true;
    $show_column_date=false;
}

$year=$search->year;
$search->world_id= $setting->world_id;
$stmt=$search->SelectExec($pdo);
if($year!==''){
    $prev=$year-1;
    $next=$year+1;
}
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?=h($page->renderTitle())?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink('js/functions.js'); ?>
<style>
td.grade{ text-align:center;}
td.race_course_name { text-align: center; }
.disabled_row{ background-color: #ccc; }
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php
// 1～3着馬を取得する処理
$race123horseGetter=new Race123HorseGetter($pdo);

$search_results=new RaceSearchResults($stmt);
$table_rows=$search_results->getAll();
$search->current_page_results_count=count($table_rows);
?>
<?php //$search->printForm($page,true,null); ?>
<!--<hr>-->
<a href="#foot" title="最下部検索フォームに移動" style="text-decoration:none;">▽検索結果</a>｜<?=h($search->getSearchParamStr())?>
<hr>
<?php if($year!==''): ?>
<a href="?year=<?=$prev?>&<?=h($search->getUrlParam(['year','page']))?>">[前年へ]</a>
<?=h($setting->getConvertedDate(['year'=>$year],'y'))?>
 <a href="?year=<?=$next?>&<?=h($search->getUrlParam(['year','page']))?>">[翌年へ]</a>
<hr>
<?php endif; ?>
<form method="get" action="<?=APP_ROOT_REL_PATH?>race/manage/duplicate/">
<?php if($page->is_editable && $search->is_one_year_only): ?>
<input type="button" value="全てチェック" onclick="toggleIdList();">
<input type="submit" value="チェックしたレースを一括複写">
<?php endif; ?>
<?php include (new TemplateImporter('race/race-search_results_table.inc.php'));?>
<?php if($search->limit>0): ?>
<hr>
<?=$search->printPagination()?>
<?php endif; ?>
<?php if($year!==''): ?>
<hr>
<a href="?year=<?=$prev?>&<?=h($search->getUrlParam(['year','page']))?>">[前年へ]</a>
<?=h($setting->getConvertedDate(['year'=>$year],'y'))?>
 <a href="?year=<?=$next?>&<?=h($search->getUrlParam(['year','page']))?>">[翌年へ]</a>
<?php endif; ?>
</form>
<hr><a id="foot"></a>
<?php $search->printForm($page,false,true); ?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
<?php $page->printScriptLink('js/race_search_form.js'); ?>
<script>
function toggleIdList() {
    const $targets = $('input[type="checkbox"][name^="id_list"]');
    const allChecked = $targets.length > 0 && $targets.filter(':checked').length === $targets.length;

    if (allChecked) {
        $targets.prop('checked', false);
    } else {
        $targets.prop('checked', true);
    }
}
</script>
</body>
</html>