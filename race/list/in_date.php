<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース一覧";
$pdo= getPDO();

if(empty($_GET['date'])){
    exit;
}
$date=filter_input(INPUT_GET,'date');
$show_disabled=filter_input(INPUT_GET,'show_disabled',FILTER_VALIDATE_BOOL);
$is_jra_only=filter_input(INPUT_GET,'is_jra_only',FILTER_VALIDATE_BOOL);
$race_course_name=filter_input(INPUT_GET,'race_course_name');

$url_params=new UrlParams();
$url_params->set('date',$date);
if($show_disabled){ $url_params->set('show_disabled',true);}
if($is_jra_only){ $url_params->set('is_jra_only',true);}
if($race_course_name){ $url_params->set('race_course_name',$race_course_name);}

$datetime=new DateTime($date);
$date_str= $datetime->format('Y-m-d');
$year=$datetime->format('Y');
$weekdaynum=$datetime->format('w');
$page->title=$datetime->format('Y年m月d日')."(".getWeekDayJa($weekdaynum).")のレース一覧";

$year_week=getWeekByDate($date_str);

# レース情報取得
$horse_tbl=Horse::TABLE;
$r_results_tbl=RaceResults::TABLE;
$grade_tbl=RaceGrade::TABLE;
$pre_bind=new StatementBinder();
$race_course_tbl = RaceCourse::QuotedTable();

$sql ="SELECT `r`.*";
$sql.=",g.short_name as grade_short_name";
$sql.=",g.css_class_suffix as grade_css_class_suffix";
$sql.=",c.short_name as race_course_short_name";
$sql.=" FROM `{$r_results_tbl}` AS `r`";
$sql.=" LEFT JOIN {$race_course_tbl} AS c ON r.race_course_name = c.unique_name";
$sql.=" LEFT JOIN `{$grade_tbl}` as g ON r.grade=g.unique_name";
$where_parts=[
    "`date`=:date",
    "`is_tmp_date`=0",
    "`world_id`=:world_id",
];
$pre_bind->add(':world_id',$setting->world_id,PDO::PARAM_INT);
if($is_jra_only){ $where_parts[]="`is_jra`=1"; }
if(!$show_disabled){ $where_parts[]="r.`is_enabled`=1"; }
if($race_course_name){
    $where_parts[]="`race_course_name` LIKE :race_course_name";
    $pre_bind->add(':race_course_name',$race_course_name);
}
$sql.=" WHERE ".implode(' AND ',$where_parts);
//$sql.=" ORDER BY `race_course_name` ASC, `race_number` ASC, `race_id` ASC;";
$sql_order_parts=[
    "`is_jra` DESC",
    "`is_nar` DESC",
    "c.sort_number IS NULL, c.sort_number ASC", // コースマスタにある競馬場はソート順適用
    "`race_course_name` ASC", // それ以外を名前順
    "`race_number` ASC",
    "`race_id` ASC",
];
$sql.=" ORDER BY ".implode(',',$sql_order_parts).";";

$stmt = $pdo->prepare($sql);
$pre_bind->add(':date', $date_str);
$pre_bind->bindTo($stmt);
$flag = $stmt->execute();

?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
<style>
td:nth-child(2){ text-align:center;}
td:nth-child(4){ text-align:center;}
table.weekdaybtn td{
    padding:0;
    text-align:center;
    vertical-align:middle;
    min-width:2.5em;
    height:2em;
}
table.weekdaybtn td.sunday{
    background-color: #f9bdb7ff;
}
table.weekdaybtn td.saturday{
    background-color: #c1e2ffff;
}
table.weekdaybtn td a{
    display:block;
    width:100%;
    height:100%;
    line-height: 2em;
    text-decoration: none;
}
table.weekdaybtn td a:link:hover{
    text-decoration: underline;
}
.disabled_row{ background-color: #ccc; }
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php
echo '<a href="'.$page->getRaceYearSearchUrl($year).'">';
echo $setting->getYearSpecialFormat($year);
if($setting->year_view_mode==0){ echo "年"; }
if($setting->year_view_mode==2){ echo "年"; }
echo '</a>';
if($setting->year_view_mode==1){ echo " "; }
echo ($datetime->format('m月d日'))."(".getWeekDayJa($weekdaynum).")";
echo "のレース一覧";
if($race_course_name){ echo "(".mb_convert_kana($race_course_name,'KV').")"; }
?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php
$url_params2=clone $url_params;
$func_weekday_class=function($weekday_num){
    $weekday_num=(int)$weekday_num;
    if($weekday_num===0){ print ' class="sunday"'; }
    if($weekday_num===6){ print ' class="saturday"';}
};
?>
<table class="weekdaybtn">
<tr>
    <td><?php
        $url_params2->set('date',tmpModifyFormat($datetime,'-52 week','Y-m-d'));
        echo (new MkTagA('前年',"?$url_params2"));
    ?></td>
    <td><?php
        $url_params2->set('date',tmpModifyFormat($datetime,'-7 day','Y-m-d'));
        echo (new MkTagA('前週',"?$url_params2"));
    ?></td>
    <td><a>／</a></td>
    <?php
        $url_params2->set('date',tmpModifyFormat($datetime,'-1 day','Y-m-d'));
        $tmp_weekday_num=(int)tmpModifyFormat($datetime,'-1 day','w');
    ?><td<?php $func_weekday_class($tmp_weekday_num);?>>
        <?php echo (new MkTagA(getWeekDayJa($tmp_weekday_num),"?$url_params2")); ?>
    </td>
    <td<?php $func_weekday_class($weekdaynum); ?> style="font-weight:bold;border-width: 2px;"><a><?php echo getWeekDayJa($weekdaynum); ?></a></td>
    <?php
        $url_params2->set('date',tmpModifyFormat($datetime,'+1 day','Y-m-d'));
        $tmp_weekday_num=(int)tmpModifyFormat($datetime,'+1 day','w');
    ?><td<?php $func_weekday_class($tmp_weekday_num);?>>
        <?php echo (new MkTagA(getWeekDayJa($tmp_weekday_num),"?$url_params2")); ?>
    </td>
    <td><a>／</a></td>
    <td style="min-width: 3.5em;" class="saturday"><?php
        $url_params2->set('date',tmpModifyFormat($datetime,'next saturday','Y-m-d'));
        echo (new MkTagA('次土曜',"?$url_params2"));
    ?></td>
    <td><?php
        $url_params2->set('date',tmpModifyFormat($datetime,'+7 day','Y-m-d'));
        echo (new MkTagA('翌週',"?$url_params2"));
    ?></td>
    <td><?php
        $url_params2->set('date',tmpModifyFormat($datetime,'+52 week','Y-m-d'));
        echo (new MkTagA('翌年',"?$url_params2"));
    ?></td>
</tr>
</table>
<?php
$new_race_url_param= (new UrlParams())->set('date',$date);
$a_tag=new MkTagA("同日のレースを登録",APP_ROOT_REL_PATH."race/result/form.php?".$new_race_url_param);
if(Session::is_logined()):
?>[ <?=$a_tag;?> ]<?php
endif;

$table_data=[];
// 1～3着馬を取得
$race123horseGetter=new Race123HorseGetter($pdo);

while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data=array_merge($data,$race123horseGetter($data['race_id']));
    $table_data[]=$data;
}
?><hr>
[ <?php echo (new MkTagA('全て','?'.$url_params->toString([],['is_jra_only','race_course_name']))); ?>｜
<?php echo (new MkTagA('中央競馬',$is_jra_only?'':('?'.$url_params->toString(['is_jra_only'=>true],['race_course_name'])))); ?>
<?php if($race_course_name){echo "｜".$race_course_name;} ?>
 ]
<table class="race_list_date">
<tr><th>場</th><th>R</th><th>距離</th><th>格付</th><th>名称</th><th>1着馬</th><th>2着馬</th><th>3着馬</th></tr><?php
$func_get_horse_link=function($id,$name_ja,$name_en)use($page){
    $a_tag=new MkTagA($name_ja?:$name_en);
    $a_tag->href($page->getHorsePageUrl($id));
    return $a_tag->get();
};
$prev_row_course='';
foreach($table_data as $data){
    if($prev_row_course && $prev_row_course!==$data['race_course_name']){
        echo "<tr><td colspan=\"8\" style=\"height:0.2em;background-color:#EEE;\"></tr>\n";
    }
    $prev_row_course=$data['race_course_name'];
    $class=new Imploader(' ');
    $class->add("race_grade_".$data['grade_css_class_suffix']??'');
    if($data['is_enabled']===0){ $class->add('disabled_row'); }
    echo "<tr class=\"".$class."\">";
    #echo $data['date']."\t";
    echo "<td>".$data['race_course_short_name']??$data['race_course_name']."</td>";
    echo "<td>".($data['race_number']?:"")."</td>";
    echo "<td>{$data['course_type']}{$data['distance']}</td>";
    echo "<td class=\"grade\">".(($data['grade_short_name']??'')?:$data['grade'])."</td>";
    echo "<td>";
    $a_tag=new MkTagA($data['race_name'],$page->getRaceResultUrl($data['race_id']));
    $a_tag->title($data['race_name'].($data['caption']?'：'.$data['caption']:''));
    echo $a_tag;
    echo "</td>";
    echo "<td>";
    if(!empty($data['r1']['horse_id'])){
        echo $func_get_horse_link($data['r1']['horse_id'],$data['r1']['name_ja'],$data['r1']['name_en']);
    }
    echo "</td>";
    echo "<td>";
    if(!empty($data['r2']['horse_id'])){
        echo $func_get_horse_link($data['r2']['horse_id'],$data['r2']['name_ja'],$data['r2']['name_en']);
    }
    echo "</td>";
    echo "<td>";
    if(!empty($data['r3']['horse_id'])){
        echo $func_get_horse_link($data['r3']['horse_id'],$data['r3']['name_ja'],$data['r3']['name_en']);
    }
    echo "</td>";
    echo "</tr>\n";
}
echo "</table>\n";
?><hr>[ <a href="./in_week.php?<?=(new UrlParams(['year'=>$year,'week'=>$year_week]))?>">週単位の一覧</a> ]
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>