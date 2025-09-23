<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="テーマ";
$page->title="{$base_title}設定一覧";
$page->ForceNoindex();

$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }

$pdo=getPDO();

$themes=Themes::getAll($pdo,true);
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
<table class="admin-master-list">
<tr>
    <th>ID</th>
    <th>名称</th>
    <th>テーマディレクトリ名</th>
    <th>表示順優先度</th>
    <th>表示順補正</th>
    <th>選択肢</th>
    <th></th>
</tr>
<?php foreach($themes as $row): ?>
<tr class="<?php print($row['is_enabled']?:"disabled"); ?>">
<?php
    $url="./form.php?id={$row['id']}";
?>
    <td><?php print $row['id']; ?></td>
    <td><?php print_h($row['name']); ?></td>
    <td><?php print $row['dir_name']; ?></td>
    <td><?php print $row['sort_priority']; ?></td>
    <td><?php print $row['sort_number']; ?></td>
    <td><?php print $row['is_enabled']?'表示':'非表示'; ?></td>
    <td><?php (new MkTagA('編',$url))->print(); ?></td>
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