<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="所属マスタ";
$page->title="{$base_title}｜設定一覧";

$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$pdo=getPDO();
$search_page=max(filter_input(INPUT_GET,'page',FILTER_VALIDATE_INT),1);
$race_grade_table=new Affiliation();
$race_grade = $race_grade_table->getPage($pdo,$search_page);
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle();  ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink('js/functions.js'); ?>
<style>
    th { background-color: #EEE;}
    tr.disabled { background-color: #EEE; }
    td.col_sort_number { text-align: right; }
    td a { text-decoration: none; }
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php $page->printTitle(); ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php
$first_tag  =new MkTagA("[最初]",($race_grade_table->current_page>2?('?page=1'):''));
$prev_tag   =new MkTagA("[前へ]",($race_grade_table->current_page>1?('?page='.($race_grade_table->current_page-1)):''));
$next_tag   =new MkTagA("[次へ]",($race_grade_table->has_next_page?('?page='.($race_grade_table->current_page+1)):''));
?>
<?=$first_tag;?>｜<?=$prev_tag;?>｜<?=$next_tag;?>
<table>
<tr>
    <th>ID</th>
    <th>名称</th>
    <th>表示順<br>補正</th>
    <th>セレクト<br>表示</th>
    <th>論理削除</th>
</tr>
<?php foreach($race_grade as $row): ?>
<tr class="<?php print($row->is_enabled?:"disabled"); ?>">
<?php
    $url="./form.php?id={$row->id}";
?>
    <td><?=h($row->id);?></td>
    <td><?=h($row->name);?></td>
    <td class="col_sort_number"><?=h($row->sort_number);?></td>
    <td><?=h($row->show_in_select_box?'表示':'非表示');?></td>
    <td><?=h($row->is_enabled?'有効':'無効化中');?></td>
</tr>
<?php endforeach; ?>
</table>
<?=$first_tag;?>｜<?=$prev_tag;?>｜<?=$next_tag;?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>