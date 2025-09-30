<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
InAppUrl::init(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="調教師マスタ";
$page->title="{$base_title}｜一覧";
$page->ForceNoindex();

$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }

$pdo=getPDO();
$search_page=max(filter_input(INPUT_GET,'page',FILTER_VALIDATE_INT),1);
$show_disabled=filter_input(INPUT_GET,'show_disabled',FILTER_VALIDATE_BOOL);
$master_table=new Trainer();
$tbl_data = $master_table->getPage($pdo,$search_page,$show_disabled);
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
    td.disabled { background-color: #EEE; }

    td.col_id, td.col_sort_number { text-align: right; }
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
$first_tag =new MkTagA("[最初]",($master_table->current_page>2?('?'.$url_param->toString(['page'=>1])):''));
$prev_tag  =new MkTagA("[前へ]",($master_table->current_page>1?('?'.$url_param->toString(['page'=>$master_table->current_page-1])):''));
$next_tag  =new MkTagA("[次へ]",($master_table->has_next_page?('?'.$url_param->toString(['page'=>$master_table->current_page+1])):''));
?>
<!--
<?=$first_tag;?>｜<?=$prev_tag;?>｜<?=$next_tag;?>
-->
<table class="admin-master-list">
<tr>
    <th>ID</th>
    <th>キー名</th>
    <th>氏名</th> 
    <th>10字内略</th>
    <th>所属</th>
    <th>匿名フラグ</th>
    <th>論理削除<br><?=(new MkTagA('表示切替',"?show_disabled=".($show_disabled?'0':'1')));?></th>
    <th></th>
</tr>
<?php foreach($tbl_data as $row): ?>
<tr class="<?=$row->is_enabled?'':"disabled"?>">
<?php $url="./form.php?id={$row->id}"; ?>
    <td class="col_id"><?=h($row->id);?></td>
    <td><?=(new MkTagA($row->unique_name,InAppUrl::to('horse/search/',['trainer'=>$row->unique_name])))?></td>
    <td class=""><?=h($row->name);?></td>
    <td class=""><?=h($row->short_name_10);?></td>
    <td class=""><?=h($row->affiliation_name);?></td>
    <td class="<?=!$row->is_anonymous?'':'disabled'?>"><?=h($row->is_anonymous?'管理用':'通常');?></td>
    <td><?=$row->is_enabled?'有効':'無効化中'?></td>
    <td><?=(new MkTagA('編集',$url))?></td>
</tr>
<?php endforeach; ?>
</table>
<?=$first_tag;?>｜<?=$prev_tag;?>｜<?=$next_tag;?>
<hr>
[ <a href="./form.php"><?php print $base_title; ?>設定新規登録</a> ]<br>
マスタ未登録の調教師を確認・登録<br>
[ <a href="./unregistered_list.php">競走馬</a>｜<a href="./unregistered_list_2.php">レース個別結果</a> ]
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>