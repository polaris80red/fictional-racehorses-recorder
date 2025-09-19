<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="レース格付マスタ";
$page->title="{$base_title}｜設定登録：内容確認";
$page->ForceNoindex();

$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$pdo=getPDO();
$id=filter_input(INPUT_POST,'race_grade_id',FILTER_VALIDATE_INT);

$editMode=($id>0);
$TableClass=RaceGrade::class;
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
$form_item->short_name=filter_input(INPUT_POST,'short_name');
$form_item->search_grade=filter_input(INPUT_POST,'search_grade');
$form_item->category=filter_input(INPUT_POST,'category');
$form_item->css_class=filter_input(INPUT_POST,'css_class');
$form_item->show_in_select_box=filter_input(INPUT_POST,'show_in_select_box',FILTER_VALIDATE_INT);
$form_item->sort_number=filter_input(INPUT_POST,'sort_number');
if($form_item->sort_number===''){
    $form_item->sort_number=null;
}else{
    $form_item->sort_number=(int)$form_item->sort_number;
}
$form_item->is_enabled=filter_input(INPUT_POST,'is_enabled',FILTER_VALIDATE_BOOL)?1:0;

$error_exists=false;
do{
    if(!$form_item->validate()){
        $page->addErrorMsgArray($form_item->errorMessages);
    }
    if(!$editMode && false!=RaceGrade::getByUniqueName($pdo,$form_item->unique_name)){
        $page->addErrorMsg("キー名 '{$form_item->unique_name}' は既に存在します");
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle();  ?></title>
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
        HTPrint::Hidden('race_grade_id',$form_item->id);
    ?></td>
</tr>
<tr>
    <th>名称</th>
    <td><?php HTPrint::HiddenAndText('unique_name',$form_item->unique_name); ?></td>
</tr>
<tr>
    <th>短縮名</th>
    <td><?php HTPrint::HiddenAndText('short_name',$form_item->short_name); ?></td>
</tr>
<tr>
    <th>検索判定</th>
    <td><?php HTPrint::HiddenAndText('search_grade',$form_item->search_grade); ?></td>
</tr>
<tr>
    <th>カテゴリ</th>
    <td><?php HTPrint::HiddenAndText('category',$form_item->category); ?></td>
</tr>
<tr>
    <th>CSSクラス名</th>
    <td><?php HTPrint::HiddenAndText('css_class',$form_item->css_class); ?></td>
</tr>
<tr>
    <th>表示順補正</th>
    <td><?php HTPrint::HiddenAndText('sort_number',$form_item->sort_number); ?></td>
</tr>
<tr>
    <th>プルダウンに表示</th>
    <td><?php
        HTPrint::Hidden('show_in_select_box',$form_item->show_in_select_box);
        print $form_item->show_in_select_box?'表示':'非表示';
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