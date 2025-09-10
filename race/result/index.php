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

.edit_menu table { margin-top: 8px;}
.edit_menu table a:link {text-decoration: none;}
.edit_menu table {font-size: 0.9em;}
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
$mode_umm=false;
switch($setting->age_view_mode){
    case Setting::AGE_VIEW_MODE_UMAMUSUME:
    case Setting::AGE_VIEW_MODE_UMAMUSUME_S:
        $mode_umm=true;
}
$empty_row_2="<td>&nbsp;</td><td></td><td class=\"horse_name\"></td>".str_repeat('<td></td>',8);
if(!$mode_umm){ $empty_row_2.=str_repeat('<td></td>',3); }
?>
<table class="race_results">
<tr>
<th>着順</th><th>枠</th><th>馬番</th>
<th style="min-width:12em;">馬名</th>
<th><?php if(
    $setting->age_view_mode===Setting::AGE_VIEW_MODE_UMAMUSUME||
    $setting->age_view_mode===Setting::AGE_VIEW_MODE_UMAMUSUME_S){
        print '級';
    }else{ print '性齢'; }
    ?></th>
<th>負担<br>重量</th>
<?php if(!$mode_umm): ?>
<th>騎手</th>
<?php endif; ?>
<th>タイム</th>
<th>着差</th>
<th>コーナー<br>通過順位</th>
<th><?=$race->course_type==='障'?'平均<br>1f':'推定<br>上り'?></th>
<?php if(!$mode_umm): ?>
<th>馬体重</th>
<?php endif; ?>
<th>所属</th>
<?php if(!$mode_umm): ?>
<th>調教師</th>
<?php endif; ?>
<th>人気</th>
<?php if($page->is_editable): ?><th>編</th><?php endif; ?>
</tr><?php
$i=0;
$registration_only_horse_is_exists=false;
$latest_horse_exists=false;
foreach ($table_data as $data) {
    $i++;
    $tr_class=new Imploader(' ');
    if($data['horse_id']==($session->latest_horse['id']??'')){
        $latest_horse_exists=true;
    }
    // 特別登録のみのデータは表示フラグがなければスキップ
    $horse_url_add_param='';
    if($data['is_registration_only']){
        $registration_only_horse_is_exists=true;
        if(!$show_registration_only){
            continue;
        }else{
            $horse_url_add_param='&show_registration_only=true';
            $tr_class->add('disabled_row');
        }
    }
    // 途中着順の場合
    /*
    18着以内かつ現在処理中の行より先の着順が出てきたときはその着順まで空行を挟む。
    */
    if($data['result_number']>$i && $data['result_number']<=18){
        for($j=$i;$j<$data['result_number'];$j++){
            echo "<tr class=\"result_number_{$j}\"><td>{$j}</td>".$empty_row_2;
            if($page->is_editable){
                echo "<td>";
                if(!empty($data['horse_id'])){
                    $url=InAppUrl::to(Routes::HORSE_RACE_RESULT_EDIT,['race_id'=>$race->race_id,'result_number'=>$i]);
                    echo '<a href="'.$url.'" title="新規登録">新</a><br>';
                }
                echo "</td>";
            }
            echo "</tr>\n";
            $i++;
        }
    }
    $tr_class->add('result_number_'.$data['result_number']);
?><tr class="<?php echo $tr_class; ?>">
<td><?php
if($data['result_text']!=''){
    print_h($data['special_result_short_name_2']?:$data['result_text']);
}else if($data['result_number']>0){
    print_h($data['result_number']);
    if($data['result_before_demotion']>0){ print"<span title=\"※".$data['result_before_demotion']."位入線降着\">(降)</span>";}
}
?></td>
<td class="waku_<?=h($data['frame_number'])?>"><?=h($data['frame_number'])?></td>
<td><?=h($data['horse_number'])?></td>
<?php
    $is_affliationed_nar=0;
    if($data['is_affliationed_nar']===null){
        $is_affliationed_nar=$data['horse_is_affliationed_nar'];
    }else{
        $is_affliationed_nar=$data['is_affliationed_nar'];
    }
    $marks=new Imploader('');
    if(($race->is_jra==1 || $race->is_nar==1)){
        // 中央競馬または地方競馬の場合、調教国・生産国でカク外・マル外マークをつける
        if($data['training_country']!='' && $data['training_country']!='JPN'){
            // 外国調教馬にカク外表記
            $marks->add("[外]");
        }else{
            // 中央競馬の場合のみ地方所属馬と元地方所属馬のカク地・マル地マーク
            if($race->is_jra){
                if($is_affliationed_nar==1){
                    $marks->add("[地]");
                }else if($is_affliationed_nar==2){
                    $marks->add("(地)");
                }
            }
            // 外国産馬のマル外表記
            if($data['breeding_country']!='' && $data['breeding_country']!='JPN'){
                $marks->add("(外)");
            }
        }
    }
    $a_tag=new MkTagA($data['name_ja']?:$data['name_en']);
    $a_tag->href($page->to_app_root_path.'horse/?horse_id='.$data['horse_id']);
    $country=($race->is_jra==0 && $race->is_nar==0)?"<span>(".h($data['training_country']).")</span> ":'';
?>
<td class="horse_name"><?=implode(' ',[$marks,$a_tag,$country])?></td>
<?php
    $s_str='';
    if($setting->age_view_mode===Setting::AGE_VIEW_MODE_DEFAULT){
        // 通常表記の場合
        $s_str.=$data['sex_str'];
    }
    $s_str.=$setting->getAgeSexSpecialFormat($data['age'],$data['sex']);
?><td class="sex_<?=h($data['sex'])?>"><?=h($s_str)?></td>
</td>
<td><?=h($data['handicap'])?></td>
<?php if($setting->age_view_mode!==1): ?>
<td style="<?=$data['jockey_row']->is_anonymous?'color:#999;':''?>"><?=h($data['jockey_name']??'')?></td>
<?php endif; ?>
<td><?=h($data['time'])?></td>
<td><?=h($data['margin'])?></td>
<?php
    $corner_numbers=[];
    if($data['corner_1']>0){ $corner_numbers[]=$data['corner_1']; }
    if($data['corner_2']>0){ $corner_numbers[]=$data['corner_2']; }
    if($data['corner_3']>0){ $corner_numbers[]=$data['corner_3']; }
    if($data['corner_4']>0){ $corner_numbers[]=$data['corner_4']; }
?><td class="col_corner_numbers"><?=h(implode('-',$corner_numbers))?></td>
<td><?=h($data['f_time'])?></td>
<?php if(!$mode_umm): ?>
    <td><?=h($data['h_weight'])?></td>
<?php endif; ?>
<td><?=h($data['tc'])?></td>
<?php if(!$mode_umm): ?>
    <td style="<?=$data['trainer_row']->is_anonymous?'color:#999;':''?>">
        <?=h($data['trainer_name']??'')?>
    </td>
<?php endif; ?>
<td class="col_favourite favourite_<?=h($data['favourite'])?>"><?=h($data['favourite'])?></td>
<?php
    if(!empty($data['horse_id'])){
        $url=InAppUrl::to(Routes::HORSE_RACE_RESULT_EDIT,[
            'race_id'=>$race->race_id,
            'horse_id'=>$data['horse_id'],
            'edit_mode'=>1
        ]);
    }
?>
<?php if($page->is_editable): ?>
<td><a href="<?=h($url)?>" title="編集">編</a></td>
<?php endif; ?>
</tr>
<?php } ?></table>
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
    <td><?=$a_tag; ?></td></tr>
<?php endif; ?>
<?php if(!empty($race->week_id)): ?>
<tr><th>ターン</th><td><?php
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
?></td></tr>
<?php endif; ?>
<?php if(!empty($page->is_editable)): ?>
<tr><th>ワールド</th><td><?=h((new World($pdo,$race->world_id))->name??'')?></td></tr>
<?php endif; ?>
<tr><th>備考</th><td><?=nl2br(h($race->note))?></td></tr>
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