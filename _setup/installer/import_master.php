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
$page->title="インストーラー｜マスタ登録";
$page->ForceNoindex();

$pdo=getPDO();

$logJsonPath=LOG_DIR_PATH.'/latest_install_info.json';
if(file_exists($logJsonPath)){
    $logJsonArray=json_decode(file_get_contents(LOG_DIR_PATH.'/latest_install_info.json'),true);
}else{
    $logJsonArray=[];
}
$logJsonArray['master_import_at']=PROCESS_STARTED_AT;
$logJsonArray['master_import']=[];

$importStatus=[];
$sqlFilesDir=dirname(__DIR__).'/sql/data';
// ファイルごとのチェックとinsert処理
/**
 * @return string|false エラーがあればエラーメッセージ、問題なければfalse
 */
$funcImportExec=function($pdo,$fileName)use($sqlFilesDir){
    $path=$sqlFilesDir.'/'.$fileName;
    if(!file_exists($path)){
        return 'error: 対象ファイルなし';
    }
    if(false===$pdo->exec(file_get_contents($path))){
        return 'error: インポート失敗';
    }
    return false;
};
$funcRecordExists=function($pdo,$table,$where=''){
    $where=$where?:'1';
    try {
        $stmt=$pdo->query("SELECT * FROM `$table` WHERE {$where} LIMIT 1;");
    } catch (Exception $e) {
        return 'error: テーブル確認エラー';
    }
    return $stmt->fetch()===false ? false : 'skip: レコードあり';
};
$importMasterList=[
    [
        // 所属マスタ
        'filename'=>'mst_affiliation.sql',
        'table'=>'mst_affiliation',
        'optional_where'=>"`id` BETWEEN 1 AND 4",
    ],
    [
        // 所属マスタ2
        'filename'=>'mst_affiliation__optional_nar.sql',
        'table'=>'mst_affiliation',
        'optional_where'=>"`id` BETWEEN 101 AND 121",
    ],
    [
        // 年齢条件マスタ
        'filename'=>'mst_race_category_age.sql',
        'table'=>'mst_race_category_age',
    ],
    [
        // 性別条件マスタ
        'filename'=>'mst_race_category_sex.sql',
        'table'=>'mst_race_category_sex',
    ],
    [
        // コースマスタ
        'filename'=>'mst_race_course.sql',
        'table'=>'mst_race_course',
    ],
    [
        // コースマスタ2
        'filename'=>'mst_race_course__optional_nar.sql',
        'table'=>'mst_race_course',
        'optional_where'=>"`id` BETWEEN 101 AND 121",
    ],
    [
        // コースマスタ3
        'filename'=>'mst_race_course__optional_other.sql',
        'table'=>'mst_race_course',
        'optional_where'=>"`id` BETWEEN 202 AND 286",
    ],
    [
        // 格付マスタ
        'filename'=>'mst_race_grade.sql',
        'table'=>'mst_race_grade',
    ],
    [
        // 特殊結果マスタ
        'filename'=>'mst_race_special_results.sql',
        'table'=>'mst_race_special_results',
    ],
    [
        // 週マスタ
        'filename'=>'mst_race_week.sql',
        'table'=>'mst_race_week',
    ],
    [
        // テーママスタ
        'filename'=>'mst_themes.sql',
        'table'=>'mst_themes',
    ],
];
foreach($importMasterList as $master){
    $resultsArray=['import'=>false];
    $result=$funcRecordExists($pdo,$master['table'],$master['optional_where']??'');
    if($result){
        $resultsArray['state']=$result;
    }else{
        $result=$funcImportExec($pdo,$master['filename']);
        if($result){
            $resultsArray['state']=$result;
        }else{
            $resultsArray['state']='import: インポート実行';
            $resultsArray['import']=true;
        }
    }
    $logJsonArray['master_import'][$master['filename']]=$resultsArray;
    $importStatus[$master['filename']]=$resultsArray;
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
        <?php foreach($importStatus as $key => $row):?>
            <tr>
                <td class="col_number"><?=h(++$i)?></td>
                <td><?=h($key)?></td>
                <td><?=h($row['state']??'')?></td>
            </tr>
        <?php endforeach;?>
    </tbody>
</table>
<hr>
<a href="./">インストーラーのトップへ戻る</a>
<hr class="no-css-fallback">
</main>
<footer>
</footer>
</body>
</html>