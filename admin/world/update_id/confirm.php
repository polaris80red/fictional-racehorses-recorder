<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
defineAppRootRelPath(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="ワールド管理｜IDの一括変更：確認";
$page->ForceNoindex();
$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }

$world_id=(string)filter_input(INPUT_POST,'world_id');
$new_world_id=trim((string)filter_input(INPUT_POST,'new_world_id'));

$pdo= getPDO();
# 対象取得
do{
    if(!Session::currentUser()->canManageSystemSettings()){
        header("HTTP/1.1 403 Forbidden");
        $page->addErrorMsg('システム設定管理権限がありません');
    }
    if($world_id==''){
        $page->addErrorMsg('変換対象の名称が未入力');
    }
    if($new_world_id==''){
        $page->addErrorMsg('新しい名称が未入力');
    }
    $updater=new IdUpdater($pdo,$world_id,$new_world_id,PDO::PARAM_INT);
    if($updater->new_id_exists(World::TABLE,'id')){
        $page->addErrorMsg('新しいIDのワールドが既に存在します');
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
?><!DOCTYPE html>
<html>
<head>
    <title><?=h($page->renderTitle())?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?=$page->renderBaseStylesheetLinks()?>
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
    <th>置換前</th>
    <td><?php HTPrint::HiddenAndText('world_id',$world_id); ?></td>
</tr>
<tr>
    <th>置換後</th>
    <td><?php HTPrint::HiddenAndText('new_world_id',$new_world_id); ?></td>
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
