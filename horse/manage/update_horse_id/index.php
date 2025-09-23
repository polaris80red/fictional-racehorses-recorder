<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
defineAppRootRelPath(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="競走馬ID一括修正";
$page->ForceNoindex();
$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }

$horse_id=filter_input(INPUT_GET,'horse_id');

$pdo= getPDO();
# 対象取得
do{
}while(false);

?><!DOCTYPE html>
<html>
<head>
    <title><?php echo $page->title; ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php echo $page->title; ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<form action="confirm.php" method="post">
<table class="edit-form-table">
<tr>
    <th>置換前競走馬ID</th>
    <td><?php HTPrint::HiddenAndText('horse_id',$horse_id); ?></td>
</tr>
<tr>
    <th>置換後競走馬ID</th>
    <td class="in_input"><input type="text" name="new_horse_id" value=""></td>
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
