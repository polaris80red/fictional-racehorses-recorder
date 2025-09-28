<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
InAppUrl::init(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース結果";
$session=new Session();
// 暫定でログイン＝編集可能
$page->is_editable=Session::isLoggedIn();
// ログイン中でも強制的にプレビュー表示にできるパラメータ
$is_preview=filter_input(INPUT_GET,'preview',FILTER_VALIDATE_BOOL);
if($is_preview){
    $page->is_editable=false;
}

$pdo= getPDO();
do{
    $errorHeader="HTTP/1.1 404 Not Found";
    $page->setErrorReturnLink("レース検索に戻る",$page->to_race_list_path);

    $race_id=(string)filter_input(INPUT_GET,'race_id');
    if($race_id===''){
        $page->addErrorMsg("レースID未指定");
        break;
    }
    $race = Race::getByRaceId($pdo, $race_id);
    if(!$race){
        $page->addErrorMsg("レース情報取得失敗\n入力ID：{$race_id}");
        break;
    }
}while(false);
if($page->error_exists){
    header($errorHeader);
    $page->printCommonErrorPage();
    exit;
}
if(ENABLE_ACCESS_COUNTER){
    ArticleCounter::countup($pdo,ArticleCounter::TYPE_RACE_RESULT,$race_id);
}
$show_registration_only=(bool)filter_input(INPUT_GET,'show_registration_only');
$session->latest_race=[
    'id'=>$race_id,
    'year'=>$race->year,
    'name'=>$race->race_short_name?:$race->race_name
];
$session->login_return_url='race/result/?race_id='.$race_id;
$race_access_history=(new RaceAccessHistory())->set($race_id)->saveToSession();

$week_data=RaceWeek::getById($pdo,$race->week_id);
$week_month=$week_data->month??null;
$turn=$week_data->umm_month_turn??null;

$resultsGetter=new RaceResultsGetter($pdo,$race_id,$race->year);
$resultsGetter->pageIsEditable=$page->is_editable;
$resultsGetter->addOrderParts([
    "`r_results`.`result_number` IS NULL",
    "`r_results`.`result_number` ASC",
    "`r_results`.`result_order` IS NULL",
    "`r_results`.`result_order` ASC",
    "`spr`.`sort_number` IS NULL",
    "`spr`.`sort_number` ASC",
    "`r_results`.`result_text` ASC",
]);
$table_data=$resultsGetter->getTableData();
$hasThisweek=$resultsGetter->hasThisweek;
$hasSps=$resultsGetter->hasSps;
$rowNumber=$resultsGetter->rowNumber;

$mode_umm=false;
switch($setting->age_view_mode){
    case Setting::AGE_VIEW_MODE_UMAMUSUME:
    case Setting::AGE_VIEW_MODE_UMAMUSUME_S:
        $mode_umm=true;
}
$registration_only_horse_is_exists=false;
$latest_horse_exists=false;
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
<style>
.race_results td:nth-child(1){ text-align:center; }
.race_results td:nth-child(2){ text-align:center; }
.race_results td:nth-child(3){ text-align:center; }
.race_results td:nth-child(5){ text-align:center; }
.race_results td.col_corner_numbers { text-align:center; }
.race_results td.col_favourite { text-align:center; }
.race_info th{ background-color: #EEEEEE; }
.disabled_row{ background-color: #dddddd; }
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php echo $page->title; ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php include (new TemplateImporter('race/race_page-content_header.inc.php'));?>
<?php include (new TemplateImporter('race/race-results_table.inc.php'));?>
<hr class="no-css-fallback">
<div style="margin-top: 4px;">
<?php
    $prev_tag=new MkTagA('前メモ');
    if($resultsGetter->hasPreviousNote||$race->previous_note){
        $prev_tag->href(InAppUrl::to('race/race_previous_note.php',['race_id'=>$race_id]));
        $prev_tag->title("レース前メモ");
    }
    $line[]=$prev_tag;
    $after_tag=new MkTagA('後メモ');
    if($resultsGetter->hasAfterNote||$race->after_note){
        $after_tag->href(InAppUrl::to('race/race_after_note.php',['race_id'=>$race_id]));
        $after_tag->title("レース後メモ");
    }
    $line[]=$after_tag;
    $name_search_tag=new MkTagA("他年度の{$race->race_name}を検索",$page->getRaceNameSearchUrl($race->race_name));
    $line[]=$name_search_tag;
    if($registration_only_horse_is_exists||$show_registration_only){
        $a_tag=new MkTagA("特別登録のみの馬を".($show_registration_only?"非表示(現在:表示)":"表示(現在:非表示)")."");
        $a_tag->href("?race_id={$race_id}".($show_registration_only?'':"&show_registration_only=true"));
        $line[]=$a_tag;
    }
?>
<?=implode('｜',$line)?>
</div>
<hr>
<table class="race_info">
    <tr><th>名称</th><td><?=h($race->race_name)?></td></tr>
    <tr><th>略名</th><td><?=h($race->race_short_name)?></td></tr>
    <tr><th>補足</th><td style="min-width: 200px;"><?=h($race->caption)?></td></tr>
    <?php if($race->date): ?>
        <tr>
            <th>日付</th>
            <?php
            $a_tag=new MkTagA($race->date.($race->date&&$race->is_tmp_date?'(仮)':''));
            if(!$race->is_tmp_date){
                $a_tag->href($page->getDateRaceListUrl($race->date));
            }
            ?>
            <td><?=$a_tag; ?></td>
        </tr>
    <?php endif; ?>
    <?php if(!empty($race->week_id)): ?>
        <tr>
            <th>ターン</th>
            <td><?php
                print_h($setting->getYearSpecialFormat($race->year)."｜");
                $a_tag=new MkTagA("第{$race->week_id}週");
                $a_tag->href(InAppUrl::to('race/list/in_week.php',['year'=>$race->year,'week'=>$race->week_id]));
                $a_tag->print();
                print "｜";
                $a_tag=new MkTagA("{$week_month}月".($turn===2?"後半":"前半"));
                $a_tag->href(InAppUrl::to('race/list/in_week.php',['year'=>$race->year,'month'=>$week_month,'turn'=>$turn]));
                $a_tag->print();
            ?></td>
        </tr>
    <?php endif; ?>
    <?php if(!empty($page->is_editable)): ?>
        <tr>
            <th>ワールド</th>
            <td><?=h((World::getById($pdo,$race->world_id))->name??'')?></td>
        </tr>
    <?php endif; ?>
    <tr>
        <th>備考</th>
        <td><?=nl2br(h($race->note))?></td>
    </tr>
</table>
<?php if($page->is_editable): ?>
    <?php include (new TemplateImporter('race/race_page-edit_menu.inc.php'));?>
<?php endif; ?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>