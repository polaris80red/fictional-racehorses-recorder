<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="ストーリー設定登録";
$page->ForceNoindex();

$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$pdo=getPDO();
$input_id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
$story=new WorldStory();
$s_setting=new Setting(false);
if($input_id>0){
    $story->getDataById($pdo,$input_id);
    if($story->record_exists){
        $page->title.="（編集）";
        $s_setting->setByStdClass($story->config_json);
    }else{
        $story->id=0;
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
        print $story->id?:"新規登録";
        HTPrint::Hidden('story_id',$story->id);
    ?></td>
</tr>
<tr>
    <th>名称</th>
    <td class="in_input"><input type="text" name="name" class="required" value="<?php print $story->name; ?>" required></td>
</tr>
<tr>
    <th>非ログイン時<br>設定画面</th>
    <td>
        <label><?php HTPrint::Radio('guest_visible',1,$story->guest_visible); ?>選択肢に表示する</label><br>
        <label><?php HTPrint::Radio('guest_visible',0,$story->guest_visible); ?>選択肢に表示しない</label>
    </td>
</tr>
<tr>
    <th>表示順優先度</th>
    <td class="in_input"><input type="number" name="sort_priority" value="<?php print $story->sort_priority; ?>"></td>
</tr>
<tr>
    <th>表示順(同優先度内で昇順)</th>
    <td class="in_input"><input type="number" name="sort_number" value="<?php print $story->sort_number; ?>" placeholder="同優先度内で昇順"></td>
</tr>
<tr>
    <th>読取専用</th>
    <td><?php $radio=new MkTagInputRadio('is_read_only',0,$story->is_read_only); ?>
        <label><?php print($radio); ?>いいえ</label><br>
        <label><?php
        $radio->value(1)->checkedIf($story->is_read_only)
        ->disabled($story->id>0?false:true)->print();
        ?>はい（上書き候補から隠す）</label>
    </td>
</tr>
<tr>
    <th>選択肢</th>
    <td>
        <label><?php
        $radio=new MkTagInputRadio('is_enabled',1,$story->is_enabled);
        $radio->print();
        ?>表示</label><br>
        <label><?php
        $radio->value(0)->checkedIf($story->is_enabled)
        ->disabled($story->id>0?false:true)->print();
        ?>非表示</label>
    </td>
</tr>
<tr><td colspan="2" style="text-align: right;"><input type="submit" value="登録内容確認"></td></tr>
</table>
設定値確認用：<br>
<textarea name="config_json" style="min-width:25em;min-height:20em;" readonly><?php print($story->getConfigJsonEncodeText()); ?></textarea><br>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>