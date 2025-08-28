<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
defineAppRootRelPath(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="ログイン";
$page->ForceNoindex();
$session=new Session();

// ALLOW_REMOTE_EDITOR_LOGIN で許可されていない場合、localhost以外からのログインは拒否する
$login_is_disabled=(function(){
    if(ALLOW_REMOTE_EDITOR_LOGIN===true){
        return false;
    }
    if(is_remote_access()){
        header('HTTP/1.1 403 Forbidden');
        return true;
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
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php $page->printTitle();  ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<form action="./execute.php" method="post">
<?php if($login_is_disabled): ?>
<p style="color: #CC0000;">
Access denied:<br>
実行中のコンピューター以外からのログインは設定で禁止されています。</p>
<?php endif; ?>
<table>
    <tr>
        <th>ID</th>
        <td class="in_input"><input type="text" name="id" style="width:10em;" value=""<?php echo $login_is_disabled?' disabled':''; ?>></td>
    </tr>
    <tr>
        <th>パス</th>
        <td class="in_input"><input type="password" name="password" style="width:10em;" value=""<?php echo $login_is_disabled?' disabled':''; ?>></td>
    </tr>
</table>
<hr>
<input type="hidden" name="return_url" value="">
<?php (new FormCsrfToken())->printHiddenInputTag(); ?>
<input type="submit" value="実行"<?php echo $login_is_disabled?' disabled':''; ?>>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>
