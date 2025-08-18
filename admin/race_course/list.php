<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="競馬場マスタ";
$page->title="{$base_title}設定一覧";

$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$pdo=getPDO();
$search_page=max(filter_input(INPUT_GET,'page',FILTER_VALIDATE_INT),1);
//$race_course=RaceCourse::getAll($pdo,true);
$race_course_table=new RaceCourse();
$race_course = $race_course_table->getPage($pdo,$search_page);
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
    select{
        height: 2em;
    }
    tr.disabled { background-color: #EEE; }
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
$prev_tag=new MkTagA("[前へ]",($race_course_table->prev_page?('?page='.$race_course_table->prev_page):''));
$next_tag=new MkTagA("[次へ]",($race_course_table->next_page?('?page='.$race_course_table->next_page):''));
?>
<?=$prev_tag;?>｜<?=$next_tag;?>
<table>
<tr>
    <th>ID</th>
    <th>キー名</th>
    <th>略名</th>
    <th>表示順補正</th>
    <th>選択肢</th>
    <th>選択肢</th>
    <th></th>
</tr>
<?php foreach($race_course as $row): ?>
<tr class="<?php print($row['is_enabled']?:"disabled"); ?>">
<?php
    $url="./form.php?id={$row['id']}";
?>
    <td><?php print $row['id']; ?></td>
    <td><?php print_h($row['unique_name']); ?></td>
    <td><?php print $row['short_name']; ?></td>
    <td><?php print $row['sort_number']; ?></td>
    <td><?php print $row['show_in_select_box']?'表示':'非表示'; ?></td>
    <td><?php print $row['is_enabled']?'表示':'非表示'; ?></td>
    <td><?php (new MkTagA('編',$url))->print(); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?=$prev_tag;?>｜<?=$next_tag;?>
<hr>
[ <a href="./form.php"><?php print $base_title; ?>設定新規登録</a> ]
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>