<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="騎手マスタ";
$page->title="{$base_title}｜登録：内容確認";
$page->ForceNoindex();

$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }
if(!Session::currentUser()->canManageMaster()){
    header("HTTP/1.1 403 Forbidden");
    $page->addErrorMsg('マスタ管理権限がありません');
}
$pdo=getPDO();
$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);

$editMode=($id>0);
$TableClass=Jockey::class;
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
$form_item->unique_name=filter_input(INPUT_POST,'unique_name');
$form_item->name=filter_input(INPUT_POST,'name');
$form_item->short_name_10=filter_input(INPUT_POST,'short_name_10');
$form_item->affiliation_name=filter_input(INPUT_POST,'affiliation_name')?:filter_input(INPUT_POST,'affiliation_name_select');
$form_item->trainer_name=filter_input(INPUT_POST,'trainer_name');
$form_item->is_anonymous=filter_input(INPUT_POST,'is_anonymous',FILTER_VALIDATE_BOOL)?1:0;
$form_item->is_enabled=filter_input(INPUT_POST,'is_enabled',FILTER_VALIDATE_BOOL)?1:0;

if(!$form_item->validate()){
    $page->addErrorMsgArray($form_item->errorMessages);
}
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?=h($page->renderTitle())?></title>
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
<h1 class="page_title"><?=h($page->title)?></h1>
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
    <th>キー名称</th>
    <td><?php HTPrint::HiddenAndText('unique_name',$form_item->unique_name); ?></td>
</tr>
<!--
<tr>
    <th>氏名</th>
    <td><?php HTPrint::HiddenAndText('name',$form_item->name); ?></td>
</tr>
-->
<tr>
    <th>10字略</th>
    <td><?php HTPrint::HiddenAndText('short_name_10',$form_item->short_name_10); ?></td>
</tr>
<tr>
    <th>所属</th>
    <td><?php HTPrint::HiddenAndText('affiliation_name',$form_item->affiliation_name); ?></td>
</tr>
<tr>
    <th>厩舎</th>
    <td><?php HTPrint::HiddenAndText('trainer_name',$form_item->trainer_name); ?></td>
</tr>
<tr>
    <th>匿名フラグ</th>
    <td><?php
        HTPrint::Hidden('is_anonymous',$form_item->is_anonymous);
        print $form_item->is_anonymous?'管理用':'通常';
    ?></td>
</tr>
<tr>
    <th>選択肢</th>
    <td><?php
        HTPrint::Hidden('is_enabled',$form_item->is_enabled);
        print $form_item->is_enabled?'表示':'非表示';
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