<?php
session_start();
require_once dirname(__DIR__,4).'/libs/init.php';
defineAppRootRelPath(4);
$page=new Page(4);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース結果詳細・削除実行";
$page->ForceNoindex();
$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }

$delete_confirm_check=filter_input(INPUT_POST,'delete_confirm_check',FILTER_VALIDATE_BOOLEAN);
$race_id=filter_input(INPUT_POST,'race_id');
$horse_id=filter_input(INPUT_POST,'horse_id');

$pdo= getPDO();
do{
    if(!$delete_confirm_check){
        $page->addErrorMsg("確認チェックボックスがオンになっていません");
        break;
    }   
    if(empty($race_id)){
        $page->addErrorMsg("レースID未指定");
        break;
    }
    if(empty($horse_id)){
        $page->addErrorMsg("競走馬ID未指定");
        break;
    }
    $horse_race_result = RaceResults::getRowByIds($pdo, $race_id, $horse_id);
    if(!$horse_race_result){
        $page->addErrorMsg("存在しないレース結果");
        break;
    }
    $horse=Horse::getByHorseId($pdo,$horse_id);
    if(!$horse){
        $page->addErrorMsg("競走馬取得エラー");
        break;
    }
    if($horse && !Session::currentUser()->canDeleteRaceResult($horse)){
        header("HTTP/1.1 403 Forbidden");
        $page->addErrorMsg("削除権限がありません");
        break;
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}else{
    $pdo->beginTransaction();
    try {
        $escaped_horse_id=SqlValueNormalizer::escapeLike($horse_id);
        $escaped_race_id=SqlValueNormalizer::escapeLike($race_id);
        $sql="DELETE FROM `".RaceResults::TABLE."` WHERE `horse_id` LIKE :horse_id AND `race_id` LIKE :race_id;";
        $stmt1=$pdo->prepare($sql);
        $stmt1->bindValue(':horse_id',$escaped_horse_id,PDO::PARAM_STR);
        $stmt1->bindValue(':race_id',$escaped_race_id,PDO::PARAM_STR);
        $stmt1->execute();
        $pdo->commit();
    } catch(Exception $e) {
        $pdo->rollback();
        $page->addErrorMsg('データベース処理エラー');
        $page->addErrorMsg(print_r($e,true));
    }
}
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php echo $page->title; ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?=$page->renderBaseStylesheetLinks()?>
<style>
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php echo $page->title; ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php if($page->error_exists):?>
エラー
<?php else:?>
<?=h($horse_id)?>のレース結果を削除しました。
<?php endif; ?>
<hr>
<a href="<?=h(APP_ROOT_REL_PATH."horse/?horse_id=".$horse_id);?>">馬データへ移動</a><br>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>
