<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting(); 
$page->setSetting($setting);
$page->title="最近アクセスしたレースの一覧";
$page->ForceNoindex();
$session=new Session();
// 暫定でログイン＝編集可能
$page->is_editable=Session::is_logined();
$pdo= getPDO();

$show_column_umm_turn=false;
$show_column_date=true;
if($setting->horse_record_date==='umm'){
    $show_column_umm_turn=true;
    $show_column_date=false;
}


$params=new UrlParams();
$show_disabled=$params->setFromGet('show_disabled',FILTER_VALIDATE_BOOL)->get();
$date_sort= $params->setFromGet('date_sort',FILTER_VALIDATE_BOOL)->get();

$world_id= $setting->world_id;
$race_history = (new RaceAccessHistory())->toArray();

# レース情報取得
$horse_tbl=Horse::TABLE;
$r_results_tbl=RaceResults::TABLE;
$week_tbl=RaceWeek::TABLE;
$course_mst_tbl=RaceCourse::TABLE;
$binder=new StatementBinder();

$where_parts=[];
if(count($race_history)>0){
    $where_in_parts=[];
    foreach($race_history as $key => $race_id){
        if($race_id===''){ continue; }
        $in_data=":race_id_{$key}";
        $where_in_parts[]=$in_data;
        $binder->add($in_data, $race_id);
    }
    $where_parts[]="`race_id` IN (".implode(',',$where_in_parts).")";
}

if($world_id>0){
    $where_parts[]="`world_id`=:world_id";
    $binder->add(':world_id', $world_id);
}
if(!$show_disabled){ $where_parts[]="r.`is_enabled`=1"; }
$sql_where=" WHERE ".implode(' AND ',$where_parts);
$sql_order_parts=[
    "`year` ASC",
    "IFNULL(w.`month`,r.`month`) ASC",
    "w.`sort_number` ASC",
    "`date` ASC",
    "`race_course_name` ASC, `race_number` ASC",
    "`race_id` ASC",
];
$sql_order_by=implode(',',$sql_order_parts);
$grade_tbl=RaceGrade::TABLE;
$sql=<<<END
SELECT
    r.*
    ,w.month AS 'w_month'
    ,w.umm_month_turn
    ,g.short_name as grade_short_name
    ,g.css_class_suffix as grade_css_class_suffix
    ,c.short_name as race_course_mst_short_name
FROM `{$r_results_tbl}` AS r
LEFT JOIN `{$week_tbl}` as w ON r.week_id=w.id
LEFT JOIN `{$grade_tbl}` as g ON r.grade LIKE g.unique_name
LEFT JOIN `{$course_mst_tbl}` as c ON r.race_course_name LIKE c.unique_name AND c.is_enabled=1
{$sql_where}
ORDER BY
{$sql_order_by};
END;

try{
    $stmt=null;
    if(count($race_history)>0){
        $stmt = $pdo->prepare($sql);
        $binder->bindTo($stmt);
        $flag = $stmt->execute();
    }
} catch(Exception $e){
    Elog::error("access_history:",['e'=>$e, 'stmt'=>$stmt, 'sql'=>$sql]);
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
// 1～3着馬を取得
$race123horseGetter=new Race123HorseGetter($pdo);
// 事前加工用
$table_data=[];
if(!is_null($stmt)){
    if($date_sort){
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $table_data[]=$data;
        }
    }else{
        $race_id_to_sort=array_flip($race_history);
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $table_data[(int)$race_id_to_sort[$data['race_id']]]=$data;
        }
        ksort($table_data);
    }

}
?>
<?php //$search->printForm($page,true,null); ?>
<!--<hr>-->
<!--<?php print "<a href=\"#foot\" title=\"最下部検索フォームに移動\" style=\"text-decoration:none;\">▽検索結果</a>｜"; ?>
<hr>
-->
<?php
$link=new MkTagA('アクセス新着順');
$link->title("最近アクセスしたレースを新しいものから");
if($date_sort){
    $link->href("?".$params->toString(['date_sort'=>'']));
}
?>
[ <?php print $link; ?> ]
<?php
$link=new MkTagA('開催順');
$link->title("開催日程の昇順");
if(!$date_sort){
    $link->href("?".$params->toString(['date_sort'=>true]));
}
?>
[ <?php print $link; ?> ]
<table class="race_list">
<tr>
<?php if($show_column_umm_turn): ?><th>時期</th><?php endif; ?>
<?php if($show_column_date): ?><th>日付</th><?php endif; ?>
<th>場</th><th style="min-width:3.5em;">距離</th><th>格付</th><th>名称</th><th>1着馬</th><th>2着馬</th><th>3着馬</th>
</tr><?php
foreach($table_data as $data){
    $data=array_merge($data,$race123horseGetter($data['race_id']));
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
        null // 計算基準年がある場合は年齢
        ]
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
        }else{
            $date_url=$page->getTurnRaceListUrl(
                $data['year'],$month,null,['week'=>$data['week_id']]);
        }
        echo "<td>".(new MkTagA($date_str,$date_url))."</td>";
    }
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
    if(!empty($data['r1']['horse_id'])){
        $a_tag=(new MkTagA($data['r1']['name_ja']?:$data['r1']['name_en']));
        $a_tag->href($page->getHorsePageUrl($data['r1']['horse_id']));
        echo $a_tag;
    }
    echo "</td>";
    echo "<td>";
    if(!empty($data['r2']['horse_id'])){
        $a_tag=(new MkTagA($data['r2']['name_ja']?:$data['r2']['name_en']));
        $a_tag->href($page->getHorsePageUrl($data['r2']['horse_id']));
        echo $a_tag;
    }
    echo "</td>";
    echo "<td>";
    if(!empty($data['r3']['horse_id'])){
        $a_tag=(new MkTagA($data['r3']['name_ja']?:$data['r3']['name_en']));
        $a_tag->href($page->getHorsePageUrl($data['r3']['horse_id']));
        echo $a_tag;
    }
    echo "</td>";
    echo "</tr>\n";
}
echo "</table>\n";
?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
<?php $page->printScriptLink('js/race_search_form.js'); ?>
</body>
</html>