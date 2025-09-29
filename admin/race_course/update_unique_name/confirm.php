<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
defineAppRootRelPath(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="競馬場マスタ管理｜キー名称の一括変更：確認";
$page->ForceNoindex();
$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }
if(!Session::currentUser()->canManageMaster()){
    header("HTTP/1.1 403 Forbidden");
    $page->addErrorMsg('マスタ管理権限がありません');
}
$u_name=(string)filter_input(INPUT_POST,'u_name');
$new_unique_name=trim((string)filter_input(INPUT_POST,'new_unique_name'));

$pdo= getPDO();
# 対象取得
do{
    if($u_name==''){
        $page->addErrorMsg('変換対象の名称が未入力');
    }
    if($new_unique_name==''){
        $page->addErrorMsg('新しい名称が未入力');
    }
    if(mb_strlen($new_unique_name)>32){
        $page->addErrorMsg('キー名は32文字以下で入力してください');
    }
    if($page->error_exists){ break; }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
$updater=new IdUpdater($pdo,$u_name,$new_unique_name);
?><!DOCTYPE html>
<html>
<head>
    <title><?=h($page->renderTitle())?></title>
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
<?php $new_id_exists=$updater->new_id_exists(RaceCourse::TABLE,'unique_name'); ?>
<?php if($new_id_exists): ?>
新名称[<?=h($new_unique_name)?>]の競馬場は既にマスタに存在します。<br>
マスタの名称は変更せず、旧名称のレースを新名称のレースに紐づけます。
<?php endif; ?>
<table class="edit-form-table">
<tr>
    <th>置換前</th>
    <td><?php HTPrint::HiddenAndText('u_name',$u_name); ?></td>
</tr>
<tr>
    <th>置換後</th>
    <td><?php HTPrint::HiddenAndText('new_unique_name',$new_unique_name); ?></td>
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
