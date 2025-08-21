<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="馬齢条件マスタ";
$page->title="{$base_title}｜設定登録";

$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$pdo=getPDO();
$input_id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
$input_name=filter_input(INPUT_GET,'name');

$s_setting=new Setting(false);
if($input_id>0){
    $form_item=RaceCategoryAge::getById($pdo,$input_id);
    if($form_item!==false){
        $page->title.="（編集）";
    }else{
        $input_id=0;
    }
}
if($input_id==0){
    $form_item=new RaceCategoryAgeRow();
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
    <th>検索ID</th>
    <td class="in_input"><select name="search_id">
    <option value="">未登録</option>
    <?php
    $age_category_list=[
        20=>'2歳',
        30=>'3歳',
        31=>'3歳以上',
        41=>'4歳以上',
        21=>'2歳以上（ナンソープS等）',
        40=>'4歳（香港クラシック）',
    ];
    foreach($age_category_list as $key=>$row){
        $selected= $key==$form_item->search_id?" selected":"";
        echo "<option value=\"{$key}\" {$selected}>{$row}</option>";
    }
    ?></select></td>
</tr>
<tr>
    <td colspan="2">2歳以上と4歳限定は世代検索のみ対応
    </td>
</tr>
<tr>
    <th>名称</th>
    <td class="in_input"><input type="text" name="name" value="<?php print $form_item->name; ?>"></td>
</tr>
<tr>
    <th>2字名</th>
    <td class="in_input"><input type="text" name="short_name_2" value="<?php print $form_item->short_name_2; ?>"></td>
</tr>
<tr>
    <th>擬人化用</th>
    <td class="in_input"><input type="text" name="name_umamusume" value="<?php print $form_item->name_umamusume; ?>"></td>
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