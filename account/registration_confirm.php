<?php
session_start();
require_once dirname(__DIR__,1).'/libs/init.php';
InAppUrl::init(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="ユーザー情報設定：内容確認";
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
$old_password=(string)filter_input(INPUT_POST,'old_password');
if(!password_verify($old_password,$form_item->password_hash)){
    $page->addErrorMsg('元のパスワードが間違っています');
}
$password=(string)filter_input(INPUT_POST,'password');
$password2=(string)filter_input(INPUT_POST,'password_2');
if($password !== filter_input(INPUT_POST,'password_2')){
    $page->addErrorMsg('新パスワードの再入力が一致していません');
}
if($password===''){
    $page->addErrorMsg('新パスワード未送信');
}
do{
    if(!$form_item->validate()){
        $page->addErrorMsgArray($form_item->errorMessages);
        break;
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle();  ?></title>
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
<h1 class="page_title"><?php $page->printTitle(); ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<form method="post" action="./registration_execute.php">
<table class="edit-form-table">
<tr>
    <th>ログインユーザー名</th>
    <td><?=h($form_item->username)?></td>
</tr>
<tr>
    <th>パスワード</th>
    <td><?php HTPrint::Hidden('password',$password); ?><?=str_repeat('*',mb_strlen($password))?></td>
</tr>
<tr><td colspan="2" style="text-align: left;"><input type="submit" value="登録実行"></td></tr>
</table>
<?php (new FormCsrfToken())->printHiddenInputTag(); ?>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>