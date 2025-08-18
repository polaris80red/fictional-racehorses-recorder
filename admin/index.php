<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
defineAppRootRelPath(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="管理画面 - ".SITE_NAME;
$session=new Session();

if(!$session->is_logined()){
    header('Location: '.APP_ROOT_REL_PATH.'sign-in/');
}
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?></title>
    <meta charset="UTF-8">
    <?php $page->printBaseStylesheetLinks(); ?>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php $page->printTitle(); ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<ul>
    <li><?php echo (new MkTagA("システム設定",APP_ROOT_REL_PATH.'setting/')); ?></li>
    <li><?php echo (new MkTagA("ワールド設定",APP_ROOT_REL_PATH.'admin/world/list.php')); ?></li>
    <li><?php echo (new MkTagA("ストーリー設定",APP_ROOT_REL_PATH.'admin/world_story/list.php')); ?></li>
    <li><?php echo (new MkTagA("競馬場マスタ設定",APP_ROOT_REL_PATH.'admin/race_course/list.php')); ?></li>
    <li><?php echo (new MkTagA("テーマ設定",APP_ROOT_REL_PATH.'admin/themes/list.php')); ?></li>
<?php if($pma_link->isAvailable()): ?>
    <li><?php echo (new MkTagA("phpMyAdmin：データベースのエクスポート",$pma_link->getDbExportUrl())); ?></li>
<?php endif; ?>
</ul>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>
