<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
InAppUrl::init(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース結果ID一括修正・確認";
$page->ForceNoindex();
$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }

$race_result_id=(string)filter_input(INPUT_POST,'race_id');
$new_race_result_id=(string)filter_input(INPUT_POST,'new_race_id');

$pdo= getPDO();
# 対象取得
do{
    if($race_result_id==''){
        $page->addErrorMsg('元レースID未入力');
        break;
    }
    if($new_race_result_id==''){
        $page->addErrorMsg('新レースID未入力');
        break;
    }
    $old_id_checker=new Race();
    if($old_id_checker->setRaceId($race_result_id)===false){
        $page->addErrorMsgArray($old_id_checker->error_msgs);
    }
    $new_id_checker=new Race();
    if($new_id_checker->setRaceId($new_race_result_id)===false){
        $page->addErrorMsgArray($new_id_checker->error_msgs);
    }
    if($page->error_exists){ break; }

    $race_data=Race::getByRaceId($pdo,$race_result_id);
    if(!$race_data){
        header("HTTP/1.1 404 Not Found");
        $page->addErrorMsg('元IDレース情報取得失敗');
        $page->addErrorMsg("入力元ID：{$race_result_id}");
        break;
    }
    if(!Session::currentUser()->canEditRace($race_data)){
        header("HTTP/1.1 403 Forbidden");
        $page->addErrorMsg("編集権限がありません");
        break;
    }
    $new_id_race_data=Race::getByRaceId($pdo,$new_race_result_id);
    if($new_id_race_data){
        $page->addErrorMsg('新IDレースが既に存在');
        $page->addErrorMsg("入力新ID：{$new_race_result_id}");
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
?><!DOCTYPE html>
<html>
<head>
    <title><?=h($page->title)?></title>
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
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<form action="execute.php" method="post">
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
<?php (new FormCsrfToken())->printHiddenInputTag(); ?>
<hr>
<label style="border:1px solid #999; padding-right:0.3em;" class="font_red"><input id="confirm_check" type="checkbox" name="delete_confirm_check" value="1">このチェックを入れて実行</label><br>
<hr>
<input id="submit_btn" type="submit" value="処理実行" disabled>
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
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>
