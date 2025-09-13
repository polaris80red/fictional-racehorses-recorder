<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
InAppUrl::init(2);
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース結果";
$session=new Session();
// 暫定でログイン＝編集可能
$page->is_editable=Session::is_logined();
// ログイン中でも強制的にプレビュー表示にできるパラメータ
$is_preview=filter_input(INPUT_GET,'preview',FILTER_VALIDATE_BOOL);
if($is_preview){
    $page->is_editable=false;
}

$page->error_return_url=$page->to_race_list_path;
$page->error_return_link_text="レース検索に戻る";

$pdo= getPDO();

$is_edit_mode = false;
if(filter_input(INPUT_GET,'mode')==='edit'){
    $is_edit_mode = true;
}
$is_edit_mode=true;
if(empty($_GET['race_id'])){
    $page->error_msgs[]="レースID未指定";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
$race_id=filter_input(INPUT_GET,'race_id');
$show_registration_only=(bool)filter_input(INPUT_GET,'show_registration_only');
# レース情報取得
$race = new Race($pdo, $race_id);
if(!$race->record_exists){
    $page->error_msgs[]="レース情報取得失敗";
    $page->error_msgs[]="入力ID：{$race_id}";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
if(ENABLE_ACCESS_COUNTER){
    ArticleCounter::countup($pdo,ArticleCounter::TYPE_RACE_RESULT,$race_id);
}
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
<hr>
<?php include (new TemplateImporter('race/race-results_table.inc.php'));?>
<hr>
<a href="<?=h($page->getRaceNameSearchUrl($race->race_name))?>" style="">他年度の<?=h($race->race_name)?>を検索</a>
<?php
    if($registration_only_horse_is_exists||$show_registration_only){
        $a_tag=new MkTagA("特別登録のみの馬を".($show_registration_only?"非表示(現在:表示)":"表示(現在:非表示)")."");
        $a_tag->href("?race_id={$race_id}".($show_registration_only?'':"&show_registration_only=true"));
        $a_tag->print();
    }
    ?>
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
                $week_url_param=new UrlParams(['year'=>$race->year,'week'=>$race->week_id]);
                $a_tag=new MkTagA("第{$race->week_id}週");
                $a_tag->href($page->to_app_root_path."race/list/in_week.php?".$week_url_param);
                $a_tag->print();
                print "｜";
                $turn_url_param=new UrlParams(['year'=>$race->year,'month'=>$week_month,'turn'=>$turn]);
                $a_tag=new MkTagA("{$week_month}月".($turn===2?"後半":"前半"));
                $a_tag->href($page->to_app_root_path."race/list/in_week.php?".$turn_url_param);
                $a_tag->print();
            ?></td>
        </tr>
    <?php endif; ?>
    <?php if(!empty($page->is_editable)): ?>
        <tr>
            <th>ワールド</th>
            <td><?=h((new World($pdo,$race->world_id))->name??'')?></td>
        </tr>
    <?php endif; ?>
    <tr>
        <th>備考</th>
        <td><?=nl2br(h($race->note))?></td>
    </tr>
    <tr>
        <th>note</th>
        <?php
            $prev_tag=new MkTagA('前メモ');
            if($resultsGetter->hasPreviousNote){
                $prev_tag->href(InAppUrl::to('race/race_previous_note.php',['race_id'=>$race_id]));
                $prev_tag->title("レース前メモ");
            }
            $after_tag=new MkTagA('後メモ');
            if($resultsGetter->hasAfterNote){
                $after_tag->href(InAppUrl::to('race/race_after_note.php',['race_id'=>$race_id]));
                $after_tag->title("レース後メモ");
            }
        ?>
        <td><?=$prev_tag?>｜<?=$after_tag?></td>
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