<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="テーマ";
$page->title="{$base_title}設定登録";
$page->ForceNoindex();

$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$pdo=getPDO();
$input_id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
$theme=new Themes();
$s_setting=new Setting(false);
if($input_id>0){
    $theme->getDataById($pdo,$input_id);
    if($theme->record_exists){
        $page->title.="（編集）";
    }else{
        $theme->id=0;
    }
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
<a href="./list.php">一覧に戻る</a>
<form method="post" action="./registration_confirm.php">
<table class="edit-form-table">
<tr>
    <th>ID</th>
    <td><?php
        print $theme->id?:"新規登録";
        HTPrint::Hidden('theme_id',$theme->id);
    ?></td>
</tr>
<tr>
    <th>名称</th>
    <td class="in_input"><input type="text" name="name" class="required" value="<?php print $theme->name; ?>" required></td>
</tr>
<tr>
    <th>テーマディレクトリ名</th>
    <td class="in_input"><input type="text" name="dir_name" class="required" value="<?php print $theme->dir_name; ?>" required></td>
</tr>
<tr>
    <th>表示順優先度</th>
    <td class="in_input"><input type="number" name="sort_priority" value="<?php print $theme->sort_priority; ?>" placeholder="大きい値ほど上"></td>
</tr>
<tr>
    <th>表示順(同優先度内で昇順)</th>
    <td class="in_input"><input type="number" name="sort_number" value="<?php print $theme->sort_number; ?>" placeholder="同優先度内で昇順"></td>
</tr>
<tr>
    <th>選択肢</th>
    <td>
        <label><?php
        $radio=new MkTagInputRadio('is_enabled',1,$theme->is_enabled);
        $radio->print();
        ?>表示</label><br>
        <label><?php
        $radio->value(0)->checkedIf($theme->is_enabled)
        ->disabled($theme->id>0?false:true)->print();
        ?>非表示</label>
    </td>
</tr>
<tr><td colspan="2" style="text-align: right;"><input type="submit" value="登録内容確認"></td></tr>
</table>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>