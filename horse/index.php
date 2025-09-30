<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
InAppUrl::init(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="競走馬情報";
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
$page->horse = $horse;
if(ENABLE_ACCESS_COUNTER){
    ArticleCounter::countup($pdo,ArticleCounter::TYPE_HORSE,$horse_id);
}
// 編集可否チェック
$page->is_editable=Session::isLoggedIn() && Session::currentUser()->canEditHorse($horse);

// ログイン中でも強制的にプレビュー表示にできるパラメータ
$is_preview=filter_input(INPUT_GET,'preview',FILTER_VALIDATE_BOOL);

$show_race_note=filter_input(INPUT_GET,'show_race_note',FILTER_VALIDATE_BOOL);
$show_horse_note=filter_input(INPUT_GET,'show_horse_note',FILTER_VALIDATE_BOOL);
if($is_preview){
    $page->is_editable=false;
}
$page->has_edit_menu=true;

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

$page_urlparam=new UrlParams(array_diff([
    'horse_id'=>$horse_id,
    'horse_history_order'=>$get_order==='desc'?'desc':'asc',
    'show_registration_only'=>$show_registration_only,
    'preview'=>$is_preview,
],[0,false,null]));

$session->latest_horse=[
    'id'=>$horse_id,
    'name'=>$horse->name_ja?:$horse->name_en
];
$session->login_return_url='horse/?horse_id='.$horse_id;

$is_broodmare=$is_sire=false;
// 「繁殖馬である」
if($horse->is_sire_or_dam===1){
    if($horse->sex===2){
        $is_broodmare=true;
    }else if($horse->sex===1){
        $is_sire=true;
    }
}
$race_history=new HorseRaceHistory($pdo,$horse_id);
$race_history->setDateOrder($date_order);
$race_history->getData();

$sex_str=sex2String($horse->sex);

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
    <?=$page->renderBaseStylesheetLinks()?>
    <?=$page->renderJqueryResource()?>
    <?=$page->renderScriptLink("js/functions.js")?>
<style>
.horse_base_data th{ min-width:80px; }
.horse_base_data td{ min-width:160px; }
.disabled_row{ background-color: #dddddd; }

td.track_condition{ text-align:center; }
td.race_course_name { text-align: center; }
td.grade{ text-align:center; }
td.number_of_starters{ text-align:right; }
td.favourite{ text-align:right; }
td.result_number{ text-align:right; }
td.h_weight{ text-align:center; }
table.horse_base_data a {text-decoration: none;}
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php include (new TemplateImporter('horse/horse_page-header.inc.php'));?>
<?php include (new TemplateImporter('horse/horse_page-profile_1.inc.php'));?>
<hr>
<span><?php
print_h($race_history->race_count_1."-");
print_h($race_history->race_count_2."-");
print_h($race_history->race_count_3."-");
?><span style="<?=$race_history->has_unregistered_race_results?'color:#999999;':'';?>"><?php
print_h($race_history->race_count_4+$race_history->race_count_5+$race_history->race_count_n." / ");
print_h($race_history->race_count_all."戦");
?></span></span>
<?php
if($is_broodmare){
    $foal_search=new HorseSearch();
    $foal_search->mare_id=$horse_id;
    $foal_search->order='birth_year__asc';
    $url = $page->to_horse_search_path.'?'.$foal_search->getUrlParam();
    $a_tag=new MkTagA('産駒',$url);
    echo "｜";
    $a_tag->print();
}
if($is_sire){
    $foal_search=new HorseSearch();
    $foal_search->sire_id=$horse_id;
    $foal_search->order='birth_year__asc';
    $url = $page->to_horse_search_path.'?'.$foal_search->getUrlParam();
    $a_tag=new MkTagA('産駒',$url);
    echo "｜";
    $a_tag->print();
}
$mode_umm=false;
switch($setting->age_view_mode){
    case Setting::AGE_VIEW_MODE_UMAMUSUME:
    case Setting::AGE_VIEW_MODE_UMAMUSUME_S:
        $mode_umm = true;
    break;
}
?>
<hr>
<?php include (new TemplateImporter('horse/horse_page-race_history.inc.php'));?>
<a id="under_results_table"></a>
<?php
    $tpl=new TemplateImporter('horse/horse_page-profile_2.inc.php');
    if($tpl->fileExists()){
        print('<hr>');
        include $tpl;
    }
?>
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
<?php if($page->is_editable): ?>
    <?php include (new TemplateImporter('horse/horse_page-edit_menu.inc.php'));?>
<?php endif; ?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>