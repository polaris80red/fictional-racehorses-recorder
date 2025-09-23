<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="ユーザーアカウント";
$page->title="{$base_title}設定登録";
$page->ForceNoindex();

$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }

$pdo=getPDO();
$inputId=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);

$editMode=($inputId>0);
$TableClass=Users::class;
$TableRowClass=$TableClass::ROW_CLASS;

if($editMode){
    $page->title.="（編集）";
    $form_item=($TableClass)::getById($pdo,$inputId);
    if($form_item===false){
        $page->addErrorMsg("テーマID '{$inputId}' が指定されていますが該当するテーマがありません");
    }
}else{
    $form_item=new ($TableRowClass)();
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
        print_h($form_item->id?:"新規登録");
        HTPrint::Hidden('id',$form_item->id);
    ?></td>
</tr>
<tr>
    <th>ログインユーザー名</th>
    <td class="in_input"><input type="text" name="username" class="required" value="<?=h($form_item->username)?>" required></td>
</tr>
<tr>
    <th>パスワード</th>
    <td class="in_input"><input type="password" name="password" class="<?=$editMode?'':'required'?>" value=""<?=$editMode?'':' required'?>></td>
</tr>
<tr>
    <th>パスワード再入力</th>
    <td class="in_input"><input type="password" name="password_2" class="<?=$editMode?'':'required'?>" value=""<?=$editMode?'':' required'?>></td>
</tr>
<?php if($editMode):?>
<tr>
    <td colspan="2">パスワードは変更時のみ入力</td>
</tr>
<?php endif;?>
<tr>
    <th>表示名</th>
    <td class="in_input"><input type="text" name="display_name" class="required" value="<?=h($form_item->display_name)?>" required></td>
</tr>
<tr>
    <th>役割・権限</th>
    <td class="in_input">
        <select name="role">
            <option value=""></option>
            <?php foreach(Role::RoleInfoList as $key=>$row):?>
                <option value="<?=h($key)?>"<?=$form_item->role==$key?' selected':''?>><?=h($row['name'])?></option>
            <?php endforeach;?>
        </select>
    </td>
</tr>
<tr>
    <th>ログイン可能期限</th>
    <?php
        $datetime=Datetime::createFromFormat('Y-m-d H:i:s',$form_item->login_enabled_until??'');
        $dateStr=$datetime===false?'':$datetime->format('Y-m-d');
    ?>
    <td class="in_input"><input type="text" id="date_picker" style="width: 6em;" name="login_enabled_until" value="<?=h($dateStr)?>">まで</td>
</tr>
<tr>
    <th>利用可否</th>
    <td>
        <label><?php
        $radio=new MkTagInputRadio('is_enabled',1,$form_item->is_enabled);
        $radio->print();
        ?>有効</label>
        <label><?php
        $radio->value(0)->checkedIf($form_item->is_enabled)
        ->disabled($form_item->id>0?false:true)->print();
        ?>無効</label>
    </td>
</tr>
<tr><td colspan="2" style="text-align: right;"><input type="submit" value="登録内容確認"></td></tr>
</table>
</form>
<script>
$("#date_picker").datepicker({
    changeYear:true,
    showButtonPanel:true,
    showOn:'button',
    dateFormat:'yy-mm-dd',
    firstDay:1,
    showOtherMonths:true,
    selectOtherMonths:true
});
</script>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>