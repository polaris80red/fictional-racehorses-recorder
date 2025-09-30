<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="ストーリー";
$page->title="{$base_title}設定登録：内容確認";
$page->ForceNoindex();

$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }
if(!Session::currentUser()->canManageSystemSettings()){
    header("HTTP/1.1 403 Forbidden");
    $page->addErrorMsg('システム設定管理権限がありません');
}

$pdo=getPDO();
$inputId=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
$editMode=($inputId>0);
$TableClass=WorldStory::class;
$TableRowClass=$TableClass::ROW_CLASS;

if($editMode){
    $page->title.="（編集）";
    $story=($TableClass)::getById($pdo,$inputId);
    if($story===false){
        $page->addErrorMsg("ID '{$inputId}' が指定されていますが該当する設定がありません");
    }
}else{
    $story=new ($TableRowClass)();
}
$story->name=filter_input(INPUT_POST,'name');
$story->guest_visible=filter_input(INPUT_POST,'guest_visible',FILTER_VALIDATE_BOOL)?1:0;
$story->sort_priority=filter_input(INPUT_POST,'sort_priority');
$story->sort_number=filter_input(INPUT_POST,'sort_number');
$story->is_read_only=filter_input(INPUT_POST,'is_read_only',FILTER_VALIDATE_BOOL)?1:0;
$story->is_enabled=filter_input(INPUT_POST,'is_enabled',FILTER_VALIDATE_BOOL)?1:0;

$save_to_session=filter_input(INPUT_POST,'save_to_session',FILTER_VALIDATE_BOOL);
$save_to_defaults=filter_input(INPUT_POST,'save_to_defaults',FILTER_VALIDATE_BOOL);

$save_setting=new Setting();
$save_setting->setByStdClass($_POST);
$config_json_data=$save_setting->getSettingArray();
if(isset($_POST['save_target']) && is_array($_POST['save_target'])){
    $diff_array=$_POST['save_target'];
    // 複数欄共通チェックボックスは比較用配列を差し替え
    if(!empty($diff_array['year_select_min_max_diff'])){
        unset($diff_array['year_select_min_max_diff']);
        $diff_array['year_select_max_diff']=1;
        $diff_array['year_select_min_diff']=1;
    }
    if(!empty($diff_array['race_search_org'])){
        unset($diff_array['race_search_org']);
        $diff_array['race_search_org_jra']=1;
        $diff_array['race_search_org_nar']=1;
        $diff_array['race_search_org_other']=1;
    }
    $config_json_data=array_intersect_key($config_json_data,$diff_array);
}

if(!$story->validate()){
    $page->addErrorMsgArray($story->errorMessages);
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
<form method="post" action="./registration_execute.php">
<table class="edit-form-table">
<tr>
    <th>ID</th>
    <td><?php
        print_h($story->id?:"新規登録");
        HTPrint::Hidden('id',$story->id);
    ?></td>
</tr>
<tr>
    <th>名称</th>
    <td><?php HTPrint::HiddenAndText('name',$story->name); ?></td>
</tr>
<tr>
    <th>非ログイン時<br>設定画面</th>
    <td>
        <?php HTPrint::Hidden('guest_visible',$story->guest_visible); ?>
        <?=$story->guest_visible?'表示':'非表示'?>
    </td>
</tr>
<tr>
    <th>表示順優先度</th>
    <td><?php HTPrint::HiddenAndText('sort_priority',$story->sort_priority); ?></td>
</tr>
<tr>
    <th>表示順補正</th>
    <td><?php HTPrint::HiddenAndText('sort_number',$story->sort_number); ?></td>
</tr>
<tr>
    <th>読取専用</th>
    <td><?php
        HTPrint::Hidden('is_read_only',$story->is_read_only);
        print $story->is_read_only?'はい':'いいえ';
    ?></td>
</tr>
<tr>
    <th>選択肢</th>
    <td><?php
        HTPrint::Hidden('is_enabled',$story->is_enabled);
        print $story->is_enabled?'表示':'非表示';
    ?></td>
</tr>
<tr><td colspan="2" style="text-align: left;"><input type="submit" value="登録実行"></td></tr>
</table>
<input type="hidden" name="save_to_session" value="<?=$save_to_session?"true":"false"?>">
セッションへの反映：<?=$save_to_session?"する":"しない"?><br>
<input type="hidden" name="save_to_defaults" value="<?=$save_to_defaults?"true":"false"?>">
デフォルト設定の上書き：<?=$save_to_defaults?"する":"しない"?><br>
<pre style="border:solid 1px #333333"><?=h(json_encode($config_json_data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE))?></pre>
<?php HTPrint::Hidden('config_json',json_encode($config_json_data)); ?>
<?php (new FormCsrfToken())->printHiddenInputTag(); ?>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>