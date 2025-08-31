<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="テーマ";
$page->title="{$base_title}設定登録：内容確認";
$page->ForceNoindex();

$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$pdo=getPDO();
$input_id=filter_input(INPUT_POST,'theme_id',FILTER_VALIDATE_INT);
$theme=new Themes();
if($input_id>0){
    $theme->getDataById($pdo,$input_id);
}
$theme->name=filter_input(INPUT_POST,'name');
$theme->dir_name=filter_input(INPUT_POST,'dir_name');
$theme->sort_priority=filter_input(INPUT_POST,'sort_priority',FILTER_VALIDATE_INT);
$theme->sort_number=filter_input(INPUT_POST,'sort_number');
if($theme->sort_number===''){
    $theme->sort_number=null;
}else{
    $theme->sort_number=(int)$theme->sort_number;
}
$theme->is_enabled=filter_input(INPUT_POST,'is_enabled',FILTER_VALIDATE_BOOL)?1:0;

$error_exists=false;
do{
    if($input_id>0 && !$theme->record_exists){
        $error_exists=true;
        $page->debug_dump_var[]=['POST'=>$_POST];
        $page->addErrorMsg("{$base_title}設定ID '{$input_id}' が指定されていますが該当する{$base_title}がありません");
    }
    if($theme->name===''){
        $error_exists=true;
        $page->debug_dump_var[]=['POST'=>$_POST];
        $page->addErrorMsg("{$base_title}設定名称未設定");
        break;
    }
    $path=APP_ROOT_REL_PATH.'themes/'.$theme->dir_name;
    if(!is_dir($path)){
        $error_exists=true;
        $page->debug_dump_var[]=['POST'=>$_POST];
        $page->addErrorMsg("指定された[ {$theme->dir_name} ]ディレクトリが存在しません。");
        break;
    }

}while(false);
if($error_exists){
    $page->printCommonErrorPage();
    exit;
}
if($input_id>0){
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
<style>
    select{
        height: 2em;
    }
</style>
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
        print_h($theme->id?:"新規登録");
        HTPrint::Hidden('theme_id',$theme->id);
    ?></td>
</tr>
<tr>
    <th>名称</th>
    <td><?php HTPrint::HiddenAndText('name',$theme->name); ?></td>
</tr>
<tr>
    <th>テーマディレクトリ名</th>
    <td><?php HTPrint::HiddenAndText('dir_name',$theme->dir_name); ?></td>
</tr>
<tr>
    <th>表示順優先度</th>
    <td><?php HTPrint::HiddenAndText('sort_priority',$theme->sort_priority); ?></td>
</tr>
<tr>
    <th>表示順補正</th>
    <td><?php HTPrint::HiddenAndText('sort_number',$theme->sort_number); ?></td>
</tr>
<tr>
    <th>選択肢</th>
    <td><?php
        HTPrint::Hidden('is_enabled',$theme->is_enabled);
        print $theme->is_enabled?'表示':'非表示';
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