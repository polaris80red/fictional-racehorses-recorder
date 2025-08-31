<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="ワールド一覧";
$page->ForceNoindex();

$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$pdo=getPDO();

$world_list=World::getAll($pdo,true);
$story_list=WorldStory::getAll($pdo);
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
    th { background-color: #EEE;}
    select{
        height: 2em;
    }
    tr.disabled { background-color: #EEE; }
    td.select_box_disabled { background-color: #EEE; }
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php $page->printTitle(); ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<table>
<tr>
    <th>ID</th>
    <th>名称</th>
    <th>非ログイン時</th>
    <th>正規日付</th>
    <th>表示順優先度</th>
    <th>選択肢</th>
    <th></th>
</tr>
<?php foreach($world_list as $world): ?>
<tr class="<?php print($world['is_enabled']?:"disabled"); ?>">
<?php
    $url="./form.php?id={$world['id']}";
?>
    <td><?=h($world['id'])?></td>
    <td><?=h($world['name'])?></td>
    <td class="<?=$world['guest_visible']?'':'select_box_disabled'?>"><?=$world['guest_visible']?'表示':'非表示'?></td>
    <td><?=$world['use_exact_date']?'あり前提':'なし前提'?></td>
    <td><?=h($world['sort_priority'])?></td>
    <td><?=$world['is_enabled']?'表示':'非表示'?></td>
    <td><?=(new MkTagA('編',$url))?></td>
</tr>
<?php endforeach; ?>
</table>
<hr>
[ <a href="./form.php">ワールド新規登録</a> ]
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>