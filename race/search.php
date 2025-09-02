<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
InAppUrl::init(1);
defineAppRootRelPath(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース検索";
$page->ForceNoindex();
$search=(new RaceSearch())->setSetting($setting);
if(!filter_input(INPUT_GET,'search_reset',FILTER_VALIDATE_BOOL)){
    $search->setBySession();
}
$pdo=getPDO();

?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink('js/functions.js'); ?>
<style>
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php $page->printTitle(); ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php
    $search2=new RaceSearch();
    $search2->setBySession();
?>
<?php if(!$search2->is_empty()): ?>
<a href="<?=h(InAppUrl::to('race/list/',['set_by_session'=>true]))?>">最後の検索条件で検索</a>
<?php endif; ?>
<?php $search->printForm($page,false,true); ?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
<?php $page->printScriptLink('js/race_search_form.js'); ?>
</body>
</html>
