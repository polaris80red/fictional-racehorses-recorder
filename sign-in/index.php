<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="ログイン";
$page->ForceNoindex();

do{
    $pdo=getPDO();
    $nowDateTime=new DateTime(PROCESS_STARTED_AT);
    $errorHeader='HTTP/1.1 403 Forbidden';
    $login_disable_status='';
    if(READONLY_MODE){
        $login_disable_status='閲覧専用モードのためログインできません。';
        break;
    }
    // ALLOW_REMOTE_EDITOR_LOGIN で許可されていない場合、localhost以外からのログインは拒否する
    if(!ALLOW_REMOTE_EDITOR_LOGIN && is_remote_access()){
        $login_disable_status='実行中のコンピューター以外からのログインは設定で禁止されています。';
        break;
    }
    if(Session::isLoggedIn()){
        $login_disable_status ="既にログイン中です。";
        break;
    }
    $login_url_token=(string)filter_input(INPUT_GET,'t');
    if($login_url_token){
        $page->title.="（個別ユーザー用）";
        $tokenCheckUser=Users::getByToken($pdo,$login_url_token);
        if(!$tokenCheckUser || $tokenCheckUser->is_enabled==0){
            // 該当ユーザーなし　または　無効化されている
            $errorHeader='HTTP/1.1 404 Not Found';
            $login_disable_status ="このログインURLは利用できません。";
            break;
        }
        $userUntil=DateTime::createFromFormat('Y-m-d H:i:s',$tokenCheckUser->login_enabled_until??'');
        if($userUntil && $nowDateTime>$userUntil){
            $login_disable_status ="このログインURLは有効期限外です";
            break;
        }
    }
    $LoginAttemptIp=new LoginAttemptIp($pdo,$_SERVER['REMOTE_ADDR']);
    $LoginAttemptIpRow=$LoginAttemptIp->get();
    $until=DateTime::createFromFormat('Y-m-d H:i:s',$LoginAttemptIpRow['login_locked_until']??'');
    if(LOGIN_IP_LOCK_DURATION_MINUTES && $until && $until>$nowDateTime){
        $login_disable_status ="同一IPログイン連続失敗のためログインを制限しています。\n";
        $login_disable_status.="（".$until->format('Y-m-d H:i:s')."まで）";
    }
}while(false);
if($login_disable_status){ header($errorHeader);}
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?=h($page->renderTitle())?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?=$page->renderBaseStylesheetLinks()?>
    <style>
        th{ background-color: #EEE; }
    </style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<form action="./execute.php" method="post">
<?php if($login_disable_status): ?>
<p style="color: #CC0000;">
Access denied:<br>
<?=nl2br(h($login_disable_status))?></p>
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
<input type="hidden" name="login_url_token" value="<?=h($login_url_token)?>">
<input type="submit" value="ログイン実行"<?php echo $login_disable_status?' disabled':''; ?>>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>
