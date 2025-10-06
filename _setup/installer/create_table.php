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
$page->title="インストーラー｜テーブル作成";
$page->ForceNoindex();

$pdo=getPDO();

$logJsonPath=LOG_DIR_PATH.'/latest_install_info.json';
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
$logJsonArray['create_tables_execute_at']=PROCESS_STARTED_AT;
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
foreach($createTableList as $tableName){
    $filePath="{$sqlFilesDir}/{$tableName}.sql";
    if(file_exists($filePath)){
        $sql=file_get_contents($filePath);
        $pdo->exec($sql);
        $logJsonArray['tables'][$tableName]['create']=true;
        ELog::debug("テーブル作成実行：{$tableName}");
    }
}
file_put_contents($logJsonPath,json_encode($logJsonArray,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
$page->renderErrorsAndExitIfAny();
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?=h($page->renderTitle())?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <link rel="stylesheet" type="text/css" href="./style.css">
    <?=$page->getMetaNoindex()?>
</head>
<body>
<header>
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<h2>テーブルリスト</h2>
<table>
    <thead>
        <tr>
            <th></th>
            <th>テーブル名</th>
            <th>ステータス</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $i=0;
        ?>
        <?php foreach($logJsonArray['tables'] as $key => $tableInfo):?>
            <tr>
                <td class="col_number"><?=h(++$i)?></td>
                <td><?=h($key)?></td>
                <td><?=h(($tableInfo['create']??false)?'created':'exists')?></td>
            </tr>
        <?php endforeach;?>
    </tbody>
</table>
<hr>
<a href="./import_master.php">2.基本的なマスタデータのインポートに進む。</a>
<hr class="no-css-fallback">
</main>
<footer>
</footer>
</body>
</html>