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
$race_grade=new RaceGradeRow();
if($id>0){
    $check_race_grade=RaceGrade::getById($pdo,$id);
    $race_grade->id=$id;
}
$race_grade->unique_name=filter_input(INPUT_POST,'unique_name');
$race_grade->short_name=filter_input(INPUT_POST,'short_name');
$race_grade->search_grade=filter_input(INPUT_POST,'search_grade');
$race_grade->category=filter_input(INPUT_POST,'category');
$race_grade->css_class_suffix=filter_input(INPUT_POST,'css_class_suffix');
$race_grade->show_in_select_box=filter_input(INPUT_POST,'show_in_select_box',FILTER_VALIDATE_INT);
$race_grade->sort_number=filter_input(INPUT_POST,'sort_number');
if($race_grade->sort_number===''){
    $race_grade->sort_number=null;
}else{
    $race_grade->sort_number=(int)$race_grade->sort_number;
}
$race_grade->is_enabled=filter_input(INPUT_POST,'is_enabled',FILTER_VALIDATE_BOOL)?1:0;

$error_exists=false;
do{
    if($id>0 && $check_race_grade===false){
        $page->debug_dump_var[]=['POST'=>$_POST];
        $page->addErrorMsg("{$base_title}設定ID '{$id}' が指定されていますが該当する{$base_title}がありません");
    }
    if($race_grade->unique_name===''){
        $page->debug_dump_var[]=['POST'=>$_POST];
        $page->addErrorMsg("{$base_title}設定｜キー名称未設定");
        break;
    }
    if(!$id && false!=RaceGrade::getByUniqueName($pdo,$race_grade->unique_name)){
        $page->addErrorMsg("キー名 '{$race_grade->unique_name}' は既に存在します");
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
        print_h($race_grade->id?:"新規登録");
        HTPrint::Hidden('race_grade_id',$race_grade->id);
    ?></td>
</tr>
<tr>
    <th>名称</th>
    <td><?php HTPrint::HiddenAndText('unique_name',$race_grade->unique_name); ?></td>
</tr>
<tr>
    <th>短縮名</th>
    <td><?php HTPrint::HiddenAndText('short_name',$race_grade->short_name); ?></td>
</tr>
<tr>
    <th>検索判定</th>
    <td><?php HTPrint::HiddenAndText('search_grade',$race_grade->search_grade); ?></td>
</tr>
<tr>
    <th>カテゴリ</th>
    <td><?php HTPrint::HiddenAndText('category',$race_grade->category); ?></td>
</tr>
<tr>
    <th>CSSクラス接尾語</th>
    <td><?php HTPrint::HiddenAndText('css_class_suffix',$race_grade->css_class_suffix); ?></td>
</tr>
<tr>
    <th>表示順補正</th>
    <td><?php HTPrint::HiddenAndText('sort_number',$race_grade->sort_number); ?></td>
</tr>
<tr>
    <th>プルダウンに表示</th>
    <td><?php
        HTPrint::Hidden('show_in_select_box',$race_grade->show_in_select_box);
        print $race_grade->show_in_select_box?'表示':'非表示';
    ?></td>
</tr>
<tr>
    <th>選択肢</th>
    <td><?php
        HTPrint::Hidden('is_enabled',$race_grade->is_enabled);
        print $race_grade->is_enabled?'表示':'非表示';
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