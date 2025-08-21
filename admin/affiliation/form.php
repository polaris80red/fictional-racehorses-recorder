<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="所属マスタ";
$page->title="{$base_title}｜設定登録";

$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$pdo=getPDO();
$input_id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
$input_name=filter_input(INPUT_GET,'name');

$s_setting=new Setting(false);
if($input_id>0){
    $affiliation=Affiliation::getById($pdo,$input_id);
    if($affiliation!==false){
        $page->title.="（編集）";
    }else{
        $input_id=0;
    }
}
if($input_id==0){
    $affiliation=new AffiliationRow();
    if($input_name){
        $affiliation->name=$input_name;
    }
}

?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle();  ?></title>
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
        print $affiliation->id?:"新規登録";
        HTPrint::Hidden('id',$affiliation->id);
    ?></td>
</tr>
<tr>
    <th rowspan="1">名称</th>
    <td class="in_input"><input type="text" name="name" class="required" value="<?php print $affiliation->name; ?>"<?=(($affiliation->id||$input_name)?' readonly':'')?> required></td>
</tr>
<tr>
    <th>表示順補正</th>
    <td class="in_input"><input type="number" name="sort_number" value="<?php print $affiliation->sort_number; ?>" placeholder="昇順"></td>
</tr>
<tr>
    <th>プルダウンに表示</th>
    <td>
        <label><?php
        $radio=new MkTagInputRadio('show_in_select_box',1,$affiliation->show_in_select_box);
        $radio->print();
        ?>表示</label><br>
        <label><?php
        $radio->value(0)->checkedIf($affiliation->show_in_select_box)->print();
        ?>非表示</label>
    </td>
</tr>
<tr>
    <th>論理削除状態</th>
    <td>
        <label><?php
        $radio=new MkTagInputRadio('is_enabled',1,$affiliation->is_enabled);
        $radio->print();
        ?>有効</label><br>
        <label><?php
        $radio->value(0)->checkedIf($affiliation->is_enabled)
        ->disabled($affiliation->id>0?false:true)->print();
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