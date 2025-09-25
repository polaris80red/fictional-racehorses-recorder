<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="ワールド登録";
$page->ForceNoindex();

$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }
if(!Session::currentUser()->canManageSystemSettings()){
    header("HTTP/1.1 403 Forbidden");
    $page->addErrorMsg('システム設定管理権限がありません');
}
$pdo=getPDO();
$inputId=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
$editMode=($inputId>0);
$TableClass=World::class;
$TableRowClass=$TableClass::ROW_CLASS;

if($editMode){
    $page->title.="（編集）";
    $world=($TableClass)::getById($pdo,$inputId);
    if($world===false){
        $page->addErrorMsg("ワールドID '{$inputId}' が指定されていますが該当するワールドがありません");
    }
}else{
    $world=new ($TableRowClass)();
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
<hr>
<?php if(!$world->id): ?>
新規登録の場合、登録完了後はストーリー設定（表示設定）の新規登録に進みます。
<?php endif; ?>
<table class="edit-form-table">
<tr>
    <th>ID</th>
    <td><?php
        print_h($world->id?:"新規登録");
        HTPrint::Hidden('world_id',$world->id);
    ?></td>
</tr>
<tr>
    <th>名称</th>
    <td class="in_input"><input type="text" name="name" class="required" value="<?=h($world->name); ?>" required></td>
</tr>
<tr>
    <th>非ログイン時<br>設定画面</th>
    <td>
        <label><?php HTPrint::Radio('guest_visible',1,$world->guest_visible); ?>選択肢に表示する</label><br>
        <label><?php HTPrint::Radio('guest_visible',0,$world->guest_visible); ?>選択肢に表示しない</label>
    </td>
</tr>
<tr>
    <th>表示順優先度</th>
    <td class="in_input"><input type="number" name="sort_priority" value="<?=h($world->sort_priority)?>"></td>
</tr>
<tr>
    <th>表示順補正</th>
    <td class="in_input"><input type="number" name="sort_number" value="<?=h($world->sort_number)?>" placeholder="同優先度内昇順"></td>
</tr>
<tr>
    <th>選択肢</th>
    <td>
        <label><?php HTPrint::Radio('is_enabled',1,$world->is_enabled); ?>表示</label><br>
        <label><?php HTPrint::Radio('is_enabled',0,$world->is_enabled,$world->id>0?'':'disabled'); ?>非表示</label>
    </td>
</tr>
<tr>
    <th>自動ID接頭語</th>
    <td class="in_input"><input type="text" name="auto_id_prefix" value="<?=h($world->auto_id_prefix)?>"></td>
</tr>
<tr>
    <td colspan="2"></td>
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