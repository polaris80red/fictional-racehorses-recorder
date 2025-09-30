<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="レース週マスタ";
$page->title="{$base_title}｜設定登録";
$page->ForceNoindex();

$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }
if(!Session::currentUser()->canManageSystemSettings()){
    header("HTTP/1.1 403 Forbidden");
    $page->addErrorMsg('システム設定管理権限がありません');
}
$pdo=getPDO();
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);

if($id>52 || $id<1){
    header('Location: ./list.php');
    exit;
}

$editMode=($id>0);
$TableClass=RaceWeek::class;
$TableRowClass=$TableClass::ROW_CLASS;

if($editMode){
    $page->title.="（編集）";
    $form_item=($TableClass)::getById($pdo,$id);
    if($form_item===false){
        $page->addErrorMsg("ID '{$id}' が指定されていますが該当するレコードがありません");
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
        HTPrint::Hidden('id',$form_item->id);
    ?></td>
</tr>
<tr>
    <th>名称</th>
    <td class="in_input"><input type="text" name="name" class="required" required value="<?=($form_item->name)?>"></td>
</tr>
<tr>
    <th>月と週補正</th>
    <td class="in_input"><select name="month" class="required" required>
    <option value="">未登録</option>
    <?php
    for ($i=1; $i <= 12; $i++) {
        $selected= $i==$form_item->month?" selected":"";
        echo "<option value=\"{$i}\" {$selected}>{$i}月</option>";
        # code...
    }
    ?></select> - <select name="month_grouping" class="required" required>
    <option value="">未登録</option>
    <?php
    for ($i=0; $i <= 5; $i++) {
        $selected= $i==($form_item->month_grouping%10)?" selected":"";
        echo "<option value=\"{$i}\" {$selected}>{$i}</option>";
        # code...
    }
    ?></select></td>
</tr>
<tr>
    <td colspan="2">ラジオボタン制御<br>補正値0は前月選択時にも表示<br>補正値5で次月選択時も表示</td>
</tr>
<tr>
    <th>ターン形式</th>
    <td>
        <label><?php
        $radio=new MkTagInputRadio('umm_month_turn',1,$form_item->umm_month_turn);
        $radio->print();
        ?>前半</label>　
        <label><?php
        $radio->value(2)->checkedIf($form_item->umm_month_turn)->print();
        ?>後半</label>
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
        $radio=new MkTagInputRadio('is_enabled',1,true);
        $radio->print();
        ?>有効</label><br>
        <label><?php
        $radio->value(0)->checked(false)->disabled(true)->print();
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