<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
require_once __DIR__.'/libs/common.inc.php';
if(!InstallerSession::isLogdIn()){
    header("Location: ./login.php");
    exit;
}
InAppUrl::init(2);
$page=new Page(2);
$page->title="インストーラー";
$page->ForceNoindex();
$pdo=getPDO();
// CREATE TABLE sqlファイルの場所からテーブル名リストを取得
$sqlFilesDir=dirname(__DIR__).'/sql/create_tables';

$tableFiles=(function($dirPath){
    $pathList=scandir($dirPath);
    $files=[];
    foreach($pathList as $path){
        if(pathinfo($path,PATHINFO_EXTENSION)==='sql'){
            $files[]=pathinfo($path,PATHINFO_FILENAME);
        }
    }
    return $files;
})($sqlFilesDir);
// データベースから現在存在するテーブルのリストを取得
$currentExistsTables=(function($pdo){
    $sql="SHOW TABLES;";
    $stmt=$pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
})($pdo);

$createTableList=[];
$logJsonArray=[];
$logJsonArray['execute_at']=PROCESS_STARTED_AT;
foreach($tableFiles as $table){
    $logArray=[];
    if(in_array($table,$currentExistsTables)){
        // 存在する
        $logArray['exists']=true;
    }else{
        // 存在しない
        $logArray['exists']=false;
        $createTableList[]=$table;
    }
    $logJsonArray['tables'][$table]=$logArray;
}

$page->renderErrorsAndExitIfAny();
$csrf=new FormCsrfToken();
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?=h($page->renderTitle())?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <link rel="stylesheet" type="text/css" href="./style.css">
    <?=$page->getMetaNoindex()?>
    <style>
        li {margin-top: 10px;}
    </style>
</head>
<body>
<header>
<div style="text-align: right;"><a href="./logout.php">ログアウト</a></div>
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<ul>
    <li>
        <a href="./create_table.php" style="font-weight: bold;">1.未作成のテーブルを作成する</a>：<br>
        必要なテーブルが存在するかチェックし、存在しなければ作成します。
    </li>
    <li>
        <a href="./import_master.php"  style="font-weight: bold;">2.基本的なマスタデータのインポート</a>：<br>
        基本的なマスタデータのテーブルが空かどうかを確認し、空の場合にインポートします。
    </li>
</ul>
<hr>
<h2>テーブルリスト</h2>
<table>
    <thead>
        <tr>
            <th></th>
            <th>テーブル名</th>
            <th>存在有無</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $i=0;
        ?>
        <?php foreach($logJsonArray['tables'] as $key => $tableInfo):?>
            <tr style="<?=$tableInfo['exists']?'':'color:red;'?>">
                <td class="col_number"><?=h(++$i)?></td>
                <td><?=h($key)?></td>
                <td><?=h($tableInfo['exists']?'ok':'未作成')?></td>
            </tr>
        <?php endforeach;?>
    </tbody>
</table>
<hr class="no-css-fallback">
</main>
<footer>
</footer>
</body>
</html>