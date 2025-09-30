<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="レース週マスタ";
$page->title="{$base_title}｜設定一覧";
$page->ForceNoindex();

$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }

$pdo=getPDO();
$search_page=max(filter_input(INPUT_GET,'page',FILTER_VALIDATE_INT),1);
$show_disabled=filter_input(INPUT_GET,'show_disabled',FILTER_VALIDATE_BOOL);
$race_category_age_table=new RaceWeek();
$race_category_age = $race_category_age_table->getPage($pdo,$search_page,true);
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?=h($page->renderTitle())?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?=$page->renderBaseStylesheetLinks()?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink('js/functions.js'); ?>
<style>
    tr.boundary_week { background-color: #fffbd2ff; }
    td.select_box_disabled { background-color: #EEE; }
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
<form action="./form.php" method="get">
    第<input name="id" type="number" value="" style="width: 4em;">週
    <input type="submit" value="IDで直接開く">
</form>
<hr>
<?php
$url_param =new UrlParams(['show_disabled'=>$show_disabled]);
$first_tag =new MkTagA("[最初]",($race_category_age_table->current_page>2?('?'.$url_param->toString(['page'=>1])):''));
$prev_tag  =new MkTagA("[前へ]",($race_category_age_table->current_page>1?('?'.$url_param->toString(['page'=>$race_category_age_table->current_page-1])):''));
$next_tag  =new MkTagA("[次へ]",($race_category_age_table->has_next_page?('?'.$url_param->toString(['page'=>$race_category_age_table->current_page+1])):''));
?>
<?=$first_tag;?>｜<?=$prev_tag;?>｜<?=$next_tag;?>
<table class="admin-master-list">
<tr>
    <th>週</th>
    <th>名称</th>
    <th>月週<br>補正</th>
    <th>前後<br>ターン形式</th>
    <th>表示順<br>補正</th>
<!--
    <th>論理削除<br><?=(new MkTagA('表示切替',"?show_disabled=".($show_disabled?'0':'1')));?></th>
-->
    <th></th>
</tr>
<?php foreach($race_category_age as $row): ?>
<?php
    $class=new Imploader(' ');
    if($row->month_grouping%10==0){
        $class->add('boundary_week');
    }
    else if($row->month_grouping%10>=5){
        $class->add('boundary_week');
    };
    if(!$row->is_enabled){ $class->add("disabled"); }
?>
<tr class="<?=$class; ?>">
<?php
    $url="./form.php?id={$row->id}";
?>
    <td class="col_id"><?=h($row->id);?></td>
    <td class="col_name"><?=h($row->name);?></td>
    <td class="" style="text-align: right;"><?=h($row->month."-".$row->month_grouping%10);?></td>
    <td class=""><?=h(str_pad($row->month,2,'0',STR_PAD_LEFT)."月 ".($row->umm_month_turn===1?'前半':($row->umm_month_turn===2?'後半':'')));?></td>
    <td class="col_sort_number"><?=h($row->sort_number);?></td>
<!--
    <td><?=h($row->is_enabled?'有効':'無効化中');?></td>
-->
    <td><?php (new MkTagA('編集',"./form.php?id={$row->id}"))->print(); ?></td>
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