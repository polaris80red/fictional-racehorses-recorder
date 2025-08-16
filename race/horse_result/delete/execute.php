<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
defineAppRootRelPath(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース結果詳細・削除実行";
$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$delete_confirm_check=filter_input(INPUT_POST,'delete_confirm_check',FILTER_VALIDATE_BOOLEAN);
$race_results_id=filter_input(INPUT_POST,'race_id');
$horse_id=filter_input(INPUT_POST,'horse_id');

$horse_race_result= new RaceResultDetail();
$horse_race_result->race_results_id=$race_results_id;
$horse_race_result->horse_id=$horse_id;

$pdo= getPDO();
# 対象取得
do{
    if(!$delete_confirm_check){
        $page->addErrorMsg("確認チェックボックスがオンになっていません");
        break;
    }   
    if(empty($race_results_id)){
        $page->addErrorMsg("レースID未指定");
        break;
    }
    if(empty($horse_id)){
        $page->addErrorMsg("競走馬ID未指定");
        break;
    }
    if(!$horse_race_result->setDataById($pdo, $race_results_id, $horse_id)){
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
    <?php $page->printBaseStylesheetLinks(); ?>
<style>
</style>
</head>
<body>
<header>
<a href="<?php echo $page->to_app_root_path; ?>">[HOME]</a>
<h1 class="page_title"><?php echo $page->title; ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php if($page->error_exists){
    ?>エラー<?php
}else{
    ?>削除しました。<?php
}
?>
<pre><?php print_r($horse_race_result); ?></pre>
<hr>
<a href="<?php echo $page->to_app_root_path ?>horse/?horse_id=<?php echo $horse_race_result->horse_id;?>">馬データへ移動</a><br>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>
