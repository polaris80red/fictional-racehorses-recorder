<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
InAppUrl::init(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="一時ロック中・候補アカウント一覧";
$page->ForceNoindex();

if(!Session::isLoggedIn()){ $page->exitToHome(); }
$currentUser=Session::currentUser();
if(!$currentUser->canManageUser()){
    $page->setErrorReturnLink('管理画面に戻る',InAppUrl::to('admin/'));
    $page->error_msgs[]="ユーザー管理には管理者権限が必要です。";
    header("HTTP/1.1 403 Forbidden");
    $page->printCommonErrorPage();
    exit;
}
$pdo=getPDO();

$sql="SELECT * FROM ".Users::QuotedTable();
$sql.=" WHERE `login_locked_until` IS NOT NULL OR `failed_login_attempts` > 0";
$sql.=" ORDER BY `login_locked_until` ASC;";
$stmt=$pdo->prepare($sql);
$stmt->execute();
$tableData=$stmt->fetchAll();
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?=h($page->renderTitle())?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink('js/functions.js'); ?>
    <style>
        td.col_failed_login_attempts { text-align: right; }
    </style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
連続でのログイン失敗が継続中か、規定回数に達して制限されて以降未ログインのアカウントの一覧です。
<hr>
<a href="./list.php">ユーザーアカウント一覧に戻る</a>
<table class="admin-master-list">
    <tr>
        <th>ID</th>
        <th>ログインID</th>
        <th>表示名</th>
        <th>役割・権限</th>
        <th>連続失敗<br>回数</th>
        <th>ログイン制限<br>終了日時</th>
        <th>最終ログイン成功</th>
        <th></th>
    </tr>
    <?php foreach($tableData as $row): ?>
        <?php
            $user=(new UsersRow())->setFromArray($row);
            $edit_url="./form.php?id={$user->id}";
        ?>
        <tr class="<?=$user->is_enabled?'':'disabled';?><?=$user->username===ADMINISTRATOR_USER?' super_admin':''?>">
            <td><?=h($user->id)?></td>
            <td <?=$user->username===ADMINISTRATOR_USER?'title="管理者アカウント"':''?>><?=h($user->username)?></td>
            <td><?=h($user->display_name)?></td>
            <td><?=h(Role::RoleInfoList[$user->role]['name']??'')?></td>
            <td class="col_failed_login_attempts"><?=h($user->failed_login_attempts)?>回</td>
            <?php
                $datetime=Datetime::createFromFormat('Y-m-d H:i:s',$user->login_locked_until??'');
                $dateStr=$datetime===false?'':($datetime->format('Y/m/d H:i:s')."まで");
            ?>
            <td style="<?=$datetime<(new DateTime(PROCESS_STARTED_AT))?'color:#999;':''?>"><?=h($dateStr)?></td>
            <?php
                $datetime=Datetime::createFromFormat('Y-m-d H:i:s',$user->last_login_at??'');
                $dateStr=$datetime===false?'':$datetime->format('Y/m/d H:i:s');
            ?>
            <td><?=h($dateStr)?></td>
            <td><?=(new MkTagA('編',$edit_url))?></td>
        </tr>
    <?php endforeach; ?>
</table>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>