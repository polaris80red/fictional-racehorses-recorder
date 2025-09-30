<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
defineAppRootRelPath(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="ワールド管理｜IDの一括変更";
$page->ForceNoindex();
$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }

$world_id=filter_input(INPUT_GET,'id');

$pdo= getPDO();
do{
    if(!Session::currentUser()->canManageSystemSettings()){
        header("HTTP/1.1 403 Forbidden");
        $page->addErrorMsg('システム設定管理権限がありません');
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
?><!DOCTYPE html>
<html>
<head>
    <title><?=h($page->renderTitle())?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?=$page->renderBaseStylesheetLinks()?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink('js/functions.js'); ?>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<form action="confirm.php" method="post">
<table class="edit-form-table">
<ul>
    <li>ID[<?=h($world_id)?>]のワールドのIDと、競走馬・レース情報のワールドIDを一括で変更します。</li>
    <li>ストーリー設定のワールドIDは変更できないため、手動で変更を保存してください。</li>
    <li>※ 別環境に移植するデータを作るための準備を想定しています。</li>
</ul>
<tr>
    <th>変更対象</th>
    <td style="min-width:15em;"><?php HTPrint::HiddenAndText('world_id',$world_id); ?></td>
</tr>
<tr>
    <th>新ID</th>
    <td class="in_input"><input type="text" name="new_world_id" value=""></td>
</tr>
</table>
<hr>
<input type="submit" value="処理内容確認">
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>
