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

$horse_race_result= new RaceResults();
$horse_race_result->race_id=$race_id;
$horse_race_result->horse_id=$horse_id;

$pdo= getPDO();
# 対象取得
do{
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
    $horse=Horse::getByHorseId($pdo, $horse_race_result->horse_id);
    if(!$horse){
        $page->addErrorMsg("競走馬取得エラー");
        break;
    }
    $race=new Race($pdo, $horse_race_result->race_id);
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php echo $page->title; ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
<style>
.font_red{
    color:#FF0000;
}
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title" style="color:red;"><?php echo $page->title; ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
削除を実行しますか？
<table class="edit-form-table">
<tr>
    <th>レースID</th>
    <td><?php HTPrint::HiddenAndText('race_id',$horse_race_result->race_id); ?></td>
</tr>
<tr>
    <th>レース名</th>
    <td><?php echo $race->year." ".$race->race_name; ?></td>
</tr>
<tr>
    <th>競走馬ID</th>
    <td><?php HTPrint::HiddenAndText('horse_id',$horse_race_result->horse_id); ?></td>
</tr>
<tr>
    <th>競走馬名</th>
     <td><?php
if($horse->name_ja){
    echo $horse->name_ja;
} else{
    echo $horse->name_en?:"";
}
?></td>
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
