<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="ワールド登録：内容確認";
$page->ForceNoindex();

$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }
if(!Session::currentUser()->canManageSystemSettings()){
    header("HTTP/1.1 403 Forbidden");
    $page->addErrorMsg('システム設定管理権限がありません');
}
$pdo=getPDO();
$inputId=filter_input(INPUT_POST,'world_id',FILTER_VALIDATE_INT);
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
$world->name=filter_input(INPUT_POST,'name');
$world->guest_visible=filter_input(INPUT_POST,'guest_visible',FILTER_VALIDATE_BOOL)?1:0;
$world->auto_id_prefix=filter_input(INPUT_POST,'auto_id_prefix');
$world->sort_priority=filter_input(INPUT_POST,'sort_priority',FILTER_VALIDATE_INT);
$sort_number=(string)filter_input(INPUT_POST,'sort_number');
$world->sort_number = $sort_number===''?null:(int)$sort_number;
$world->is_enabled=filter_input(INPUT_POST,'is_enabled',FILTER_VALIDATE_BOOL)?1:0;

if(!$world->validate()){
    $page->addErrorMsgArray($world->errorMessages);
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
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<form method="post" action="./registration_execute.php">
<?php if(!$world->id): ?>
新規登録の場合、登録完了後はストーリー設定（表示設定）の新規登録に進みます。
<?php endif; ?>
<table class="edit-form-table">
<tr>
    <th>ID</th>
    <td><?php
        print_r($world->id?:"新規登録");
        HTPrint::Hidden('world_id',$world->id);
    ?></td>
</tr>
<tr>
    <th>名称</th>
    <td><?php HTPrint::HiddenAndText('name',$world->name); ?></td>
</tr>
<tr>
    <th>非ログイン時<br>設定画面</th>
    <td>
        <?php HTPrint::Hidden('guest_visible',$world->guest_visible); ?>
        <?=$world->guest_visible?'表示':'非表示'?>
    </td>
</tr>
<tr>
    <th>表示順優先度</th>
    <td><?php HTPrint::HiddenAndText('sort_priority',$world->sort_priority); ?></td>
</tr>
<tr>
    <th>表示順補正</th>
    <td><?php HTPrint::HiddenAndText('sort_number',$world->sort_number); ?></td>
</tr>
<?php if($world->id!==0): ?>
<tr>
    <th>選択肢</th>
    <td><?php
        HTPrint::Hidden('is_enabled',$world->is_enabled);
        print $world->is_enabled?'表示':'非表示';
    ?></td>
</tr>
<?php endif; ?>
<tr>
    <th>自動ID接頭語</th>
    <td><?php HTPrint::HiddenAndText('auto_id_prefix',$world->auto_id_prefix); ?></td>
</tr>
<tr><td colspan="2" style="text-align: left;"><input type="submit" value="登録実行"></td></tr>
<?php (new FormCsrfToken())->printHiddenInputTag(); ?>
</table>
<?php if($world->id===0){ HTPrint::Hidden('is_enabled',1); } ?>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>