<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース結果削除・実行";
$page->ForceNoindex();
$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$race_result_id=(string)filter_input(INPUT_POST,'race_id');

$pdo= getPDO();
# 対象取得
do{
    if($race_result_id==''){
        $page->addErrorMsg('元レースID未入力');
    }
    if($race_result_id!==htmlspecialchars($race_result_id)){
        $page->addErrorMsg('元レースIDに特殊文字');
    }
    if($page->error_exists){ break; }
    $race_data=new Race($pdo,$race_result_id);
    if(!$race_data->record_exists){
        $page->addErrorMsg('元IDレース情報取得失敗');
        $page->addErrorMsg("入力元ID：{$race_result_id}");
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}else{
    $pdo->beginTransaction();
    try{ 
        $escaped_race_result_id=SqlValueNormalizer::escapeLike($race_result_id);
        $sql="DELETE FROM `".RaceResults::TABLE."` WHERE `race_id` LIKE :old_id;";
        $stmt2=$pdo->prepare($sql);
        $stmt2->bindValue(':old_id',$escaped_race_result_id,PDO::PARAM_STR);
        $stmt2->execute();

        $sql="DELETE FROM `".Race::TABLE."` WHERE `race_id` LIKE :old_id;";
        $stmt3=$pdo->prepare($sql);
        $stmt3->bindValue(':old_id',$escaped_race_result_id,PDO::PARAM_STR);
        $stmt3->execute();

        $pdo->commit();
    }catch(Exception $e){
        $pdo->rollBack();
        $page->addErrorMsg("PDO_ERROR:".print_r($e,true));
        $page->printCommonErrorPage();
    }
}

?><!DOCTYPE html>
<html>
<head>
    <title><?=h($page->title)?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
<style>
table{
	border-collapse:collapse;
}
table, tr, th, td{
	border:solid 1px #333;
}
th{
	padding-left:0.3em;
	padding-right:0.3em;
}
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
レース情報 <?=h($race_result_id)?> を削除しました
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>
