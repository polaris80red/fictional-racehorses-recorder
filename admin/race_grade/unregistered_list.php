<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="レース格付マスタ";
$page->title="{$base_title}｜未登録リスト";
$page->ForceNoindex();

$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }

$pdo=getPDO();
$current_page=max(filter_input(INPUT_GET,'page',FILTER_VALIDATE_INT),1);
// $input_per_page=max(filter_input(INPUT_GET,'per_page',FILTER_VALIDATE_INT),0);
// $per_page = ($input_per_page===0?'10':$input_per_page);
$per_page=20;
$table_data=(function(int $current_page,int $per_page)use($pdo){
    $parts[]="SELECT DISTINCT r.grade";
    $parts[]="FROM `".Race::TABLE."` as r";
    $parts[]="LEFT JOIN `".RaceGrade::TABLE."` AS g ON r.grade LIKE g.unique_name";
    $parts[]="WHERE g.unique_name IS NULL AND r.grade NOT LIKE ''";
    $parts[]="ORDER BY r.grade ASC";
    if($per_page>0){
        $parts[]="LIMIT {$per_page}";
        if($current_page>1){
            $parts[]="OFFSET ".($current_page-1)*$per_page;
        }
    }
    $stmt=$pdo->prepare(implode(' ',$parts));
    $stmt->execute();
    return $stmt->fetchAll();
})($current_page,$per_page);
if($table_data===false){ $table_data=[]; }

$record_num=count($table_data);
$first_tag  =new MkTagA("[最初]",($current_page>2?('?page=1'):''));
$prev_tag   =new MkTagA("[前へ]",($current_page>1?('?page='.($current_page-1)):''));
$next_tag   =new MkTagA("[次へ]",(($record_num>=$per_page)?('?page='.($current_page+1)):''));

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
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<a href="./list.php">一覧に戻る</a><br>
<p>レース結果だけに存在し、レース格付マスタにない格付名の一覧です。</p>
<?=$first_tag;?>｜<?=$prev_tag;?>｜<?=$next_tag;?>
<table class="admin-master-list">
<tr>
    <th>名称</th>
    <th colspan="2">リンク</th>
</tr>
<?php foreach($table_data as $row): ?>
<tr class="">
<?php $url="./form.php?id={$row['grade']}"; ?>
    <td><?=h($row['grade'])?></td>
    <td><a href="./form.php?name=<?=h(urlencode($row['grade']))?>">マスタ登録</a></td>
    <td><a href="./update_unique_name/form.php?u_name=<?=h(urlencode($row['grade']))?>">レース結果を変換</a></td>
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
