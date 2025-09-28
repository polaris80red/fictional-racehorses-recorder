<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
InAppUrl::init(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="競走馬戦績等エクスポート";
$page->ForceNoindex();
$session=new Session();

$pdo= getPDO();
$page->setErrorReturnLink("競走馬検索に戻る",InAppUrl::to("horse/search"));
$errorHeader="HTTP/1.1 404 Not Found";
do{
    $horse_id=(string)filter_input(INPUT_GET,'horse_id');
    if($horse_id==''){
        $page->addErrorMsg("競走馬ID未指定");
        break;
    }
    $horse=Horse::getByHorseId($pdo,$horse_id);
    if($horse===false){
        $page->addErrorMsg("競走馬情報取得失敗\n入力ID：{$horse_id}");
        break;
    }
}while(false);
if($page->error_exists){
    header($errorHeader);
    $page->printCommonErrorPage();
    exit;
}
$sex_str=sex2String($horse->sex);
$urlparam=new UrlParams(['horse_id'=>$horse_id]);
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle();  ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
</head>
<body>
<header>
 <?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php $page->printTitle(); ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php include (new TemplateImporter('horse/horse_page-header.inc.php'));?>
<?php include (new TemplateImporter('horse/export/index.inc.php')); ?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>