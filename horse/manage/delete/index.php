<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
defineAppRootRelPath(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="競走馬データ削除・確認";
$page->ForceNoindex();
$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }

$horse_id=(string)filter_input(INPUT_POST,'horse_id');
$pdo= getPDO();
do{
    $errorHeader="HTTP/1.1 404 Not Found";
    if($horse_id==''){
        $page->addErrorMsg('元ID未入力');
        break;
    }
    $horse=Horse::getByHorseId($pdo,$horse_id);
    if(!$horse){
        $page->addErrorMsg("元ID馬情報取得失敗\n入力元ID：{$horse_id}");
        break;
    }
    $errorHeader="HTTP/1.1 403 Forbidden";
    if($horse && !Session::currentUser()->canDeleteHorse($horse)){
        $page->addErrorMsg("削除権限がありません");
        break;
    }
}while(false);
$page->renderErrorsAndExitIfAny($errorHeader);
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
table{
	border-collapse:collapse;
}
table, tr, th, td{
	border:solid 1px #333;
}
th{
	padding-left:0.3em;
	padding-right:0.3em;
}
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
<form action="execute.php" method="post">
<table>
<tr>
    <th>対象馬</th>
    <td><?=h(implode('/',array_diff([$horse->name_ja,$horse->name_en],[''])))?></td>
</tr>
<tr>
    <th>対象競走馬ID</th>
    <td><?php printHiddenAndText('horse_id',$horse_id); ?></td>
</tr>
</table>
<hr>
<label style="border:1px solid #999; padding-right:0.3em;" class="font_red"><input id="confirm_check" type="checkbox" name="delete_confirm_check" value="1">このチェックを入れて実行</label><br>
<hr>
<input id="submit_btn" type="submit" value="競走馬データ削除実行" disabled>
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
<?=(new FormCsrfToken())?>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>
