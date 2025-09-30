<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="レース格付マスタ";
$page->title="{$base_title}｜設定一覧";
$page->ForceNoindex();

$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }

$pdo=getPDO();
$search_page=max(filter_input(INPUT_GET,'page',FILTER_VALIDATE_INT),1);
$show_disabled=filter_input(INPUT_GET,'show_disabled',FILTER_VALIDATE_BOOL);
$race_grade_table=new RaceGrade();
$race_grade = $race_grade_table->getPage($pdo,$search_page,$show_disabled);
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
    td.col_sort_number { text-align: right; }
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php
$url_param =new UrlParams(['show_disabled'=>$show_disabled]);
$first_tag =new MkTagA("[最初]",($race_grade_table->current_page>2?('?'.$url_param->toString(['page'=>1])):''));
$prev_tag  =new MkTagA("[前へ]",($race_grade_table->current_page>1?('?'.$url_param->toString(['page'=>$race_grade_table->current_page-1])):''));
$next_tag  =new MkTagA("[次へ]",($race_grade_table->has_next_page?('?'.$url_param->toString(['page'=>$race_grade_table->current_page+1])):''));
?>
<?=$first_tag;?>｜<?=$prev_tag;?>｜<?=$next_tag;?>
<table class="admin-master-list">
<tr>
    <th>ID</th>
    <th>キー名</th>
    <th>略名</th>
    <th>検索判定</th>
    <th>結果ページ等<br>カテゴリ</th>
    <th>表示順<br>補正</th>
    <th>セレクト<br>表示</th>
    <th>論理削除<br><?=(new MkTagA('表示切替',"?show_disabled=".($show_disabled?'0':'1')));?></th>
    <th colspan="2"></th>
</tr>
<?php foreach($race_grade as $row): ?>
<tr class="<?php print($row->is_enabled?:"disabled"); ?>">
<?php
    $url="./form.php?id={$row->id}";
?>
    <td><?=h($row->id);?></td>
    <td><?=h($row->unique_name);?></td>
    <td><?=h($row->short_name);?></td>
    <td><?=h($row->search_grade);?></td>
    <td><?=h($row->category);?></td>
    <td class="col_sort_number"><?=h($row->sort_number);?></td>
    <td class="<?=$row->show_in_select_box?'':'select_box_disabled'?>"><?=h($row->show_in_select_box?'表示':'非表示');?></td>
    <td><?=h($row->is_enabled?'有効':'無効化中');?></td>
    <td><?php (new MkTagA('編集',"./form.php?id={$row->id}"))->print(); ?></td>
    <td><?php (new MkTagA('改名',"./update_unique_name/form.php?u_name={$row->unique_name}"))->print(); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?=$first_tag;?>｜<?=$prev_tag;?>｜<?=$next_tag;?>
<hr>
[ <a href="./form.php"><?php print $base_title; ?>設定新規登録</a> ]<br>
[ <a href="./unregistered_list.php">マスタ未登録のグレードを確認・登録</a> ]
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>