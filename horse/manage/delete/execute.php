<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
defineAppRootRelPath(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="競走馬データ削除・実行";
$page->ForceNoindex();
$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$horse_id=(string)filter_input(INPUT_POST,'horse_id');

$pdo= getPDO();
# 対象取得
do{
    if($horse_id==''){
        $page->addErrorMsg('元ID未入力');
    }
    if($horse_id!==htmlspecialchars($horse_id)){
        $page->addErrorMsg('元IDに特殊文字');
    }
    if($page->error_exists){ break; }
    $horse_data=new Horse();
    $horse_data->setDataById($pdo,$horse_id);
    if(!$horse_data->record_exists){
        $page->addErrorMsg('元ID馬情報取得失敗');
        $page->addErrorMsg("入力元ID：{$horse_id}");
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}else{
    $pdo->beginTransaction();
    try{
        $escaped_horse_id=SqlValueNormalizer::escapeLike($horse_id);
        $sql="DELETE FROM `".HorseTag::TABLE."` WHERE `horse_id` LIKE :old_id;";
        $stmt1=$pdo->prepare($sql);
        $stmt1->bindValue(':old_id',$escaped_horse_id,PDO::PARAM_STR);
        $stmt1->execute();

        $sql="DELETE FROM `".RaceResults::TABLE."` WHERE `horse_id` LIKE :old_id;";
        $stmt2=$pdo->prepare($sql);
        $stmt2->bindValue(':old_id',$escaped_horse_id,PDO::PARAM_STR);
        $stmt2->execute();

        $sql="DELETE FROM `".Horse::TABLE."` WHERE `horse_id` LIKE :old_id;";
        $stmt3=$pdo->prepare($sql);
        $stmt3->bindValue(':old_id',$escaped_horse_id,PDO::PARAM_STR);
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
競走馬データ <?=h($horse_id)?> を削除しました。
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>
