<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="レース週マスタ";
$page->title="{$base_title}｜設定登録：内容確認";
$page->ForceNoindex();

$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }
if(!Session::currentUser()->canManageSystemSettings()){
    header("HTTP/1.1 403 Forbidden");
    $page->addErrorMsg('システム設定管理権限がありません');
}
$pdo=getPDO();
$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);

$editMode=($id>0);
$TableClass=RaceWeek::class;
$TableRowClass=$TableClass::ROW_CLASS;

if($editMode){
    $page->title.="（編集）";
    $form_item=($TableClass)::getById($pdo,$id);
    if($form_item===false){
        $page->addErrorMsg("ID '{$id}' が指定されていますが該当するレコードがありません");
    }
}else{
    $form_item=new ($TableRowClass)();
}

$form_item->name=filter_input(INPUT_POST,'name');
$form_item->month=filter_input(INPUT_POST,'month',FILTER_VALIDATE_INT);
$form_item->month_grouping=filter_input(INPUT_POST,'month_grouping',FILTER_VALIDATE_INT);
$form_item->month_grouping= $form_item->month.($form_item->month_grouping%10);
$form_item->umm_month_turn=filter_input(INPUT_POST,'umm_month_turn',FILTER_VALIDATE_INT);
$form_item->sort_number=filter_input(INPUT_POST,'sort_number');
if($form_item->sort_number===''){
    $form_item->sort_number=null;
}else{
    $form_item->sort_number=(int)$form_item->sort_number;
}
$form_item->is_enabled=filter_input(INPUT_POST,'is_enabled',FILTER_VALIDATE_BOOL)?1:0;

do{
    if(!$form_item->validate()){
        $page->addErrorMsgArray($form_item->errorMessages);
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?><?=" - ".SITE_NAME; ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink('js/functions.js'); ?>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php $page->printTitle(); ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<form method="post" action="./registration_execute.php">
<table class="edit-form-table">
<tr>
    <th>ID</th>
    <td><?php
        print_h($form_item->id?:"新規登録");
        HTPrint::Hidden('id',$form_item->id);
    ?></td>
</tr>
<tr>
    <th>名称</th>
    <td><?php HTPrint::HiddenAndText('name',$form_item->name); ?></td>
</tr>
<tr>
    <th>月と週補正</th>
    <td>
        <?php HTPrint::HiddenAndText('month',$form_item->month); ?>-<?=$form_item->month_grouping%10; ?>
        <?php HTPrint::Hidden('month_grouping',$form_item->month_grouping); ?>
    </td>
</tr>
<tr>
    <th>ターン形式</th>
    <td>
        <?php HTPrint::Hidden('umm_month_turn',$form_item->umm_month_turn); ?>
        <?=($form_item->umm_month_turn===1?'前半':($form_item->umm_month_turn===2?'後半':'')); ?>
    </td>
</tr>
<tr>
    <th>表示順補正</th>
    <td><?php HTPrint::HiddenAndText('sort_number',$form_item->sort_number); ?></td>
</tr>
<tr>
    <th>選択肢</th>
    <td><?php
        HTPrint::Hidden('is_enabled',$form_item->is_enabled);
        print $form_item->is_enabled?'有効':'無効化中';
    ?></td>
</tr>
<tr><td colspan="2" style="text-align: left;"><input type="submit" value="登録実行"></td></tr>
</table>
<?php (new FormCsrfToken())->printHiddenInputTag(); ?>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>