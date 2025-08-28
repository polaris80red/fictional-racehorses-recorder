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
if(!Session::is_logined()){ $page->exitToHome(); }

$pdo=getPDO();
$input_id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
$world=new World();
if($input_id>0){
    $world->getDataById($pdo,$input_id);
    if($world->record_exists){
        $page->title.="（編集）";
    }else{
        $world->id=0;
    }
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
        print $world->id?:"新規登録";
        HTPrint::Hidden('world_id',$world->id);
    ?></td>
</tr>
<tr>
    <th>名称</th>
    <td class="in_input"><input type="text" name="name" class="required" value="<?php print $world->name; ?>" required></td>
</tr>
<tr>
    <th>非ログイン時<br>設定画面</th>
    <td>
        <label><?php HTPrint::Radio('guest_visible',1,$world->guest_visible); ?>選択肢に表示する</label><br>
        <label><?php HTPrint::Radio('guest_visible',0,$world->guest_visible); ?>選択肢に表示しない</label>
    </td>
</tr>
<tr>
    <th>正規日付</th>
    <td>
        <label><?php HTPrint::Radio('use_exact_date',1,$world->use_exact_date); ?>あり前提</label><br>
        <label><?php HTPrint::Radio('use_exact_date',0,$world->use_exact_date); ?>なし前提</label>
    </td>
</tr>
<tr>
    <th>表示順優先度</th>
    <td class="in_input"><input type="number" name="sort_priority" value="<?php print $world->sort_priority; ?>"></td>
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
    <td class="in_input"><input type="text" name="auto_id_prefix" value="<?php print $world->auto_id_prefix; ?>"></td>
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