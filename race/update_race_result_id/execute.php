<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース結果ID一括修正・実行";
$page->ForceNoindex();
$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$race_result_id=(string)filter_input(INPUT_POST,'race_id');
$new_race_result_id=(string)filter_input(INPUT_POST,'new_race_id');

$pdo= getPDO();
# 対象取得
do{
    if(!(new FormCsrfToken())->isValid()){
        ELog::error($page->title.": CSRFトークンエラー|".__FILE__);
        $page->addErrorMsg("入力フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
        break;
    }
    if($race_result_id==''){
        $page->addErrorMsg('元レースID未入力');
    }
    if($new_race_result_id==''){
        $page->addErrorMsg('新レースID未入力');
    }
    if($race_result_id!==htmlspecialchars($race_result_id)){
        $page->addErrorMsg('元レースIDに特殊文字');
    }
    if($new_race_result_id!==htmlspecialchars($new_race_result_id)){
        $page->addErrorMsg('新レースIDに特殊文字');
    }
    if($page->error_exists){ break; }
    $race_data=new Race($pdo,$race_result_id);
    if(!$race_data->record_exists){
        $page->addErrorMsg('元IDレース情報取得失敗');
        $page->addErrorMsg("入力元ID：{$race_result_id}");
    }
    $new_id_race_data=new Race($pdo,$new_race_result_id);
    if($new_id_race_data->record_exists){
        $page->addErrorMsg('新IDレースが既に存在');
        $page->addErrorMsg("入力新ID：{$new_race_result_id}");
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
$updater=new IdUpdater($pdo,$race_result_id,$new_race_result_id);
$updater->addUpdateTarget(RaceResults::TABLE,'race_results_id');
$updater->addUpdateTarget(Race::TABLE,'race_id');
$updater->execute();

?><!DOCTYPE html>
<html>
<head>
    <title><?php echo $page->title; ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<form action="" method="post">
<table class="edit-form-table">
<tr>
    <th>対象レース</th>
    <td><?=h($race_data->year."年 ".$race_data->race_name)?></td>
</tr>
<tr>
    <th>置換前レースID</th>
    <td><?php HTPrint::HiddenAndText('race_id',$race_result_id); ?></td>
</tr>
<tr>
    <th>置換後レースID</th>
    <td><?php HTPrint::HiddenAndText('new_race_id',$new_race_result_id); ?></td>
</tr>
</table>
<hr>
<a href="<?=h($page->getRaceResultUrl($new_race_result_id))?>">レースに移動</a>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>
