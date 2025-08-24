<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="レース特殊結果マスタ";
$page->title="{$base_title}｜設定登録：内容確認";

$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$pdo=getPDO();
$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
$form_item=new RaceSpecialResultsRow();
if($id>0){
    $check_form_item=RaceSpecialResults::getById($pdo,$id);
    $form_item->id=$id;
}
$form_item->unique_name=filter_input(INPUT_POST,'unique_name');
$form_item->name=filter_input(INPUT_POST,'name');
$form_item->short_name_2=filter_input(INPUT_POST,'short_name_2');
$form_item->is_registration_only=filter_input(INPUT_POST,'is_registration_only');
$form_item->sort_number=filter_input(INPUT_POST,'sort_number');
if($form_item->sort_number===''){
    $form_item->sort_number=null;
}else{
    $form_item->sort_number=(int)$form_item->sort_number;
}
$form_item->is_enabled=filter_input(INPUT_POST,'is_enabled',FILTER_VALIDATE_BOOL)?1:0;

$error_exists=false;
do{
    if(mb_strlen($form_item->short_name_2,'UTF-8')>2){
        $page->addErrorMsg("2文字略名は2文字以内で設定してください");
    }
    if($id>0 && $check_form_item===false){
        $page->debug_dump_var[]=['POST'=>$_POST];
        $page->addErrorMsg("{$base_title}設定ID '{$id}' が指定されていますが該当する{$base_title}がありません");
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
if($id>0){
    $page->title.="（編集）";
}

?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?><?=" - ".SITE_NAME; ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
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
        print $form_item->id?:"新規登録";
        HTPrint::Hidden('id',$form_item->id);
    ?></td>
</tr>
<tr>
    <th>キー名称</th>
    <td><?php HTPrint::HiddenAndText('unique_name',$form_item->unique_name); ?></td>
</tr>
<tr>
    <th>名称</th>
    <td><?php HTPrint::HiddenAndText('name',$form_item->name); ?></td>
</tr>
<tr>
    <th>2字略</th>
    <td><?php HTPrint::HiddenAndText('short_name_2',$form_item->short_name_2); ?></td>
</tr>
<tr>
    <th>結果表示区分</th>
    <td><?php
        HTPrint::Hidden('is_registration_only',$form_item->is_registration_only);
        print $form_item->is_registration_only?'結果掲載有り':'登録のみで不出走';
    ?></td></td>
</tr>
<tr>
    <th>表示順補正</th>
    <td><?php HTPrint::HiddenAndText('sort_number',$form_item->sort_number); ?></td>
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