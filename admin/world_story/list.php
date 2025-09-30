<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="ストーリー";
$page->title="{$base_title}設定一覧";
$page->ForceNoindex();

$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }

$pdo=getPDO();

$story_list=WorldStory::getAll($pdo,true);
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
    td.select_box_disabled { background-color: #EEE; }
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<table class="admin-master-list">
<tr>
    <th>ID</th>
    <th>名称</th>
    <th>非ログイン時</th>
    <th>表示順優先度</th>
    <th>表示順補正</th>
    <th>読取専用</th>
    <th>選択肢</th>
    <th></th>
</tr>
<?php foreach($story_list as $row): ?>
<tr class="<?php print($row['is_enabled']?:"disabled"); ?>">
<?php
    $url="./form.php?id={$row['id']}";
?>
    <td><?=h($row['id'])?></td>
    <td><?=h($row['name'])?></td>
    <td class="<?=$row['guest_visible']?'':'select_box_disabled'?>"><?=$row['guest_visible']?'表示':'非表示'?></td>
    <td><?=h($row['sort_priority'])?></td>
    <td><?=h($row['sort_number'])?></td>
    <td><?=$row['is_read_only']?'はい':'いいえ'?></td>
    <td><?=$row['is_enabled']?'表示':'非表示'?></td>
    <td><?=(new MkTagA('編',$url))?></td>
</tr>
<?php endforeach; ?>
</table>
<hr>
[ <a href="./form.php"><?php print $base_title; ?>設定新規登録</a> ]
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>