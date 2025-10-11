<?php
session_start();
require_once dirname(__DIR__,4).'/libs/init.php';
defineAppRootRelPath(4);
$page=new Page(4);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース結果詳細・削除確認";
$page->ForceNoindex();
$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }

$race_id=filter_input(INPUT_POST,'race_id');
$horse_id=filter_input(INPUT_POST,'horse_id');

$pdo= getPDO();
do{
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
    $horse=Horse::getByHorseId($pdo, $horse_id);
    if(!$horse){
        $page->addErrorMsg("競走馬取得エラー");
        break;
    }
    if($horse && !Session::currentUser()->canDeleteRaceResult($horse)){
        header("HTTP/1.1 403 Forbidden");
        $page->addErrorMsg("削除権限がありません");
        break;
    }
    $race=Race::getByRaceId($pdo, $race_id);
}while(false);
$page->renderErrorsAndExitIfAny();
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?=h($page->renderTitle())?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?=$page->renderBaseStylesheetLinks()?>
    <?=$page->renderJqueryResource()?>
<style>
.font_red{
    color:#FF0000;
}
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title" style="color:red;"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
削除を実行しますか？
<table class="edit-form-table">
<tr>
    <th>レースID</th>
    <td><?php HTPrint::HiddenAndText('race_id',$race_id); ?></td>
</tr>
<tr>
    <th>レース名</th>
    <td><?=h($race->year." ".$race->race_name)?></td>
</tr>
<tr>
    <th>競走馬ID</th>
    <td><?php HTPrint::HiddenAndText('horse_id',$horse_id); ?></td>
</tr>
<tr>
    <th>競走馬名</th>
     <td><?=h($horse->name_ja?:($horse->name_en?:''))?></td>
</tr>
</table>
<hr>
<form action="./execute.php" method="post">
<input type="hidden" name="delete_confirm_check" value="0">
<label style="border:1px solid #999; padding-right:0.3em;" class="font_red"><input id="confirm_check" type="checkbox" name="delete_confirm_check" value="1">このチェックを入れて実行</label><br>
<br>
<?php HTPrint::Hidden('race_id',$race_id); ?>
<?php HTPrint::Hidden('horse_id',$horse_id); ?>
<input id="submit_btn" type="submit" value="レース結果詳細データ削除実行" disabled>
<script>
$(function() {
    //チェックボックス操作時
    $('#confirm_check').click(function(){
    if($(this).prop('checked')) {
        $('#submit_btn').removeAttr('disabled').removeProp('disabled').addClass('font_red');
        $(this).parent().removeClass('font_red');
    } else {
        $('#submit_btn').attr('disabled',true).prop('disabled',true).removeClass('font_red');
        $(this).parent().addClass('font_red');
    }
    });
});
</script>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>
