<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="競走馬ID一括修正・確認";
$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$horse_id=(string)filter_input(INPUT_POST,'horse_id');
$new_horse_id=(string)filter_input(INPUT_POST,'new_horse_id');

$pdo= getPDO();
# 対象取得
do{
    if($horse_id==''){
        $page->addErrorMsg('元ID未入力');
    }
    if($new_horse_id==''){
        $page->addErrorMsg('新ID未入力');
    }
    if($horse_id!==htmlspecialchars($horse_id)){
        $page->addErrorMsg('元IDに特殊文字');
    }
    if($new_horse_id!==htmlspecialchars($new_horse_id)){
        $page->addErrorMsg('新IDに特殊文字');
    }
    if($page->error_exists){ break; }
    $horse_data=new Horse();
    $horse_data->setDataById($pdo,$horse_id);
    if(!$horse_data->record_exists){
        $page->addErrorMsg('元ID馬情報取得失敗');
        $page->addErrorMsg("入力元ID：{$horse_id}");
    }
    $new_id_horse_data=new Horse();
    $new_id_horse_data->setDataById($pdo,$new_horse_id);
    if($new_id_horse_data->record_exists){
        $page->addErrorMsg('新IDが既に存在');
        $page->addErrorMsg("入力新ID：{$new_horse_id}");
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
?><!DOCTYPE html>
<html>
<head>
    <title><?php echo $page->title; ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
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
<h1 class="page_title"><?php echo $page->title; ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<form action="execute.php" method="post">
<table class="edit-form-table">
<tr>
    <th>対象馬</th>
    <td><?php echo $horse_data->name_ja."/".$horse_data->name_en; ?></td>
</tr>
<tr>
    <th>置換前競走馬ID</th>
    <td><?php printHiddenAndText('horse_id',$horse_id); ?></td>
</tr>
<tr>
    <th>置換後競走馬ID</th>
    <td><?php printHiddenAndText('new_horse_id',$new_horse_id); ?></td>
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
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>
