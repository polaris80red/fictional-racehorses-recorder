<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
InAppUrl::init(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="競走馬レース情報一括編集";
$page->ForceNoindex();
$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }
$pdo= getPDO();

$page->error_return_url=InAppUrl::to("horse/search");
$page->error_return_link_text="競走馬検索に戻る";
if(empty($_GET['horse_id'])){
    $page->error_msgs[]="競走馬ID未指定";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
$horse_id=filter_input(INPUT_GET,'horse_id');

$page_urlparam=new UrlParams([
    'horse_id'=>$horse_id,
]);
# 馬情報取得
$horse=new Horse();
$horse->setDataById($pdo, $horse_id);
if(!$horse->record_exists){
    $page->error_msgs[]="競走馬情報取得失敗";
    $page->error_msgs[]="入力ID：{$horse_id}";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
$race_history=new HorseRaceHistory($pdo,$horse_id);
$race_history->setDateOrder('ASC');
$race_history->getData();

$sex_str=sex2String($horse->sex);
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
.disabled_row{ background-color: #dddddd; }

table.horse_history { margin-top: 8px; }
td.race_course_name { text-align: center; }
td.grade{ text-align:center; }
td.frame_number{ text-align:center; }
td.horse_number{ text-align:center; }
td.favourite{ text-align:right; }
td.result_number{ text-align:right; }
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php $page->printTitle(); ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php include (new TemplateImporter('horse/horse_page-header.inc.php'));?>
<span><?php
print_h($race_history->race_count_1."-");
print_h($race_history->race_count_2."-");
print_h($race_history->race_count_3."-");
?><span style="<?=$race_history->has_unregistered_race_results?'color:#999999;':'';?>"><?php
print_h($race_history->race_count_4+$race_history->race_count_5+$race_history->race_count_n." / ");
print_h($race_history->race_count_all."戦");
?></span></span>
<?php
$mode_umm=false;
switch($setting->age_view_mode){
    case Setting::AGE_VIEW_MODE_UMAMUSUME:
    case Setting::AGE_VIEW_MODE_UMAMUSUME_S:
        $mode_umm = true;
    break;
}
?>
<hr>
<ul>
    <li>セン馬への変更は、それ以降のレースにも適用します。</li>
    <li>地方区分をカク[地]・マル(地)にする際は、次のカク[地]・マル(地)までの地方区分無しのレースにも適用します。</li>
</ul>
<hr>
<form method="post" action="./confirm.php">
<input type="submit" value="変更内容を確認">
<input type="hidden" name="horse_id" value="<?=h($horse_id)?>">
<table class="horse_history">
<tr>
    <th><?=$setting->horse_record_date==='umm'?'時期':'年月'?></th>
    <th>開催</th>
    <th>レース名</th>
    <th>格付</th>
    <th>枠</th>
    <th>馬<br>番</th>
    <th>人<br>気</th><th colspan="2">着順</th><th colspan="2">着補正</th><th>騎手</th>
    <th>斤量</th>
    <th>タイム</th>
    <th>馬体重</th>
    <th>所属</th>
    <th>厩舎</th>
    <th>調教</th>
    <th>性別</th>
    <th>地方区分</th>
</tr><?php
$registration_only_race_is_exists=false;
$latest_race_is_exists=false; ?>
<?php foreach ($race_history as $data):?>
<?php
    $tr_class=new Imploader(' ');

    if(empty($data->race_id)){ continue; }
    $race = $data->race_row;
    $grade = $data->grade_row;
    $jockey=$data->jockey_row;

    if(!empty($session->latest_race['id'])&&
        $session->latest_race['id']===$data->race_id)
    {
        $latest_race_is_exists=true;
    }
    $race_url_add_param='';
    if($data->is_registration_only==1){
        $registration_only_race_is_exists=true;
        $tr_class->add('disabled_row');
    }
    $tr_class->add('race_grade_'.$grade->css_class_suffix);
    if($race->is_enabled===0){ $tr_class->add('disabled_row'); }
?>
<tr class="<?=h($tr_class)?>">
<?php
    $datetime=null;
    if($race->date!=null && $race->is_tmp_date==0){
        $datetime=new DateTime($race->date);
    }
    $month=$race->month;
    // ウマ娘ターン表記の場合は補正済み月を優先
    if($setting->horse_record_date==='umm' && $data->w_month > 0){
        $month=$data->w_month;
    }
    $day=is_null($datetime)?0:(int)$datetime->format('d');
    $date_str=$setting->getRaceListDate([
        'year'=>$race->year,
        'month'=>$month,
        'day'=>$day,
        'turn'=>$data->umm_month_turn,
        'age'=>$race->year - $horse->birth_year]);
    $url = '';
    if($setting->horse_record_date==='umm'){
        if($data->umm_month_turn > 0){
            $url = $page->getTurnRaceListUrl($race->year,$month,$data->umm_month_turn);
        }
    }else if($datetime!==null){
        $url=$page->getDateRaceListUrl($datetime);
    }else{
        $url = $page->getTurnRaceListUrl($race->year,$month,null,['week'=>$race->week_id]);
    }
    $date_str=(new MkTagA($date_str,$url))->get();
?>
<td><?=$date_str?></td>
<?php
    $a_tag=new MkTagA($data->course_row->short_name??$race->race_course_name);
    if($datetime!==null){
        $a_tag->href($page->getDateRaceListUrl(
            $datetime,
            ['race_course_name'=>$race->race_course_name]
        ));
        $a_tag->title($race->race_course_name);
    }
?>
<td class="race_course_name"><?=$a_tag?></td>
<td class="race_name">
    <a href="<?=h($page->getRaceResultUrl($data->race_id).$race_url_add_param)?>" title="<?=h($race->race_name.($race->caption?'：'.$race->caption:''))?>"><?=h($race->race_short_name?:$race->race_name)?></a>
</td>
<td class="grade"><?=h(($grade->short_name??'')?:$race->grade)?></td>
<td class="in_input frame_number">
    <input type="text" name="race[<?=h($data->race_id)?>][frame_number]" style="width: 1.5em;" value="<?=h($data->frame_number)?>" placeholder="枠">
</td>
<td class="in_input horse_number">
    <input type="text" name="race[<?=h($data->race_id)?>][horse_number]" style="width: 1.5em;" value="<?=h($data->horse_number)?>" placeholder="番">
</td>
<td class="in_input favourite">
    <input type="text" name="race[<?=h($data->race_id)?>][favourite]" style="width: 1.5em;" value="<?=h($data->favourite)?>" placeholder="人">
</td>
<?php
    $result_s='';
    $h_result_txt='';
    if($data->result_text!=''){
        $h_result_txt=h($data->special_result_short_name_2?:$data->result_text);
        $result_s=$data->result_text;
    }else if($data->result_number > 0){
        $result_s=$data->result_number;
        if($data->result_before_demotion > 0){
            $h_result_txt.="<span title=\"※".h($data->result_before_demotion)."位入線降着\">(降)</span>";
        }
        $h_result_txt.=h($data->result_number."着");
    }
    echo "<td class=\"result_number "."\">{$h_result_txt}</td>";
?>
<td class="in_input">
    <input type="text" name="race[<?=h($data->race_id)?>][result]" style="width: 2em;" value="<?=h($result_s)?>" placeholder="着">
</td>
<!--<td class="in_input">
    <input type="text" name="race[<?=h($data->race_id)?>][result_number]" style="width: 2em;" value="<?=h($data->result_number)?>" placeholder="着">
</td>
<td class="in_input">
    <input type="text" name="race[<?=h($data->race_id)?>][result_text]" style="width: 3em;" value="<?=h($data->result_text)?>" placeholder="特殊">
</td>-->
<td class="in_input">
    <input type="text" name="race[<?=h($data->race_id)?>][result_order]" style="width: 2em;" value="<?=h($data->result_order)?>" placeholder="補正">
</td>
<td class="in_input">
    <input type="text" name="race[<?=h($data->race_id)?>][result_before_demotion]" style="width: 1.5em;" value="<?=h($data->result_before_demotion?:'')?>" placeholder="降">
</td>
<td class="in_input">
    <input type="text" name="race[<?=h($data->race_id)?>][jockey]" style="width: 6em;" value="<?=h($data->jockey_name)?>" placeholder="騎手">
</td>
<td class="in_input handicap">
    <input type="text" name="race[<?=h($data->race_id)?>][handicap]" style="width: 2.5em;" value="<?=h($data->handicap)?>" placeholder="斤量">
</td>
<td class="in_input time">
    <input type="text" name="race[<?=h($data->race_id)?>][time]" style="width: 3em;" value="<?=h($data->time)?>" placeholder="タイム">
</td>
<td class="in_input">
    <input type="text" name="race[<?=h($data->race_id)?>][h_weight]" style="width: 3em;" value="<?=h($data->h_weight)?>" placeholder="馬体重">
</td>
<td class="in_input">
    <input type="text" name="race[<?=h($data->race_id)?>][tc]" style="width: 3em;" value="<?=h($data->tc)?>" placeholder="所属">
</td>
<td class="in_input">
    <input type="text" name="race[<?=h($data->race_id)?>][trainer_name]" style="width: 6em;" value="<?=h($data->trainer_name)?>" placeholder="厩舎">
</td>
<td class="in_input">
    <input type="text" name="race[<?=h($data->race_id)?>][training_country]" style="width: 2.5em;" value="<?=h($data->training_country)?>" placeholder="国">
</td>
<?php $s_radio=MkTagInput::Radio("race[".$data->race_id."][sex]"); ?>
<td class="sex">
    <label><?=$s_radio->value(0)->checked($data->sex==0||$horse->sex==2)?>馬</label>
    <label><?=$s_radio->value(1)->checkedIf($data->sex)->disabled($horse->sex==2)?>牡</label>
    <label><?=$s_radio->value(3)->checkedIf($data->sex)->disabled($horse->sex==2)?>セ</label>
</td>
<?php $n_radio=MkTagInput::Radio("race[".$data->race_id."][is_affliationed_nar]"); ?>
<td class="is_affliationed_nar">
    <label><?=$n_radio->value(0)->checkedIf($data->is_affliationed_nar)?>なし</label>
    <label><?=$n_radio->value(1)->checkedIf($data->is_affliationed_nar)?>[地]</label>
    <label><?=$n_radio->value(2)->checkedIf($data->is_affliationed_nar)?>(地)</label>
</td>
</tr>
<?php endforeach; ?>
</table>
</form>
<a id="under_results_table"></a>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>