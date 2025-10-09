<?php
session_start();
require_once __DIR__.'/libs/init.php';
InAppUrl::init();
$page=new Page();
$setting=new Setting();
$page->setSetting($setting);
$page->title=SITE_NAME;
$session=new Session();
$session->login_return_url='';
// 暫定でログイン＝編集可能
$page->is_editable=SESSION::isLoggedIn();
if($setting->hasErrors){
    $page->setErrorReturnLink('インストーラーへ移動','./_setup/installer/login.php');
    $page->addErrorMsg('表示設定エラー');
    $page->addErrorMsgArray($setting->errorMessages);
    $page->addErrorMsg('テーブルが未作成の可能性があります');
}
$page->renderErrorsAndExitIfAny();
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?=h(SITE_NAME)?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?=$page->renderBaseStylesheetLinks()?>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php include (new TemplateImporter('index.inc.php'));?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>