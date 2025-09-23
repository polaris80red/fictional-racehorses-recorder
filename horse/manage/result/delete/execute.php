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

$horse_race_result= new RaceResults();
$horse_race_result->race_id=$race_id;
$horse_race_result->horse_id=$horse_id;

$pdo= getPDO();
# 対象取得
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
    if(!$horse_race_result->setDataById($pdo, $race_id, $horse_id)){
        $page->addErrorMsg("存在しないレース結果");
        break;
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}else{
    try {
        $pdo->beginTransaction();
        $horse_race_result->DeleteExec($pdo);
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
    <?php $page->printBaseStylesheetLinks(); ?>
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
<?=h($horse_race_result->horse_id)?>のレース結果を削除しました。
<?php endif; ?>
<hr>
<a href="<?=h(APP_ROOT_REL_PATH."horse/?horse_id=".$horse_race_result->horse_id);?>">馬データへ移動</a><br>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>
