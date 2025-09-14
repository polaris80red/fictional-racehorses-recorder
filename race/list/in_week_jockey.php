<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
InAppUrl::init(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース一覧";
$pdo= getPDO();

$year=(string)filter_input(INPUT_GET,'year');
if($year===''){
    $page->error_msgs[]="年度未指定";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
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
$jockey_name=filter_input(INPUT_GET,'jockey');
if(!$jockey_name){
    $page->error_msgs[]="騎手名未指定";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
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
    $month=$week->month;
}
if($jockey_name){ $url_params->set('jockey',$jockey_name);}
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
}else if($month>0){
    $page_title_text2.= str_pad($month,2,'0',STR_PAD_LEFT)."月";
    if($umm_month_turn!=0){
        $page_title_text2.= (($umm_month_turn===2)?'後半':'前半');
    }
}
$page_title_text2.= "の '{$jockey_name}' 騎乗レース一覧";
$page->title=$page_title_text1.$page_title_text2;

# レース情報取得
$race_list_getter=new RaceListGetter($pdo);
$pre_bind=new StatementBinder();
$sql_where_and_parts=[
    "`world_id`=:world_id",
    "`year`=:year",
];
$pre_bind->add(':world_id',$setting->world_id,PDO::PARAM_INT);

$race_list_getter->addJoin('RIGHT JOIN `'.RaceResults::TABLE.'` AS `rr` ON r.race_id=rr.race_id');
$sql_where_and_parts[]="rr.`jockey_name`=:jockey";
$pre_bind->add(':jockey', $jockey_name, PDO::PARAM_STR);

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
$race_list_getter->addWhereParts($sql_where_and_parts);
$race_list_getter->addOrderParts([
    "w.`umm_month_turn` ASC",
    "w.`sort_number` ASC",
    "r.`date` ASC",
    "r.`is_jra` DESC",
    "r.`is_nar` DESC",
    "c.sort_number IS NULL, c.sort_number ASC", // コースマスタにある競馬場はソート順適用
    "r.`race_course_name` ASC", // それ以外を名前順
    "r.`race_number` ASC",
    "r.`race_id` ASC",
]);
$stmt = $race_list_getter->getPDOStatement();
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
$prev_month=$month-1;
$next_month=$month+1;
if($umm_month_turn!=0){
    $prev_month= ($umm_month_turn===1||$umm_month_turn==0)?$month-1:$month;
    $next_month= ($umm_month_turn===2||$umm_month_turn==0)?$month+1:$month;
}
if($prev_month<=0){
    $prev_year=$year-1;
    $prev_month=12;
}
if($next_month>=13){
    $next_year=$year+1;
    $next_month=1;
}
?>
<?php if($umm_month_turn!=0): ?>
<?php
    $url_param_str=$url_params->toString(['year'=>$prev_year,'month'=>$prev_month,'turn'=>$prev_turn]);
    echo (new MkTagA('前ターン',"?{$url_param_str}"));
?>｜<?php
    $url_param_str=$url_params->toString(['year'=>$next_year,'month'=>$next_month,'turn'=>$next_turn]);
    echo (new MkTagA('次ターン',"?{$url_param_str}"));
?>
<?php elseif($week_id==0 && $month>0): ?>
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

$search_results=new RaceSearchResults($stmt);
$table_rows=$search_results->getAll();
?><hr>
[ <?php echo (new MkTagA('全て','?'.$url_params->toString([],['is_jra_only','is_grade_only']))); ?>
｜<?php echo (new MkTagA('中央のみ',$is_jra_only&&!$is_grade_only?'':('?'.$url_params->toString(['is_jra_only'=>true],['is_grade_only'])))); ?>
｜<?php echo (new MkTagA('中央重賞のみ',$is_jra_only&&$is_grade_only?'':('?'.$url_params->toString(['is_jra_only'=>true,'is_grade_only'=>true])))); ?>
｜<?php echo (new MkTagA('重賞のみ',$is_grade_only&&!$is_jra_only?'':('?'.$url_params->toString(['is_grade_only'=>true],['is_jra_only'])))); ?>
 ]
<table class="race_list_date">
<tr>
    <th>日付</th>
    <th>場</th>
    <th>R</th>
    <th>距離</th>
    <th>格付</th>
    <th>名称</th>
    <th>騎乗馬</th>
    <th>着順</th>
</tr>
<?php
$func_get_horse_link=function($id,$name_ja,$name_en)use($page){
    $a_tag=new MkTagA($name_ja?:$name_en);
    $a_tag->href($page->getHorsePageUrl($id));
    return $a_tag->get();
};
$prev_date='';
$prev_turn=0;
$prev_week_id=0;

$resultGetter=new class($pdo,$jockey_name){
    private $stmt;
    private $race_id;
    public function __construct(PDO $pdo,string $jockey_name)
    {
        $sql="SELECT * FROM `".RaceResults::TABLE."` WHERE `race_id`=:race_id AND `jockey_name`=:jockey";
        $this->stmt=$pdo->prepare($sql);
        $this->stmt->bindValue(':jockey',$jockey_name,PDO::PARAM_STR);
        $this->stmt->bindParam(':race_id',$this->race_id,PDO::PARAM_STR);
    }
    public function get($race_id){
        $this->race_id=$race_id;
        $this->stmt->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }
};
?>
<?php foreach($table_rows as $row): ?>
    <?php
        $race=$row->raceRow;
        $raceWeek=$row->weekRow;
        $raceGrade=$row->gradeRow;
        $raceCourse=$row->courseRow;
        $raceResult=$resultGetter->get($race->race_id);
        $horse=new Horse();
        $horse->setDataById($pdo,$raceResult->horse_id);
    ?>
    <?php if($week_id===0 && $umm_month_turn==0 && $month>0 && $prev_turn!==$raceWeek->umm_month_turn): ?>
        <?php
        $style="height:0.2em;background-color:#EEE;";
        switch($raceWeek->umm_month_turn){
            case 1:
                $text='前半';
                break;
            case 2:
                $text='後半';
                break;
        }
        ?>
        <tr style="<?=$style?>"><td colspan="9"><?=$text?></td></tr>
    <?php endif; ?>
    <?php if($prev_week_id != $race->week_id): ?>
        <?php
            $new_race_url_param= new UrlParams();
            $new_race_url_param->set('year',$year)->set('week_id',$race->week_id);
            $week_str="第{$race->week_id}週（{$raceWeek->name}）";
            $style="background-color:#EEE;text-align:left;";
        ?>
        <tr><td colspan="9" style="<?=$style?>"><?=h($week_str)?></td></tr>
    <?php endif; ?>
    <?php
        $prev_date=$race->date;
        $prev_week_id=$race->week_id;
        $prev_turn=$raceWeek->umm_month_turn;
        $class=new Imploader(' ');
        $class->add($raceGrade->css_class??'');
        if($race->is_enabled===0){ $class->add('disabled_row'); }
    ?>
    <tr class="<?=$class?>">
        <td><?=is_null($race->date)?'':(new DateTime($race->date))->format('m/d')?></td>
        <td><?=h($raceCourse->short_name??$race->race_course_name)?></td>
        <td><?=h($race->race_number?:"")?></td>
        <td><?=h($race->course_type.$race->distance)?></td>
        <td class="grade"><?=h(($raceGrade->short_name??'')?:$race->grade)?></td>
        <?php
            $a_tag=new MkTagA($race->race_name,$page->getRaceResultUrl($race->race_id));
            $a_tag->title($race->race_name.($race->caption?'：'.$race->caption:''));
        ?>
        <td><?=$a_tag?></td>
        <?php
            $horse_tag=new MkTagA($horse->name_ja?:$horse->name_en,InAppUrl::to('horse/',['horse_id'=>$horse->horse_id]));
        ?>
        <td><?=$horse_tag?></td>
        <td><?=h($raceResult->result_number)?>着</td>
    </tr>
<?php endforeach; ?>
</table>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>
<?php
function printTable(){

}