<?php
session_start();
require_once dirname(__DIR__,1).'/libs/init.php';
InAppUrl::init(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="ユーザー情報設定";
$page->ForceNoindex();

if(!Session::isLoggedIn()){ $page->exitToHome(); }

$pdo=getPDO();
$inputId=Session::currentUser()->getId();

$TableClass=Users::class;
$TableRowClass=$TableClass::ROW_CLASS;
$form_item=($TableClass)::getById($pdo,$inputId);
if(!$form_item){
    $page->addErrorMsg("ID '{$inputId}' が指定されていますが該当するレコードがありません");
}
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
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
自分のログインパスワードを変更します。
<form method="post" action="./registration_confirm.php">
<table class="edit-form-table">
<tr>
    <th>ログインユーザー名</th>
    <td><?=h($form_item->username)?></td>
</tr>
<tr>
    <th>現在のパスワード</th>
    <td class="in_input"><input type="password" name="old_password" class="required" value="" required></td>
</tr>
<tr>
    <th>新パスワード</th>
    <td class="in_input"><input type="password" name="password" class="required" value="" required></td>
</tr>
<tr>
    <th>パスワード再入力</th>
    <td class="in_input"><input type="password" name="password_2" class="required" value="" required></td>
</tr>
<tr><td colspan="2" style="text-align: right;"><input type="submit" value="登録内容確認"></td></tr>
</table>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>