<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース一覧";
$pdo= getPDO();

$year=(string)filter_input(INPUT_GET,'year');
if($year===''){exit;}
$year=(int)$year;

$month=(int)filter_input(INPUT_GET,'month',FILTER_VALIDATE_INT);
$week_id=(int)filter_input(INPUT_GET,'week',FILTER_VALIDATE_INT);
$umm_month_turn=(int)filter_input(INPUT_GET,'turn',FILTER_VALIDATE_INT);
if($week_id){
    $month=0;
    $umm_month_turn=0;
}else if($umm_month_turn){
    $week_id=0;
}
$show_disabled=filter_input(INPUT_GET,'show_disabled',FILTER_VALIDATE_BOOL);
$is_jra_only=filter_input(INPUT_GET,'is_jra_only',FILTER_VALIDATE_BOOL);
$is_grade_only=filter_input(INPUT_GET,'is_grade_only',FILTER_VALIDATE_BOOL);

$url_params=new UrlParams();
$url_params->set('year',$year);
if($month){ $url_params->set('month',$month);}
if($week_id){
    $url_params->set('week',$week_id);
    $week=RaceWeek::getById($pdo,$week_id);
    $month=$week['month'];
}
if($umm_month_turn){ $url_params->set('turn',$umm_month_turn);}
if($show_disabled){ $url_params->set('show_disabled',true);}
if($is_jra_only){ $url_params->set('is_jra_only',true);}
if($is_grade_only){ $url_params->set('is_grade_only',true);}

$page_title_text1="";
$page_title_text1.=  $setting->getYearSpecialFormat($year);
if($setting->year_view_mode==0){ $page_title_text1.= "年"; }
if($setting->year_view_mode==2){ $page_title_text1.= "年"; }
$page_title_text2="";
if($setting->year_view_mode==1){ $page_title_text2.= " "; }
if($week_id>0){
    $page_title_text2.= "第".str_pad($week_id,2,'0',STR_PAD_LEFT)."週";
    $page_title_text2.= "({$month}月)";
}else{
    $page_title_text2.= str_pad($month,2,'0',STR_PAD_LEFT)."月";
    if($umm_month_turn!=0){
        $page_title_text2.= (($umm_month_turn===2)?'後半':'前半');
    }
}
$page_title_text2.= 'のレース一覧';
$page->title=$page_title_text1.$page_title_text2;

# レース情報取得
$pre_bind=new StatementBinder();
$horse_tbl=Horse::TABLE;
$r_results_tbl=RaceResults::QuotedTable();
$race_week_tbl=RaceWeek::QuotedTable();
$race_course_tbl = RaceCourse::QuotedTable();
$grade_tbl=RaceGrade::TABLE;
$sql=<<<END
SELECT
r.*
,w.month
,w.umm_month_turn
,g.short_name as grade_short_name
,g.css_class_suffix as grade_css_class_suffix
FROM {$r_results_tbl} AS r
LEFT JOIN {$race_week_tbl} AS w ON r.`week_id`= w.id
LEFT JOIN {$race_course_tbl} AS c ON r.race_course_name = c.name
LEFT JOIN `{$grade_tbl}` as g ON r.grade=g.race_results_key
END;
$sql_where_and_parts=["`year`=:year"];
if($week_id>0){
    $sql_where_and_parts[]="r.`week_id`=:week_id";
    $pre_bind->add(':week_id', $week_id, PDO::PARAM_INT);
}else if($month>0){
    $sql_where_and_parts[]="w.`month`=:month";
    $pre_bind->add(':month', $month, PDO::PARAM_INT);
}
$pre_bind->add(':year', $year, PDO::PARAM_INT);
if($umm_month_turn){
    $sql_where_and_parts[]="w.`umm_month_turn`=:turn";
    $pre_bind->add(':turn', $umm_month_turn, PDO::PARAM_INT);
}
if($is_grade_only){
    $sql_where_and_parts[]="(`grade` LIKE 'G_' OR `grade` LIKE 'Jpn_' OR `grade` LIKE '重賞')";
}
if($is_jra_only){
    $sql_where_and_parts[]="r.is_jra=1";
}
if(!$show_disabled){ $sql_where_and_parts[]="r.`is_enabled`=1"; }
$sql.=" WHERE ".implode(' AND ',$sql_where_and_parts);
$sql_order_parts=[
    "w.`umm_month_turn` ASC",
    "w.`sort_number` DESC",
    "`date` ASC",
    "`is_jra` DESC",
    "`is_nar` DESC",
    "c.sort_number IS NULL, c.sort_number ASC", // コースマスタにある競馬場はソート順適用
    "`race_course_name` ASC", // それ以外を名前順
    "`race_number` ASC",
    "`race_id` ASC",
];
$sql.=" ORDER BY ".implode(',',$sql_order_parts).";";
$stmt = $pdo->prepare($sql);
//$stmt->bindValue(':turn', $umm_month_turn, PDO::PARAM_INT);
$pre_bind->bindTo($stmt);
$flag = $stmt->execute();
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?></title>
    <meta charset="UTF-8">
    <?php $page->printBaseStylesheetLinks(); ?>
<style>
td:nth-child(3){ text-align:center;}
td:nth-child(5){ text-align:center;}
table.weekdaybtn td{
    padding:0;
    text-align:center;
    vertical-align:middle;
    width:2.5em;
    height:2em;
}
table.weekdaybtn td a{
    display:block;
    width:100%;
    height:100%;
}
table.weekdaybtn td a:hover{
    background-color:#CCF;
}
.disabled_row{ background-color: #ccc; }
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php
(new MkTagA($page_title_text1,$page->getRaceYearSearchUrl($year)))->print();
echo $page_title_text2;
?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php
$prev_year=$year;
$next_year=$year;

$prev_turn=$next_turn=$umm_month_turn===2?1:2;
?>
<?php if($umm_month_turn!=0): ?>
<?php
$prev_month= ($umm_month_turn===1||$umm_month_turn==0)?$month-1:$month;
$next_month= ($umm_month_turn===2||$umm_month_turn==0)?$month+1:$month;
if($prev_month<=0){
    $prev_year=$year-1;
    $prev_month=12;
}
if($next_month>=13){
    $next_year=$year+1;
    $next_month=1;
}
?>
<?php
    $url_param_str=$url_params->toString(['year'=>$prev_year,'month'=>$prev_month,'turn'=>$prev_turn]);
    echo (new MkTagA('前ターン',"?{$url_param_str}"));
?>｜<?php
    $url_param_str=$url_params->toString(['year'=>$next_year,'month'=>$next_month,'turn'=>$next_turn]);
    echo (new MkTagA('次ターン',"?{$url_param_str}"));
?>
<?php elseif($week_id==0): ?>
<?php
    $url_param_str=$url_params->toString(['year'=>$prev_year,'month'=>$prev_month]);
    echo (new MkTagA('前月',"?{$url_param_str}"));
?>｜<?php
    $url_param_str=$url_params->toString(['year'=>$next_year,'month'=>$next_month]);
    echo (new MkTagA('次月',"?{$url_param_str}"));
?>
<?php elseif($week_id!=0): ?>
<?php
    $prev_week=$week_id-1;
    if($prev_week<=0){
        $prev_year=$year-1;
        $prev_week=52;
    }
    $next_week=$week_id+1;
    if($next_week>52){
        $next_week=1;
        $next_year=$year+1;
    }
    $url_param_str=$url_params->toString(['year'=>$prev_year,'week'=>$prev_week]);
    echo (new MkTagA('前週',"?{$url_param_str}"));
?>｜<?php
    $url_param_str=$url_params->toString(['year'=>$next_year,'week'=>$next_week]);
    echo (new MkTagA('次週',"?{$url_param_str}"));
?>
<?php endif; ?>
<?php
$table_data=[];
// 1～3着馬を取得
$race123horseGetter=new Race123HorseGetter($pdo);

while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data=array_merge($data,$race123horseGetter($data['race_id']));
    $table_data[]=$data;
}
?><hr>
[ <?php echo (new MkTagA('全て','?'.$url_params->toString([],['is_jra_only','is_grade_only']))); ?>
｜<?php echo (new MkTagA('中央のみ',$is_jra_only&&!$is_grade_only?'':('?'.$url_params->toString(['is_jra_only'=>true],['is_grade_only'])))); ?>
｜<?php echo (new MkTagA('中央重賞のみ',$is_jra_only&&$is_grade_only?'':('?'.$url_params->toString(['is_jra_only'=>true,'is_grade_only'=>true])))); ?>
｜<?php echo (new MkTagA('重賞のみ',$is_grade_only&&!$is_jra_only?'':('?'.$url_params->toString(['is_grade_only'=>true],['is_jra_only'])))); ?>
 ]
<table class="race_list_date">
<tr><th>日付</th><th>場</th><th>R</th><th>距離</th><th>格付</th><th>名称</th><th>1着馬</th><th>2着馬</th><th>3着馬</th></tr><?php
$func_get_horse_link=function($id,$name_ja,$name_en)use($page){
    $a_tag=new MkTagA($name_ja?:$name_en);
    $a_tag->href($page->getHorsePageUrl($id));
    return $a_tag->get();
};
$prev_date='';
$prev_row_course='';
$prev_turn=0;
$prev_week_id=0;
foreach($table_data as $data){
    if(false && $prev_row_course && $prev_row_course!==$data['race_course_name']){
        echo "<tr><td colspan=\"9\" style=\"height:0.2em;background-color:#EEE;\"></tr>\n";
    }
    $prev_row_course=$data['race_course_name'];
    if( $week_id===0 && $umm_month_turn==0 && $prev_turn!==$data['umm_month_turn']){
        echo "<tr><td colspan=\"9\" style=\"height:0.2em;background-color:#EEE;\">";
        switch($data['umm_month_turn']){
            case 1:
                echo "前半";
                break;
            case 2:
                echo "後半";
                break;
        }
        echo "</td></tr>\n";
    }else if($prev_week_id!==0 && $prev_week_id != $data['week_id']){
        echo "<tr><td colspan=\"9\" style=\"height:0.2em;background-color:#EEE;\">";
        echo "</td></tr>\n";
    }
    $prev_date=$data['date'];
    $prev_week_id=$data['week_id'];
    $prev_turn=$data['umm_month_turn'];
    $class=new Imploader(' ');
    $class->add("race_grade_".$data['grade_css_class_suffix']??'');
    if($data['is_enabled']===0){ $class->add('disabled_row'); }
    echo "<tr class=\"".$class."\">";
    #echo $data['date']."\t";
    echo "<td>";
    if(!is_null($data['date'])){
        echo (new DateTime($data['date']))->format('m/d');
    }
    echo "</td>";
    echo "<td>{$data['race_course_name']}</td>";
    echo "<td>".($data['race_number']?:"")."</td>";
    echo "<td>{$data['course_type']}{$data['distance']}</td>";
    echo "<td class=\"grade\">".($data['grade_short_name']??$data['grade'])."</td>";
    echo "<td>";
    echo '<a href="'.$page->getRaceResultUrl($data['race_id']).'" title="'.$data['race_name'].($data['caption']?'：'.$data['caption']:'').'">';
    echo $data['race_name'];
    echo "</a>\t";
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
?><hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>
<?php
function printTable(){

}