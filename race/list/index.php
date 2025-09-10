<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting(); 
$page->setSetting($setting);
$page->title="レース検索";
$session=new Session();
// 暫定でログイン＝編集可能
$page->is_editable=Session::is_logined();
$pdo= getPDO();

$search=(new RaceSearch())->setSetting($setting);
if(filter_input(INPUT_GET,'set_by_session',FILTER_VALIDATE_BOOL)){
    $search->setBySession();
}else{
    $search->setByUrl();
}
if($search->is_empty()){
    redirect_exit(APP_ROOT_REL_PATH."race/search.php");
}
$show_column_umm_turn=false;
$show_column_date=true;
if($setting->horse_record_date==='umm'){
    $show_column_umm_turn=true;
    $show_column_date=false;
}

$year=$search->year;
$search->world_id= $setting->world_id;
$stmt=$search->SelectExec($pdo);
if($year!==''){
    $prev=$year-1;
    $next=$year+1;
}
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink('js/functions.js'); ?>
<style>
td.grade{ text-align:center;}
td.race_course_name { text-align: center; }
.disabled_row{ background-color: #ccc; }
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php $page->printTitle(); ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php
// 1～3着馬を取得する処理
$race123horseGetter=new Race123HorseGetter($pdo);

$search_results=new RaceSearchResults($stmt);
$table_rows=$search_results->getAll();
$search->current_page_results_count=count($table_rows);
?>
<?php //$search->printForm($page,true,null); ?>
<!--<hr>-->
<a href="#foot" title="最下部検索フォームに移動" style="text-decoration:none;">▽検索結果</a>｜<?=h($search->getSearchParamStr())?>
<hr>
<?php if($year!==''): ?>
<a href="?year=<?=$prev?>&<?=h($search->getUrlParam(['year','page']))?>">[前年へ]</a>
<?=h($setting->getConvertedDate(['year'=>$year],'y'))?>
 <a href="?year=<?=$next?>&<?=h($search->getUrlParam(['year','page']))?>">[翌年へ]</a>
<hr>
<?php endif; ?>
<form method="get" action="<?=APP_ROOT_REL_PATH?>race/manage/duplicate/">
<?php if($page->is_editable && $search->is_one_year_only): ?>
<input type="button" value="全てチェック" onclick="toggleIdList();">
<input type="submit" value="チェックしたレースを一括複写">
<?php endif; ?>
<table class="race_list clear">
<tr>
    <td colspan="<?=($page->is_editable && $search->is_one_year_only)?8:7?>"><?=($search->limit>0)?$search->printPagination():''?></td>
    <td style="text-align: center;"><span style="white-space:nowrap;"><a href="?search_reset=1\">[検索条件初期化]</a></td>
</tr>
<tr>
<?php if($page->is_editable && $search->is_one_year_only): ?><th>複</th><?php endif; ?>
<?php if($show_column_umm_turn): ?><th>時期</th><?php endif; ?>
<?php if($show_column_date): ?><th>日付</th><?php endif; ?>
<th>場</th><th style="min-width:3.5em;">距離</th><th>格付</th><th>名称</th><th>1着馬</th><th>2着馬</th><th>3着馬</th>
</tr>
<?php foreach($table_rows as $key => $row): ?>
    <?php
        $race=$row->raceRow;
        $raceWeek=$row->weekRow;
        $raceGrade=$row->gradeRow;
        $raceCourse=$row->courseRow;
        $class=(new Imploader(' '))->add("race_grade_".$raceGrade->css_class_suffix??'');
        if($race->is_enabled===0){ $class->add('disabled_row'); }
    ?>
    <tr class="<?=$class?>">
        <?php if($page->is_editable && $search->is_one_year_only): ?>
            <td class="in_input">
                <label style="width:100%;height:100%;"><?=(new MkTagInput('checkbox',"id_list[]",$race->race_id))?></label>
            </td>
        <?php endif; ?>
        <?php
            // 正規日付があり、仮日付でない場合　と　それ以外
            $datetime=null;
            if(!is_null($race->date) && $race->is_tmp_date==0){
                $datetime=new DateTime($race->date);
                $day=(int)$datetime->format('d');
            }else{
                $day=null;
            }
            // ウマ娘ターンモードでは週マスタの月指定を優先
            $month=$race->month;
            if($setting->horse_record_date==='umm'){
                $month=$raceWeek->month??$race->month;
            }
            $umdb_date=$setting->getRaceListDate([
                'year'=>$race->year,  // レースの年
                'month'=>$month,        // レースの月
                'day'=>$day,            // レースの日
                'turn'=>$raceWeek->umm_month_turn, // レースのターン
                'age'=>($search->is_generation_search&&$year!=null)?($race->year-$year+3):null // 計算基準年がある場合は年齢
                ],
                $search->is_one_year_only
            );
            $date_str=(string)$umdb_date;
            $date_str_year_part="";
        ?>
        <?php if($show_column_umm_turn): ?>
            <?php
                // ウマ娘ターンカラム
                $url='';
                if($raceWeek->umm_month_turn>0){
                    $url = $page->getTurnRaceListUrl($race->year,$month,$raceWeek->umm_month_turn);
                }
            ?>
            <td><?=(new MkTagA($date_str,$url))?></td>
        <?php endif; ?>
        <?php if($show_column_date): ?>
            <?php
                // 年月日カラム
                $date_url="";
                if($datetime!==null){
                    $date_url=$page->getDateRaceListUrl($datetime);
                }else{
                    $date_url=$page->getTurnRaceListUrl(
                        $race->year,$month,null,['week'=>$race->week_id]);
                }
            ?>
            <td><?=(new MkTagA($date_str,$date_url))?></td>
        <?php endif; ?>
        <?php
            // 競馬場カラム
            $a_tag=new MkTagA($raceCourse->short_name??$race->race_course_name);
            if($datetime!==null){
                $a_tag->href($page->getDateRaceListUrl(
                    $datetime,
                    ['race_course_name'=>$race->race_course_name]
                ));
                $a_tag->title($race->race_course_name);
            }
        ?>
        <td class="race_course_name"><?=$a_tag?></td>
        <td><?=h($race->course_type.$race->distance)?></td>
        <td class="grade"><?=h(($raceGrade->short_name??'')?:$race->grade)?></td>
        <td>
            <a href="<?=h($page->getRaceResultUrl($race->race_id))?>" title="<?=h($race->race_name.($race->caption?'：'.$race->caption:''))?>"><?=h($race->race_name)?></a>
        </td>
        <?php
            $race123horse=$race123horseGetter($race->race_id);
            $h1=(object)($race123horse['r1']??null);
            $h2=(object)($race123horse['r2']??null);
            $h3=(object)($race123horse['r3']??null);
        ?>
        <td><?=empty($h1->horse_id)?'':(new MkTagA(($h1->name_ja?:$h1->name_en),$page->getHorsePageUrl($h1->horse_id)))?></td>
        <td><?=empty($h2->horse_id)?'':(new MkTagA(($h2->name_ja?:$h2->name_en),$page->getHorsePageUrl($h2->horse_id)))?></td>
        <td><?=empty($h3->horse_id)?'':(new MkTagA(($h3->name_ja?:$h3->name_en),$page->getHorsePageUrl($h3->horse_id)))?></td>
    </tr>
<?php endforeach; ?>
</table>
<?php if($search->limit>0): ?>
<hr>
<?=$search->printPagination()?>
<?php endif; ?>
<?php if($year!==''): ?>
<hr>
<a href="?year=<?=$prev?>&<?=h($search->getUrlParam(['year','page']))?>">[前年へ]</a>
<?=h($setting->getConvertedDate(['year'=>$year],'y'))?>
 <a href="?year=<?=$next?>&<?=h($search->getUrlParam(['year','page']))?>">[翌年へ]</a>
<?php endif; ?>
</form>
<hr><a id="foot"></a>
<?php $search->printForm($page,false,true); ?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
<?php $page->printScriptLink('js/race_search_form.js'); ?>
<script>
function toggleIdList() {
    const $targets = $('input[type="checkbox"][name^="id_list"]');
    const allChecked = $targets.length > 0 && $targets.filter(':checked').length === $targets.length;

    if (allChecked) {
        $targets.prop('checked', false);
    } else {
        $targets.prop('checked', true);
    }
}
</script>
</body>
</html>