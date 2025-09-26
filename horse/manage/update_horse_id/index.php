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
    if($horse_id==''){
        header("HTTP/1.1 404 Not Found");
        $page->addErrorMsg('元ID未指定');
        break;
    }
    $horse=Horse::getByHorseId($pdo,$horse_id);
    if(!$horse){
        header("HTTP/1.1 404 Not Found");
        $page->addErrorMsg('元ID馬情報取得失敗');
        $page->addErrorMsg("入力元ID：{$horse_id}");
        break;
    }
    if($horse && !Session::currentUser()->canEditHorse($horse)){
        header("HTTP/1.1 403 Forbidden");
        $page->addErrorMsg("編集権限がありません");
        break;
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
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
