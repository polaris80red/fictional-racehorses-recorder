<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
defineAppRootRelPath(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="騎手マスタ管理｜キー名称の一括変更";
$page->ForceNoindex();
$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }
if(!Session::currentUser()->canManageMaster()){
    header("HTTP/1.1 403 Forbidden");
    $page->addErrorMsg('マスタ管理権限がありません');
}
$unique_name=filter_input(INPUT_GET,'u_name');

$pdo= getPDO();
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
?><!DOCTYPE html>
<html>
<head>
    <title><?=h($page->renderTitle())?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink('js/functions.js'); ?>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<form action="confirm.php" method="post">
<table class="edit-form-table">
<ul>
    <li>騎手[<?=h($unique_name)?>]が存在すればキー名を新名称に変更します。</li>
    <li>騎手[<?=h($unique_name)?>]が指定されたレースの騎手名を一括で新名称に変更します。</li>
</ul>
<tr>
    <th>変更対象</th>
    <td style="min-width:15em;"><?php HTPrint::HiddenAndText('u_name',$unique_name); ?></td>
</tr>
<tr>
    <th>新名称</th>
    <td class="in_input"><input type="text" name="new_unique_name" value=""></td>
</tr>
</table>
<hr>
<input type="submit" value="処理内容確認">
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>
