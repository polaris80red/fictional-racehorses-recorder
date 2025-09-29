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
$page->renderErrorsAndExitIfAny($errorHeader);
$sex_str=sex2String($horse->sex);
$urlparam=new UrlParams(['horse_id'=>$horse_id]);

$title=(function($pageTitle)use($horse){
    $t = $horse->name_ja?:'';
    if($horse->name_en){
        $t.=$t?" ({$horse->name_en})":$horse->name_en;
    }
    return $pageTitle.($t?" | {$t}":'');
})($page->title);
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?=h($page->renderTitle($title))?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
</head>
<body>
<header>
 <?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?=h($page->title)?></h1>
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