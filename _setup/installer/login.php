<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
require_once __DIR__.'/libs/common.inc.php';
InAppUrl::init(2);
$page=new Page(2);
$page->title="インストーラー｜ログイン";
$page->ForceNoindex();

$userName=(string)filter_input(INPUT_POST,'username');
$password=(string)filter_input(INPUT_POST,'password');
do{
    $errorMessage='';
    if($userName===''){
        // 空の場合はログイン実行扱いしない
        break;
    }
    // ユーザー名がある場合にログイン判定
    if($userName!==ADMINISTRATOR_USER){
        $errorMessage='ユーザーまたはパスワードを確認してください';
        break;
    }
    if(($password==='' && ADMINISTRATOR_PASS==='')){
        if(ALLOW_REMOTE_EDITOR_LOGIN){
            $errorMessage='リモートログインを許可している場合は管理者パスワードの設定が必須です';
            break;
        }
    }else if(!password_verify($password,ADMINISTRATOR_PASS)){
        $errorMessage='ユーザーまたはパスワードを確認してください';
        break;
    }
    InstallerSession::login();
    header("Location: ./");
    exit;
}while(false);
$loginDisabled=false;
if(ALLOW_REMOTE_EDITOR_LOGIN && ADMINISTRATOR_PASS===''){
    $loginDisabled=true;
    $disabledMessage ="リモートログインを許可している場合は管理者パスワードの設定が必須です。\n";
    $disabledMessage.="config.inc.phpを確認してください。";
}
$page->renderErrorsAndExitIfAny();
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?=h($page->renderTitle())?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <link rel="stylesheet" type="text/css" href="./style.css">
    <?=$page->getMetaNoindex()?>
</head>
<body>
<header>
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php if($errorMessage):?>
<div class="error_message_box">ログイン失敗：<?=nl2br(h($errorMessage))?></div>
<?php endif;?>
<?php if($loginDisabled):?>
<div class="error_message_box"><?=nl2br(h($disabledMessage))?></div>
<?php endif;?>
<form action="#" method="post">
    <table>
        <tr>
            <th>ユーザー名</th>
            <td><input type="text" name="username" value="" required<?=$loginDisabled?' disabled':''?>></td>
        </tr>
        <tr>
            <th>パスワード</th>
            <td><input type="password" name="password" value=""<?=$loginDisabled?' disabled':''?>></td>
        </tr>
        <tr><td colspan="2" style="text-align: right;">
            <input type="submit" value="送信"<?=$loginDisabled?' disabled':''?>>
        </td></tr>
    </table>
</form>
<hr class="no-css-fallback">
</main>
<footer>
</footer>
</body>
</html>