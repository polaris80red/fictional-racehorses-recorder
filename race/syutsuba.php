<?php
/**
 * 比較的古め（おそらく）のJRA出馬表風
 */
session_start();
require_once dirname(__DIR__).'/libs/init.php';
defineAppRootRelPath(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="出馬表";
$session=new Session();
// 暫定でログイン＝編集可能
$page->is_editable=Session::is_logined();

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
$race=new Race($pdo, $race_id);
if(!$race->record_exists){
    $page->error_msgs[]="レース情報取得失敗";
    $page->error_msgs[]="入力ID：{$race_id}";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
$week_data=RaceWeek::getById($pdo,$race->week_id);
$week_month=$week_data->month;
$turn=$week_data->umm_month_turn;

?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
<style>
p {font-size:90%;}
td.race_result_column{
    min-width:135px;
}
td:nth-child(1){
    padding:0;
    padding-left:2px;
    padding-right:2px;
}
td:nth-child(-n+2){
    text-align:center;
}
.syutsuba .ib.grade{
    min-width:20px;
    text-align:center;
    padding-left:0.3em;
    padding-right:0.3em;
}
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
<?php
# このレース情報取得
$rr_count=4;
$table_data=get_syutsuba_data($pdo, $race, $rr_count);
?>
<table class="syutsuba">
    <tr>
        <th>枠<br>番</th>
        <th>馬<br>番</th>
        <th></th>
        <th>前走</th>
        <th>前々走</th>
        <th>3走前</th>
        <th>4走前</th>

    </tr>
<?php
foreach ($table_data as $data) {
    // 1件目からない場合　表を描画しない
    if(empty($data['horse_id'])){
        continue;
    }
    // 特別登録のみのデータは表示フラグがなければスキップ
    if(!$show_registration_only && $data['is_registration_only']){
        continue;
    }
?><tr>
<td>
<?php if(!empty($data['frame_number'])): ?>
<span style="border:solid 1px #999; padding-left:0.4em; padding-right:0.4em;" class="<?php echo "waku_".$data['frame_number']; ?>"> <?php echo $data['frame_number']; ?></span>
<?php endif; ?>
</td>
<td><?php echo empty($data['horse_number'])?"":$data['horse_number']; ?></td>
<td style="min-width:160px;">
<?php
    $training_country='';
    if(!empty($data['training_country'])){
        $training_country=$data['training_country'];
    }else{
        $training_country=$data['horse_training_country'];
    }
    if(($data['is_jra']==1 || $data['is_nar']==1)&& $training_country!='JPN'){
        echo "[外] ";
    }
    if($data['is_jra']==1&& $data['is_affliationed_nar']==1){
        echo "[地] ";
    }
    echo '<a class="horse_name" href="'.$page->to_app_root_path.'horse/?horse_id='.$data['horse_id'].'">';
    echo ($data['name_ja']?:$data['name_en']);
    echo "</a>";
    if($data['is_jra']==0 && $data['is_nar']==0){
        echo "<span style=\"\"> (".($data['training_country']?:$data['horse_training_country']).")</span> ";
    }
    echo "<br>";
    echo "□□□□";
    if(!empty($data['tc'])){
        echo "（{$data['tc']}）";
    }else{
        echo "（{$data['horse_tc']}）";
    }
?><br>
父：<?php echo $data['sire_name']?:"□□□□□□"; ?><br>
母：<?php echo $data['mare_name']?:"□□□□□□"; ?><br>
(母の父：<?php echo $data['bms_name']?:"□□□□□□"; ?>)<br>
<?php
echo $data['sex_str'].$data['age']."歳";
if($data['color']){ echo "/".$data['color'];}
if($data['handicap']){ echo " ".$data['handicap']."kg";}

?>
</td>
<?php
$i=1;

// 現在のレースに未登録の空きが設定あれば空セル追加
if(!empty($data['non_registered_prev_race_number']) && $data['non_registered_prev_race_number']>0){
    for($j=0;$j<$data['non_registered_prev_race_number'];$j++){
        if($i>$rr_count){ break; }
        $i++;
        if($page->is_editable){
            $url ="{$page->to_app_root_path}race/horse_result/form.php?";
            $url.="horse_id={$data['horse_id']}";
            $url.="&next_race_id={$data['race_id']}";
            print "<td class=\"race_result_column\"><a href=\"{$url}\">……</a></td>\n";
        }else{
            print "<td class=\"race_result_column\">……</td>\n";
        }
    }
}
foreach($data['horse_results'] as $prev_race){
    $r=null;
    $result_number=0;
    if(!empty($prev_race['race_name'])){
        $r=(object)$prev_race;
        $result_number=$r->result_number;
    }
    if($i>$rr_count){ break; }
    $i++;
?><td class="race_result_column <?php printResultClass($result_number); ?> <?php echo "race_grade_".($r->grade_css_class_suffix??''); ?>"><?php
    if($r!=null){
        echo "<div>";
        $date_line='';
        if($setting->syutsuba_year==1){ $date_line.=$setting->getYearSpecialFormat($r->year); }
        if($setting->syutsuba_date==='mx'||$setting->syutsuba_date==='md'||$setting->syutsuba_date==='m'){
            if($setting->syutsuba_year==1){
                if($setting->year_month_separator==='/'){
                    $date_line.='.';
                }else{
                    $date_line.=' ';
                }
            }
            $date_line.=str_pad($r->month,2,'0',STR_PAD_LEFT);
            if($setting->syutsuba_date==='m' && ($setting->syutsuba_year==0 || $setting->year_month_separator===' ')){
                    $date_line.='月';
            }
        }
        if($setting->syutsuba_year==0 && $setting->syutsuba_date=='none'){ $date_line='&nbsp;'; }
        if($setting->syutsuba_date==='mx'){ $date_line.='.xx'; }
        if($setting->syutsuba_date==='md'){
            $date_line.='.';
            if($r->date!=null && $r->is_tmp_date==0){
                $date_obj=new DateTime($r->date);
                $date_line.=$date_obj->format('d');
            }else{
                $date_line.='xx';
            }
        }
        echo "<span>{$date_line}</span>";
        $course_name = $r->race_course_name;
        if(!empty($r->race_course_short_name)){ $course_name=$r->race_course_short_name; }
        if(!empty($r->race_course_short_name_m)){ $course_name=$r->race_course_short_name_m; }
        echo "<span style=\"display:inline-block;float:right;\">"." {$course_name}"."</span>";
        echo "</div>\n";

        echo "<div>";
        $url=$page->getRaceResultUrl($r->race_id);
        echo "<span style=\"\"><a class=\"race_name\" href=\"{$url}\">";
        echo $r->race_short_name==''?$r->race_name:$r->race_short_name;
        echo "</a></span>";
        echo "<span style=\"display:inline-block;float:right;\" class=\"ib grade\">".($r->grade_short_name??$r->grade)."</span>"."<br>\n";
        echo "</div>\n";

        echo "<span class=\"result_number\" style=\"\">";
        echo $r->result_text?($r->special_result_short_name_2?:$r->result_text):($r->result_number."着");
        echo "</span>";
        echo "<br>\n";
        echo $r->course_type.$r->distance."<br>\n";

        if(!empty($r->winner_or_runner_up['horse_id'])){
            echo '<a href="'.$page->to_app_root_path.'horse/?horse_id='.$r->winner_or_runner_up['horse_id'].'">';
            echo $r->winner_or_runner_up['name_ja']?:$r->winner_or_runner_up['name_en'];
            echo '</a>';
        }else{
            echo '&nbsp;';
        }
        echo "<br>\n";
        #echo "<hr><pre>".print_r($data['horse_results'][$i],true)."</pre>";
    }
    ?></td><?php
    if(!empty($r->non_registered_prev_race_number) && $r->non_registered_prev_race_number>0){
        for($j=0;$j<$r->non_registered_prev_race_number;$j++){
            if($i>$rr_count){ break; }
            $i++;
            if($page->is_editable){
                $url ="{$page->to_app_root_path}race/horse_result/form.php?";
                $url.="horse_id={$data['horse_id']}";
                $url.="&next_race_id={$r->race_id}";
                print "<td class=\"race_result_column\"><a href=\"{$url}\">……</a></td>\n";
            }else{
                print "<td class=\"race_result_column\">……</td>\n";
            }
        }
    }
}
?>
</tr><?php
}
?></table>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>
