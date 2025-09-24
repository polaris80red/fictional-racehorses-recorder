<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
InAppUrl::init(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="競走馬情報｜プロフィール";
$session=new Session();

$page->error_return_url=InAppUrl::to("horse/search");
$page->error_return_link_text="競走馬検索に戻る";
if(empty($_GET['horse_id'])){
    $page->error_msgs[]="競走馬ID未指定";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
$pdo= getPDO();
$horse_id=filter_input(INPUT_GET,'horse_id');
$horse=Horse::getByHorseId($pdo,$horse_id);
if($horse===false){
    $page->error_msgs[]="競走馬情報取得失敗";
    $page->error_msgs[]="入力ID：{$horse_id}";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
$page->horse = $horse;
if(ENABLE_ACCESS_COUNTER){
    ArticleCounter::countup($pdo,'horse_profile',$horse_id);
}
// 編集可否チェック
$page->is_editable=Session::isLoggedIn() && Session::currentUser()->canHorseEdit($horse);

// ログイン中でも強制的にプレビュー表示にできるパラメータ
$is_preview=filter_input(INPUT_GET,'preview',FILTER_VALIDATE_BOOL);
if($is_preview){
    $page->is_editable=false;
}
$get_order=filter_input(INPUT_GET,'horse_history_order');
switch($get_order){
    case 'asc':
        $setting->saveToSession('hors_history_sort_is_desc',false);
        break;
    case 'desc':
        $setting->saveToSession('hors_history_sort_is_desc',true);
        break;
}

$date_order = $setting->hors_history_sort_is_desc ? 'DESC':'ASC';
$show_registration_only=(bool)filter_input(INPUT_GET,'show_registration_only');

$page_urlparam=new UrlParams([
    'horse_id'=>$horse_id,
    'horse_history_order'=>$get_order==='desc'?'desc':'asc',
    'show_registration_only'=>$show_registration_only,
]);

$page->error_return_url=InAppUrl::to("horse/",['horse_id'=>$horse_id]);
$page->error_return_link_text="競走馬ページに戻る";
if($horse->profile==''){
    $page->error_msgs[]="プロフィール未登録の競走馬情報です";
    $page->error_msgs[]="入力ID：{$horse_id}";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}

$session->latest_horse=[
    'id'=>$horse_id,
    'name'=>$horse->name_ja?:$horse->name_en
];
$session->login_return_url='horse/?horse_id='.$horse_id;

$sex_str=sex2String($horse->sex);
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink("js/functions.js"); ?>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php $page->printTitle(); ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php include (new TemplateImporter('horse/horse_page-header.inc.php'));?>
<?=nl2br(h($horse->profile))?>
<?php $horse_tags=(new HorseTag($pdo))->getTagNames($page->horse->horse_id); ?>
<?php if(count($horse_tags)>0):?>
<hr>
検索タグ：<?php
        $search_text_search=new HorseSearch();
        $links=new Imploader('　');
        foreach($horse_tags as $tag){
            $search_text_search->search_text = $tag;
            $url ="{$page->to_horse_search_path}?".$search_text_search->getUrlParam();
            $links->add("<a href=\"".h($url)."\">#".h($tag)."</a>");
        }
        print($links);
?>
<?php endif; ?>
<a id="under_results_table"></a>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>