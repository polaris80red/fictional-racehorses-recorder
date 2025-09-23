<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="レース格付マスタ";
$page->title="{$base_title}｜設定登録";
$page->ForceNoindex();

$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }

$pdo=getPDO();
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
$input_name=filter_input(INPUT_GET,'name');

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
    if($input_name){
        $form_item->unique_name=$input_name;
    }
}
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
        print_r($form_item->id?:"新規登録");
        HTPrint::Hidden('race_grade_id',$form_item->id);
    ?></td>
</tr>
<tr>
    <?php if($form_item->id||$input_name): ?>
        <th>キー名称</th>
        <td><?=(MkTagInput::Hidden('unique_name',$form_item->unique_name)).h($form_item->unique_name)?></td>
    <?php else: ?>
        <th>キー名称</th>
        <td class="in_input">
            <input type="text" name="unique_name" class="required" required value="<?=h($form_item->unique_name)?>">
        </td>
    <?php endif; ?>
</tr>
<tr>
    <th>短縮名</th>
    <td class="in_input"><input type="text" name="short_name" value="<?=h($form_item->short_name)?>" placeholder="空ならキー名称を使用"></td>
</tr>
<tr>
    <th>検索判定</th>
    <td class="in_input">
        <select name="search_grade" style="width:5em;">
<?php
    $search_grade_list=['G1','G2','G3','L','OP','重賞','3勝','2勝','1勝','未勝','新馬'];
    echo '<option value=""></option>'."\n";
    $target_exists=false;
    foreach($search_grade_list as $val){
        if($val==$form_item->search_grade){
            $selected_or_empty=' selected ';
        }else{
            $selected_or_empty='';
        }
        echo '<option value="'.$val,'"'.$selected_or_empty.'>';
        echo $val;
        echo '</option>'."\n";
    }
?></select>
    </td>
</tr>
<tr>
    <th>カテゴリ</th>
    <td class="in_input"><input type="text" name="category" value="<?=h($form_item->category)?>"></td>
</tr>
<tr>
    <th>CSSクラス名</th>
    <td class="in_input"><input type="text" name="css_class" value="<?=h($form_item->css_class)?>"></td>
</tr>
<tr>
    <th>表示順補正</th>
    <td class="in_input"><input type="number" name="sort_number" value="<?=h($form_item->sort_number)?>" placeholder="昇順"></td>
</tr>
<tr>
    <th>プルダウンに表示</th>
    <td>
        <label><?php
        $radio=new MkTagInputRadio('show_in_select_box',1,$form_item->show_in_select_box);
        $radio->print();
        ?>表示</label><br>
        <label><?php
        $radio->value(0)->checkedIf($form_item->show_in_select_box)->print();
        ?>非表示</label>
    </td>
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
<?php if($form_item->id): ?>
<hr>
<div style="text-align: right;">
※ キー名称はレース側のグレードも一括更新するため専用画面で変更してください<br>
[ <a href="./update_unique_name/form.php?<?=h(new UrlParams(['u_name'=>$form_item->unique_name]));?>">キー名称一括変換</a> ]
</div>
<?php endif; ?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>