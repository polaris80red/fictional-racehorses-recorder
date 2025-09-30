<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
InAppUrl::init(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="一時ロック中・候補IP一覧";
$page->ForceNoindex();

if(!Session::isLoggedIn()){ $page->exitToHome(); }
$currentUser=Session::currentUser();
if(!$currentUser->canManageUser()){
    $page->setErrorReturnLink('管理画面に戻る',InAppUrl::to('admin/'));
    $page->error_msgs[]="管理者権限が必要です。";
    header("HTTP/1.1 403 Forbidden");
    $page->printCommonErrorPage();
    exit;
}
$pdo=getPDO();

$sql="SELECT * FROM ".LoginAttemptIp::QuotedTable();
$sql.=" WHERE 1";
$sql.=" ORDER BY `ip` ASC;";
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
    <?=$page->renderBaseStylesheetLinks()?>
    <?=$page->renderJqueryResource()?>
    <?=$page->renderScriptLink("js/functions.js")?>
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
ログイン失敗履歴のあるIPアドレス情報。
<hr>
<a href="./list.php">ユーザーアカウント一覧に戻る</a>
<table class="admin-master-list">
    <tr>
        <th>IPアドレス</th>
        <th>連続失敗<br>回数</th>
        <th>ログイン制限<br>終了日時</th>
        <th>最終アクセス</th>
    </tr>
    <?php foreach($tableData as $row): ?>
        <?php
            $ipRow=(object)$row;
        ?>
        <tr>
            <td><?=h($ipRow->ip)?></td>
            <td class="col_failed_login_attempts"><?=h($ipRow->login_failed_attempts)?>回</td>
            <?php
                $datetime=Datetime::createFromFormat('Y-m-d H:i:s',$ipRow->login_locked_until??'');
                $dateStr=$datetime===false?'':($datetime->format('Y/m/d H:i:s')."まで");
            ?>
            <td style="<?=$datetime<(new DateTime(PROCESS_STARTED_AT))?'color:#999;':''?>"><?=h($dateStr)?></td>
            <?php
                $datetime=Datetime::createFromFormat('Y-m-d H:i:s',$ipRow->last_attempt_at??'');
                $dateStr=$datetime===false?'':$datetime->format('Y/m/d H:i:s');
            ?>
            <td><?=h($dateStr)?></td>
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