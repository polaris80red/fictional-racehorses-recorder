<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="レース特殊結果マスタ";
$page->title="{$base_title}｜設定一覧";
$page->ForceNoindex();

$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$pdo=getPDO();
$search_page=max(filter_input(INPUT_GET,'page',FILTER_VALIDATE_INT),1);
$show_disabled=filter_input(INPUT_GET,'show_disabled',FILTER_VALIDATE_BOOL);
$race_sp_results_table=new RaceSpecialResults();
$tbl_data = $race_sp_results_table->getPage($pdo,$search_page,true);
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?><?=" - ".SITE_NAME; ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink('js/functions.js'); ?>
<style>
    th { background-color: #EEE;}

    tr.disabled { background-color: #EEE; }
    td.select_box_disabled { background-color: #EEE; }

    td.col_id, td.col_sort_number { text-align: right; }
    td a { text-decoration: none; }
    #content th a { text-decoration: none;}
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
$url_param =new UrlParams(['show_disabled'=>$show_disabled]);
$first_tag =new MkTagA("[最初]",($race_sp_results_table->current_page>2?('?'.$url_param->toString(['page'=>1])):''));
$prev_tag  =new MkTagA("[前へ]",($race_sp_results_table->current_page>1?('?'.$url_param->toString(['page'=>$race_sp_results_table->current_page-1])):''));
$next_tag  =new MkTagA("[次へ]",($race_sp_results_table->has_next_page?('?'.$url_param->toString(['page'=>$race_sp_results_table->current_page+1])):''));
?>
<!--
<?=$first_tag;?>｜<?=$prev_tag;?>｜<?=$next_tag;?>
-->
<table>
<tr>
    <th>ID</th>
    <th>キー名</th>
    <th>名称</th>
    <th>2字略</th>
    <th>カウント</th>
    <th>結果表示区分</th>
    <th>表示順<br>補正</th>
    <th>論理削除</th>
    <th></th>
</tr>
<?php foreach($tbl_data as $row): ?>
<tr class="<?=$row->is_enabled?:"disabled"?>">
<?php $url="./form.php?id={$row->id}"; ?>
    <td class="col_id"><?=h($row->id);?></td>
    <td><?=h($row->unique_name);?></td>
    <td class=""><?=h($row->name);?></td>
    <td class=""><?=h($row->short_name_2);?></td>
    <td class=""><?=h($row->is_registration_only?'登録のみ不出走':'結果掲載有り');?></td>
    <td class=""><?=h($row->is_excluded_from_race_count?'数えない':'着外1回');?></td>
    <td class="col_sort_number"><?=h($row->sort_number);?></td>
    <td><?=h($row->is_enabled?'有効':'無効化中');?></td>
    <td><?=(new MkTagA('編集',$url))?></td>
</tr>
<?php endforeach; ?>
</table>
<!--
<?=$first_tag;?>｜<?=$prev_tag;?>｜<?=$next_tag;?>
<hr>
[ <a href="./form.php"><?php print $base_title; ?>設定新規登録</a> ]
-->
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>