<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="競馬場マスタ";
$page->title="{$base_title}設定登録";
$page->ForceNoindex();

$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }
if(!Session::currentUser()->canManageMaster()){
    header("HTTP/1.1 403 Forbidden");
    $page->addErrorMsg('マスタ管理権限がありません');
}
$pdo=getPDO();
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
$input_name=filter_input(INPUT_GET,'name');

$editMode=($id>0);
$TableClass=RaceCourse::class;
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
    <title><?=h($page->renderTitle())?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?=$page->renderBaseStylesheetLinks()?>
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
<h1 class="page_title"><?=h($page->title)?></h1>
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
        HTPrint::Hidden('race_course_id',$form_item->id);
    ?></td>
</tr>
<tr>
    <?php if($form_item->id||$form_item->unique_name): ?>
        <th rowspan="2">キー名称</th>
        <td><?=(MkTagInput::Hidden('unique_name',$form_item->unique_name)).h($form_item->unique_name)?></td>
    <?php else: ?>
        <th rowspan="2">キー名称</th>
        <td class="in_input">
            <input type="text" name="unique_name" class="required" required value="<?=h($form_item->unique_name)?>">
        </td>
    <?php endif; ?>
</tr>
<tr>
    <td>レースの競馬場名が<br>有効な競馬場マスタの上記に一致すると<br>表示順や略名での表示を適用します</td></tr>
<tr>
    <th>短縮名</th>
    <td class="in_input"><input type="text" name="short_name" value="<?=h($form_item->short_name)?>" placeholder="空ならキー名称を使用"></td>
</tr>
<tr>
    <th rowspan="2">短縮名2</th>
    <td class="in_input"><input type="text" name="short_name_m" value="<?=h($form_item->short_name_m)?>" placeholder="空ならメイン略称を使用"></td>
</tr>
<tr>
    <td>出馬表等用の国名1文字でない略称</td>
</tr>
<tr>
    <th>表示順優先度</th>
    <td class="in_input"><input type="number" name="sort_priority" value="<?=h($form_item->sort_priority)?>" placeholder="降順"></td>
</tr>
<tr>
    <th>表示順補正</th>
    <td class="in_input"><input type="number" name="sort_number" value="<?=h($form_item->sort_number); ?>" placeholder="同優先度内昇順"></td>
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
※ キー名称はレース側の競馬場も一括更新するため専用画面で変更してください<br>
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