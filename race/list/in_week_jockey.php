<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
InAppUrl::init(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース一覧";
$page->ForceNoindex();
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
$jockey=Jockey::getByUniqueName($pdo,$jockey_name);
$jockey_view_name=$jockey_name;
if($jockey!==false){
    // レコードがある場合
    if($jockey->is_anonymous==1 && !Session::is_logined()){
        $jockey_view_name="□□□□";
    }else{
        $jockey_view_name=$jockey->short_name_10?:$jockey_name;
    }
}

$show_disabled=filter_input(INPUT_GET,'show_disabled',FILTER_VALIDATE_BOOL);
$is_jra_only=filter_input(INPUT_GET,'is_jra_only',FILTER_VALIDATE_BOOL);
$is_grade_only=filter_input(INPUT_GET,'is_grade_only',FILTER_VALIDATE_BOOL);

$show_result=(function(){
    $input=(string)filter_input(INPUT_GET,'show_result');
    if($input!==''){
        return filter_var($input,FILTER_VALIDATE_BOOL);
    }
    return true;
})();

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
if($show_result===false){ $url_params->set('show_result',false);}

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
$page_title_text2.= "の '{$jockey_view_name}' 騎乗".($show_result?'結果':'予定')."一覧";
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
td.col_result_number{ text-align: right; }
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
<div><span style="font-weight: bold;"><?=$jockey_view_name?></span> 騎乗<?=$show_result?'結果':'予定'?></div>
[ <?php echo (new MkTagA('全て',!$is_jra_only&&!$is_grade_only?'':('?'.$url_params->toString([],['is_jra_only','is_grade_only'])))); ?>
｜<?php echo (new MkTagA('中央のみ',$is_jra_only&&!$is_grade_only?'':('?'.$url_params->toString(['is_jra_only'=>true],['is_grade_only'])))); ?>
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
    <?php if($show_result):?><th>着順</th><?php endif;?>
    <th>厩舎</th>
</tr>
<?php
$full_row_span=$show_result?9:8;
$empty_row='<tr>'.'<td colspan="'.$full_row_span.'">'.'</tr>';
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
$prev_date='';
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
            $prev_date='';
        ?>
        <tr style="<?=$style?>"><td colspan="<?=$full_row_span?>"><?=$text?></td></tr>
    <?php endif; ?>
    <?php if($prev_week_id != $race->week_id): ?>
        <?php
            $new_race_url_param= new UrlParams();
            $new_race_url_param->set('year',$year)->set('week_id',$race->week_id);
            $week_str="第{$race->week_id}週（{$raceWeek->name}）";
            $style="background-color:#EEE;text-align:left;";
            $prev_date='';
        ?>
        <tr><td colspan="<?=$full_row_span?>" style="<?=$style?>"><?=h($week_str)?></td></tr>
    <?php endif; ?>
    <?php if($prev_date!='' && strval($race->date?:null)!==$prev_date):?>
        <?=$empty_row?>
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
            $url=$show_result?$page->getRaceResultUrl($race->race_id):InAppUrl::to('race/syutsuba.php',['race_id'=>$race->race_id]);
            $a_tag=new MkTagA($race->race_name,$url);
            $a_tag->title($race->race_name.($race->caption?'：'.$race->caption:''));
        ?>
        <td><?=$a_tag?></td>
        <?php
            $horse_tag=new MkTagA($horse->name_ja?:$horse->name_en,InAppUrl::to('horse/',['horse_id'=>$horse->horse_id]));
        ?>
        <td><?=$horse_tag?></td>
        <?php if($show_result):?>
            <td class="col_result_number"><?=h($raceResult->result_text?:($raceResult->result_number?$raceResult->result_number.'着':''))?></td>
        <?php endif;?>
        <?php
            $trainer_un=$raceResult->trainer_name?:$horse->trainer_name;
            $trainer_view_name=$trainer_un;
            $trainer=Trainer::getByUniqueName($pdo,$trainer_un);
            if($trainer){
                if($trainer->is_anonymous && !Session::is_logined()){
                    $trainer_view_name='□□□□';
                }else{
                    $trainer_view_name=$trainer->short_name_10?:$trainer_un;
                }
            }
        ?>
        <td><?=h($trainer_view_name)?></td>
    </tr>
<?php endforeach; ?>
</table>
<?php
$params=['year'=>$year,'is_jra_only'=>$is_jra_only];
if($week_id>0){
    $params['week']=$week_id;
    $text='今週のレース結果一覧';
}else if($month>0){
    $params['month']=$month;
    $text='今月のレース結果一覧';
    if($umm_month_turn>0){
        $params['turn']=$umm_month_turn;
        $text='このターンのレース結果一覧';
    }
}
?>
[ <?=new MkTagA($text,InAppUrl::to('race/list/in_week.php',$params))?> ]
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