<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="ログイン";
$page->ForceNoindex();
$session=new Session();

// ALLOW_REMOTE_EDITOR_LOGIN で許可されていない場合、localhost以外からのログインは拒否する
$login_disable_status=(function(){
    if(READONLY_MODE){
        return '閲覧専用モードのためログインできません。';
    }
    if(ALLOW_REMOTE_EDITOR_LOGIN===true){
        return false;
    }
    if(is_remote_access()){
        header('HTTP/1.1 403 Forbidden');
        return '実行中のコンピューター以外からのログインは設定で禁止されています。';
    }
})();

?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle();  ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
    <style>
        th{ background-color: #EEE; }
    </style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php $page->printTitle();  ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<form action="./execute.php" method="post">
<?php if($login_disable_status): ?>
<p style="color: #CC0000;">
Access denied:<br>
<?=h($login_disable_status)?></p>
<?php endif; ?>
<table>
    <tr>
        <th>ユーザー名(ID)</th>
        <td class="in_input"><input type="text" name="id" style="width:10em;" value=""<?php echo $login_disable_status?' disabled':''; ?> required></td>
    </tr>
    <tr>
        <th>パスワード</th>
        <td class="in_input"><input type="password" name="password" style="width:10em;" value=""<?php echo $login_disable_status?' disabled':''; ?>></td>
    </tr>
</table>
<hr>
<input type="hidden" name="return_url" value="">
<?php (new FormCsrfToken())->printHiddenInputTag(); ?>
<input type="submit" value="実行"<?php echo $login_disable_status?' disabled':''; ?>>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>
