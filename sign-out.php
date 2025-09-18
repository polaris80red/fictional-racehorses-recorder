<?php
session_start();
require_once __DIR__.'/libs/init.php';
$page=new Page();
$setting=new Setting(); 
$page->setSetting($setting);
$page->title="ログアウト - ".SITE_NAME;
$page->ForceNoindex();
$session=new Session();
$session->Logout();
$_SESSION[APP_INSTANCE_KEY]=[];
session_destroy();
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php $page->printTitle(); ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<p>ログアウトしました</p>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>
