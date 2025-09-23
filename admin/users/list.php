<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="ユーザーアカウント";
$page->title="{$base_title}一覧";
$page->ForceNoindex();

$currentUser=Session::currentUser();
if($currentUser===null || !$currentUser->canUserManage()){ $page->exitToHome(); }

$pdo=getPDO();

$tableData=Users::getAll($pdo,true);
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
    <style>
        tr.super_admin td:nth-child(2){ color: red; background-color: #ffffa0ff; }
        tr.super_admin td:nth-child(n+4):not(:last-child){
            background-color: #EEE;
            text-decoration: line-through;
        }
    </style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php $page->printTitle(); ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<table class="admin-master-list">
    <tr>
        <th>ID</th>
        <th>ログインID</th>
        <th>表示名</th>
        <th>役割・権限</th>
        <th>ログイン可能期限</th>
        <th>最終ログイン</th>
        <th>利用可否</th>
        <th></th>
    </tr>
    <?php foreach($tableData as $row): ?>
        <?php
            $user=(new UsersRow())->setFromArray($row);
            $url="./form.php?id={$user->id}";
        ?>
        <tr class="<?=$user->is_enabled?'':'disabled';?><?=$user->username===ADMINISTRATOR_USER?' super_admin':''?>">
            <td><?=h($user->id)?></td>
            <td <?=$user->username===ADMINISTRATOR_USER?'title="管理者アカウント"':''?>><?=h($user->username)?></td>
            <td><?=h($user->display_name)?></td>
            <td><?=h(Role::RoleInfoList[$user->role]['name']??'')?></td>
            <?php
                $datetime=Datetime::createFromFormat('Y-m-d H:i:s',$user->login_enabled_until??'');
                $dateStr=$datetime===false?'':$datetime->format('Y-m-d');
            ?>
            <td><?=h($dateStr)?></td>
            <td><?=h($user->last_login_at)?></td>
            <td><?=$user->is_enabled?'有効':'無効'?></td>
            <td><?=(new MkTagA('編',$url))?></td>
        </tr>
    <?php endforeach; ?>
</table>
<hr>
[ <a href="./form.php"><?php print $base_title; ?>新規登録</a> ]
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>