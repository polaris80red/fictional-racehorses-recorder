<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="レース格付マスタ";
$page->title="{$base_title}｜未登録リスト";

$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$pdo=getPDO();
$current_page=max(filter_input(INPUT_GET,'page',FILTER_VALIDATE_INT),1);
// $input_per_page=max(filter_input(INPUT_GET,'per_page',FILTER_VALIDATE_INT),0);
// $per_page = ($input_per_page===0?'10':$input_per_page);
$per_page=20;
$table_data=(function(int $current_page,int $per_page)use($pdo){
    $parts[]="SELECT DISTINCT r.grade";
    $parts[]="FROM `".RaceResults::TABLE."` as r";
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
    <title><?php $page->printTitle();  ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink('js/functions.js'); ?>
<style>
    th { background-color: #EEE;}
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
<a href="./list.php">一覧に戻る</a><br>
<p>レース結果だけに存在し、レース格付マスタにない格付名の一覧です。</p>
<?=$first_tag;?>｜<?=$prev_tag;?>｜<?=$next_tag;?>
<table>
<tr>
    <th>競馬場名称</th>
    <th colspan="1">リンク</th>
</tr>
<?php foreach($table_data as $row): ?>
<tr class="">
<?php
    $url="./form.php?id={$row['grade']}";
?>
    <td><?=$row['grade'];?></td>
    <td><a href="./form.php?name=<?=urlencode($row['grade']);?>">マスタ登録</a></td>
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
