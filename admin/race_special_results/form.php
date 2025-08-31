<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="レース特殊結果マスタ";
$page->title="{$base_title}｜設定登録";
$page->ForceNoindex();

$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$pdo=getPDO();
$input_id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
$input_name=filter_input(INPUT_GET,'name');

$s_setting=new Setting(false);
if($input_id>0){
    $form_item=RaceSpecialResults::getById($pdo,$input_id);
    if($form_item!==false){
        $page->title.="（編集）";
    }else{
        $input_id=0;
    }
}
if($input_id==0){
    $form_item=new RaceSpecialResultsRow();
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
        print_h($form_item->id?:"新規登録");
        HTPrint::Hidden('id',$form_item->id);
    ?></td>
</tr>
<tr>
    <th>キー名称</th>
    <td class="in_input"><input type="text" name="unique_name" class="required" required value="<?=h($form_item->unique_name)?>" readonly></td>
</tr>
<tr>
    <th>名称</th>
    <td class="in_input"><input type="text" name="name" class="required" required value="<?=h($form_item->name)?>"></td>
</tr>
<tr>
    <th>2字略</th>
    <td class="in_input"><input type="text" name="short_name_2" value="<?=h($form_item->short_name_2)?>" placeholder="空ならキー名を使用"></td>
</tr>
<tr>
    <th>結果表示区分</th>
    <td>
        <label><?php
        $radio=new MkTagInputRadio('is_registration_only',1,$form_item->is_registration_only);
        $radio->print();
        ?>結果掲載有り</label><br>
        <label><?php
        $radio->value(0)->checkedIf($form_item->is_registration_only)
        ->disabled($form_item->id>0?false:true)->print();
        ?>登録のみで不出走</label>
    </td>
</tr>
<tr>
    <th>カウント</th>
    <td>
        <label><?php
        $radio=new MkTagInputRadio('c',0,$form_item->is_excluded_from_race_count);
        $radio->print();
        ?>着外1回</label><br>
        <label><?php
        $radio->value(1)->checkedIf($form_item->is_excluded_from_race_count)
        ->disabled($form_item->id>0?false:true)->print();
        ?>数えない</label>
    </td>
</tr>
<tr>
    <th>表示順補正</th>
    <td class="in_input"><input type="number" name="sort_number" value="<?=h($form_item->sort_number)?>" placeholder="昇順"></td>
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