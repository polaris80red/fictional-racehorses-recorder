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
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink('js/functions.js'); ?>
<style>
td:nth-child(4){
    text-align:center;
}
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
$table_data=[];

// 1～3着馬を取得
$race123horseGetter=new Race123HorseGetter($pdo);

while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data=array_merge($data,$race123horseGetter($data['race_id']));
    $table_data[]=$data;
}
$search->current_page_results_count=count($table_data);
?>
<?php //$search->printForm($page,true,null); ?>
<!--<hr>-->
<?php print "<a href=\"#foot\" title=\"最下部検索フォームに移動\" style=\"text-decoration:none;\">▽検索結果</a>｜".$search->getSearchParamStr(); ?>
<hr>
<?php
if($year!==''){
    echo '<a href="?year='.$prev.'&'.$search->getUrlParam(['year','page']).'">[前年へ]</a> ';
    echo $setting->getYearSpecialFormat($year);
    if($setting->year_view_mode==0){ echo "年"; }
    if($setting->year_view_mode==2){ echo "年"; }
    echo ' <a href="?year='.$next.'&'.$search->getUrlParam(['year','page']).'">[翌年へ]</a>';
    echo "<hr>\n";
}
echo "<table class=\"race_list clear\">\n";
echo "<tr><td colspan=\"8\">";
if($search->limit>0){
    $search->printPagination();
}
echo "<span style=\"white-space:nowrap; display:inline-block; float:right;\"><a href=\"?search_reset=1\">[検索条件初期化]</a>";
echo "</td></tr>";
?><tr>
<?php if($show_column_umm_turn): ?><th>時期</th><?php endif; ?>
<?php if($show_column_date): ?><th>日付</th><?php endif; ?>
<th>場</th><th style="min-width:3.5em;">距離</th><th>格付</th><th>名称</th><th>1着馬</th><th>2着馬</th><th>3着馬</th>
</tr><?php
foreach($table_data as $data){
    $class=[];
    $class[]="race_grade_".$data['grade_css_class_suffix']??'';
    if($data['is_enabled']===0){ $class[]='disabled_row'; }
    echo '<tr class="'.implode(' ',$class).'">';
    // 正規日付があり、仮日付でない場合　と　それ以外
    $datetime=null;
    if(!is_null($data['date']) && $data['is_tmp_date']==0){
        $datetime=new DateTime($data['date']);
        $day=(int)$datetime->format('d');
    }else{
        $day=null;
    }
    // ウマ娘ターンモードでは週マスタの月指定を優先
    $month=$data['month'];
    if($setting->horse_record_date==='umm'){
        $month=$data['w_month']??$data['month'];
    }
    $umdb_date=$setting->getRaceListDate([
        'year'=>$data['year'],  // レースの年
        'month'=>$month,        // レースの月
        'day'=>$day,            // レースの日
        'turn'=>$data['umm_month_turn'], // レースのターン
        'age'=>($search->is_generation_search&&$year!=null)?($data['year']-$year+3):null // 計算基準年がある場合は年齢
        ],
        $search->is_one_year_only
    );
    $date_str=(string)$umdb_date;
    $date_str_year_part="";
    // ウマ娘ターンカラム
    if($show_column_umm_turn){
        $url='';
        if($data['umm_month_turn']>0){
            $url = $page->getTurnRaceListUrl($data['year'],$month,$data['umm_month_turn']);
        }
        echo "<td class=\"turn\">";
        (new MkTagA($date_str,$url))->print();
        echo "</td>";
    }
    // 年月日カラム
    if($show_column_date){
        $date_url="";
        if($datetime!==null){
            $date_url=$page->getDateRaceListUrl($datetime);
        }
        echo "<td>".(new MkTagA($date_str,$date_url))."</td>";
    }
    // 競馬場カラム
    $race_course_show_name = $data['race_course_mst_short_name']??$data['race_course_name'];
    $a_tag=new MkTagA($race_course_show_name);
    if($datetime!==null){
        $a_tag->href($page->getDateRaceListUrl(
            $datetime,
            ['race_course_name'=>$data['race_course_name']]
        ));
        $a_tag->title($data['race_course_name']);
    }
    echo "<td class=\"race_course_name\">{$a_tag}</td>";
    echo "<td>{$data['course_type']}{$data['distance']}</td>";
    echo "<td class=\"grade\">".(($data['grade_short_name']??'')?:$data['grade'])."</td>";
    echo "<td>";
    echo '<a href="'.$page->getRaceResultUrl($data['race_id']).'" title="'.$data['race_name'].($data['caption']?'：'.$data['caption']:'').'">';
    echo $data['race_name'];
    echo "</a>\t";
    echo "</td>";
    echo "<td>";
    $a_tag=new MkTagA();
    if(!empty($data['r1']['horse_id'])){
        $a_tag->setLinkText($data['r1']['name_ja']?:$data['r1']['name_en']);
        $a_tag->href($page->getHorsePageUrl($data['r1']['horse_id']));
        echo $a_tag;
    }
    echo "</td>";
    echo "<td>";
    if(!empty($data['r2']['horse_id'])){
        $a_tag->setLinkText($data['r2']['name_ja']?:$data['r2']['name_en']);
        $a_tag->href($page->getHorsePageUrl($data['r2']['horse_id']));
        echo $a_tag;
    }
    echo "</td>";
    echo "<td>";
    if(!empty($data['r3']['horse_id'])){
        $a_tag->setLinkText($data['r3']['name_ja']?:$data['r3']['name_en']);
        $a_tag->href($page->getHorsePageUrl($data['r3']['horse_id']));
        echo $a_tag;
    }
    echo "</td>";
    echo "</tr>\n";
}
echo "</table>\n";
if($search->limit>0){
    echo "<hr>\n";
    $search->printPagination();
}
if($year!==''){
    echo "<hr>\n";
    echo '<a href="?year='.$prev.'&'.$search->getUrlParam(['year','page']).'">[前年へ]</a> ';
    echo $setting->getYearSpecialFormat($year);
    if($setting->year_view_mode==0){ echo "年"; }
    if($setting->year_view_mode==2){ echo "年"; }
    echo ' <a href="?year='.$next.'&'.$search->getUrlParam(['year','page']).'">[翌年へ]</a>';
}
?>
<hr><a id="foot"></a>
<?php $search->printForm($page,false,true); ?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
<?php $page->printScriptLink('js/race_search_form.js'); ?>
</body>
</html>