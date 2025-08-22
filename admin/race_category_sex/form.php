<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="性別条件マスタ";
$page->title="{$base_title}｜設定登録";

$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$pdo=getPDO();
$input_id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
$input_name=filter_input(INPUT_GET,'name');

$s_setting=new Setting(false);
if($input_id>0){
    $form_item=RaceCategorySex::getById($pdo,$input_id);
    if($form_item!==false){
        $page->title.="（編集）";
    }else{
        $input_id=0;
    }
}
if($input_id==0){
    $form_item=new RaceCategorySexRow();
    if($input_name){
        $form_item->name=$input_name;
    }
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
        print $form_item->id?:"新規登録";
        HTPrint::Hidden('id',$form_item->id);
    ?></td>
</tr>
<tr>
    <th>名称</th>
    <td class="in_input"><input type="text" name="name" class="required" required value="<?php print $form_item->name; ?>"></td>
</tr>
<tr>
    <th>3字名</th>
    <td class="in_input"><input type="text" name="short_name_3" value="<?php print $form_item->short_name_3; ?>"></td>
</tr>
<tr>
    <th>擬人化用</th>
    <td class="in_input"><input type="text" name="umm_category" value="<?php print $form_item->umm_category; ?>"></td>
</tr>
<tr>
    <th>表示順補正</th>
    <td class="in_input"><input type="number" name="sort_number" value="<?php print $form_item->sort_number; ?>" placeholder="昇順"></td>
</tr>
<tr>
    <th>論理削除状態</th>
    <td>
        <label><?php
        $radio=new MkTagInputRadio('is_enabled',1,$form_item->is_enabled);
        $radio->print();
        ?>有効</label><br>
        <label><?php
        $radio->value(0)->checkedIf($form_item->is_enabled)
        ->disabled($form_item->id>0?false:true)->print();
        ?>無効化中</label>
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