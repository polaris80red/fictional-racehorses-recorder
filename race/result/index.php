<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
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
<?php require_once APP_ROOT_DIR."/race/race_content_header.inc.php"; ?>
<hr>
<?php
# レース着順取得
$sql=(function(){
    $horse_tbl=Horse::TABLE;
    $race_tbl=Race::TABLE;
    $r_results_tbl=RaceResults::TABLE;
    $race_special_results_tbl=RaceSpecialResults::TABLE;
    $jockey_tbl=Jockey::TABLE;
    $trainer_tbl=Trainer::TABLE;

    $horse_s_columns=new SqlMakeSelectColumns(Horse::TABLE);
    $horse_s_columns->addColumnsByArray([
        'name_ja','name_en','birth_year'
    ]);
    $horse_s_columns->addColumnAs('sex','horse_sex');
    $horse_s_columns->addColumnAs('tc','horse_tc');
    $horse_s_columns->addColumnAs('training_country','horse_training_country');
    $horse_s_columns->addColumnAs('is_affliationed_nar','horse_is_affliationed_nar');
    $sql_part_select_columns=implode(",\n",[
        "`r_results`.*",
        $horse_s_columns->get(true),
        "`race`.*",
        "`spr`.`short_name_2` AS special_result_short_name_2",
        "`spr`.`is_registration_only`",
        "`jk`.`short_name_10` as jockey_mst_short_name_10",
        "`jk`.`is_anonymous` as jockey_mst_is_anonymous",
        "`jk`.`is_enabled` as jockey_mst_is_enabled",
        "`trainer`.`short_name_10` as trainer_mst_short_name_10",
        "`trainer`.`is_anonymous` as trainer_mst_is_anonymous",
        "`trainer`.`is_enabled` as trainer_mst_is_enabled",
    ]);
     
    $sql=<<<END
    SELECT
    {$sql_part_select_columns}
    FROM `{$race_tbl}` AS `race`
    LEFT JOIN `{$r_results_tbl}` AS `r_results`
        ON `race`.`race_id`=`r_results`.`race_id`
    LEFT JOIN `{$horse_tbl}`
        ON `r_results`.`horse_id`=`{$horse_tbl}`.`horse_id`
    LEFT JOIN `{$race_special_results_tbl}` as spr
        ON `r_results`.result_text LIKE spr.unique_name AND spr.is_enabled=1
    LEFT JOIN `{$jockey_tbl}` as `jk`
        ON `r_results`.`jockey`=`jk`.`unique_name` AND `jk`.`is_enabled`=1
    LEFT JOIN `{$trainer_tbl}` as `trainer`
        ON `{$horse_tbl}`.`trainer`=`trainer`.`unique_name` AND `trainer`.`is_enabled`=1
    WHERE `race`.`race_id`=:race_id
    ORDER BY
        `r_results`.`result_number` IS NULL,
        `r_results`.`result_number` ASC,
        `r_results`.`result_order` IS NULL,
        `r_results`.`result_order` ASC,
        `spr`.`sort_number` IS NULL,
        `spr`.`sort_number` ASC,
        `r_results`.`result_text` ASC;
    END;
    return $sql;
})();

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':race_id', $race_id, PDO::PARAM_STR);
$flag = $stmt->execute();
$table_data=[];
while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if(empty($data['horse_id'])){ continue; }
    $data['sex']=(int)($data['sex']?:$data['horse_sex']);
    $data['sex_str']=sex2String($data['sex']);
    $data['age']=empty($data['birth_year'])?'':($race->year-$data['birth_year']);
    if($data['jockey_mst_is_enabled']==1){
        if($data['jockey_mst_is_anonymous']==1){
            $data['jockey']=(!$page->is_editable)?'□□□□':($data['jockey_mst_short_name_10']?:$data['jockey']);
        }else{
            $data['jockey']=$data['jockey_mst_short_name_10']?:$data['jockey'];
        }
        if($page->is_editable){}
    }
    if($data['trainer_mst_is_enabled']==1){
        if($data['trainer_mst_is_anonymous']==1){
            $data['trainer']=(!$page->is_editable)?'□□□□':($data['trainer_mst_short_name_10']?:$data['trainer']);
        }else{
            $data['trainer']=$data['trainer_mst_short_name_10']?:$data['trainer'];
        }
        if($page->is_editable){}
    }
    $table_data[]=$data;
}
$mode_umm=false;
switch($setting->age_view_mode){
    case Setting::AGE_VIEW_MODE_UMAMUSUME:
    case Setting::AGE_VIEW_MODE_UMAMUSUME_S:
        $mode_umm=true;
}
$empty_row_2="<td>&nbsp;</td><td></td><td class=\"horse_name\"></td><td></td><td></td><td></td><td></td><td></td><td></td>";
if(!$mode_umm){ $empty_row_2.="<td></td><td></td>"; }
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
<th>着差</th>
<th>コーナー<br>通過順位</th>
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
    // 馬情報がない異常データのスキップ
    if(empty($data['horse_id'])){
        ELog::debug($data);
        //echo "<tr><td></td>".$empty_row_2."</tr>\n";
        continue;
    }
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
                    $url=$page->to_app_root_path."race/horse_result/form.php?race_id={$race->race_id}&result_number={$i}";
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
<td class="horse_name">
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
    $is_affliationed_nar=0;
    if($data['is_affliationed_nar']===null){
        $is_affliationed_nar=$data['horse_is_affliationed_nar'];
    }else{
        $is_affliationed_nar=$data['is_affliationed_nar'];
    }
    if($data['is_jra']==1&& $is_affliationed_nar==1){
        echo "[地] ";
    }
    echo '<a href="'.$page->to_app_root_path.'horse/?horse_id='.h($data['horse_id']).$horse_url_add_param.'">';
    echo ($data['name_ja']?:$data['name_en']);
    if($data['is_jra']==0 && $data['is_nar']==0){
        echo " <span>(".h($training_country).")</span> ";
    }
    echo "</a>";
?></td>
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
<td><?=h($data['jockey']??'')?></td>
<?php endif; ?>
<td><?=h($data['margin'])?></td>
<?php
    $corner_numbers=[];
    if($data['corner_1']>0){ $corner_numbers[]=$data['corner_1']; }
    if($data['corner_2']>0){ $corner_numbers[]=$data['corner_2']; }
    if($data['corner_3']>0){ $corner_numbers[]=$data['corner_3']; }
    if($data['corner_4']>0){ $corner_numbers[]=$data['corner_4']; }
?><td class="col_corner_numbers"><?=h(implode('-',$corner_numbers))?></td>
<td><?=h(!empty($data['tc'])?$data['tc']:$data['horse_tc'])?></td>
<?php if($setting->age_view_mode!==1): ?>
<td><?=h($data['trainer']??'')?></td>
<?php endif; ?>
<td class="col_favourite favourite_<?=h($data['favourite'])?>"><?=h($data['favourite'])?></td>
<?php
    if(!empty($data['horse_id'])){
        $url =APP_ROOT_REL_PATH."race/horse_result/form.php?";
        $url.=(new UrlParams(['race_id'=>$race->race_id,'horse_id'=>$data['horse_id'],'edit_mode'=>1]));   
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
<hr><input type="button" id="edit_tgl" value="編集" style="<?=!EDIT_MENU_TOGGLE?'display:none;':''?>">
<input type="hidden" id="hiddden_race_id" value="<?=h($race->race_id)?>">
<input type="button" value="レースIDをクリップボードにコピー" onclick="copyToClipboard('#hiddden_race_id');">
(race_id=<?=h($race->race_id)?>)<a id="edit_menu"></a>
<div class="edit_menu" style="<?=EDIT_MENU_TOGGLE?'display:none;':''?>"> 
<input type="hidden" id="edit_menu_states" value="0">
<table>
    <tr>
<?php $url=APP_ROOT_REL_PATH."race/result/form.php?race_id={$race->race_id}&edit_mode=1"; ?>
        <td><a href="<?=$url?>">このレースの情報を編集</a></td>
<?php $url=APP_ROOT_REL_PATH."race/horse_result/form.php?race_id={$race->race_id}"; ?>
        <td><a href="<?=h($url)?>">このレースに戦績を追加</a></td>
        <td><a href="<?=APP_ROOT_REL_PATH?>race/update_race_result_id/form.php?race_id=<?=h($race->race_id)?>&edit_mode=1">レースID修正</a></td>
    </tr>
    <tr>
<?php
$a_tag=new MkTagA('最後に開いた馬をこのレースに追加');
$latest_horse=new Horse();
if(!empty($session->latest_horse['id'])){
    $latest_horse->setDataById($pdo,$session->latest_horse['id']);
}
if($latest_horse->record_exists){
    if($latest_horse_exists){
        $a_tag->title("最後に開いた競走馬は既に登録されています")->setStyle('text-decoration','line-through');
    }else if($latest_horse->birth_year==null){
        $a_tag->title("生年仮登録馬のため戦績追加不可")->setStyle('text-decoration','line-through');
    }else{
        $url=APP_ROOT_REL_PATH."race/horse_result/form.php?horse_id={$session->latest_horse['id']}&race_id={$race->race_id}";
        $a_tag->href($url);
    }
}
?>
        <td colspan="2"><?=$a_tag?></td>
        <td>
<?php if(!empty($session->latest_horse['id'])): ?>
<?php $url=APP_ROOT_REL_PATH."horse/?horse_id={$session->latest_horse['id']}"; ?>
<a href="<?=h($url)?>"><?=h($session->latest_horse['name']?:$session->latest_horse['id'])?></a>
<?php endif; ?>
        </td>
    </tr>
    <tr>
        <td>
<a href="<?=APP_ROOT_REL_PATH?>race/result/form.php?race_id=<?=h($race->race_id);?>&edit_mode=0">コピーして新規登録</a>
        </td>
<?php
    $a_tag=new MkTagA('同日同場で新規登録');
    if($race->date!=''){
        $a_tag->setLinkText('同日同場で新規登録');
        $urlparam=new UrlParams([
            'date'=>$race->date,
            'race_course_name'=>$race->race_course_name]);
        $a_tag->href(APP_ROOT_REL_PATH."race/result/form.php?".$urlparam);
    }else{
        $a_tag->setLinkText('同週同場で新規登録');
        $urlparam=new UrlParams([
            'year'=>$race->year,
            'week_id'=>$race->week_id,
            'race_course_name'=>$race->race_course_name]);
        $a_tag->href(APP_ROOT_REL_PATH."race/result/form.php?".$urlparam);
    }
    ?>
        <td><?=$a_tag?></td>
        <td></td>
    </tr>
</table>
</div>
<script>
$(function() {
    $('#edit_tgl').click(function(){
    if($('#edit_menu_states').val()=='0') {
        $('.edit_menu').css('display','block');
        $('#edit_menu_states').val('1');
    } else {
        $('.edit_menu').css('display','none');
        $('#edit_menu_states').val('0');
    }
    });
});
</script>
<?php endif; ?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>