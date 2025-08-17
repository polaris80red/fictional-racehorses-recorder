<div class="race_header">
<div style="margin-bottom: 4px;"><span style="border-bottom:solid 1px #CCC;"><?php
$day=($race->date == null || $race->is_tmp_date)?null:(new DateTime($race->date))->format('d');
$week=(new RaceWeek())->getById($pdo,$race->week_id);
$umdb_date = $setting->getRaceListDate([
    'year'=>$race->year,
    'month'=>(($race->date == null || $race->is_tmp_date || $setting->horse_record_date==='umm')&& isset($week['month']))?$week['month']:$race->month,
    'day'=>$day,
    'turn'=>$week_data['umm_month_turn']??null
]);
$grade_obj=RaceGrade::getByRaceResultsKey($pdo,$race->grade);

$a_tag=new MkTagA($umdb_date->getLimitedString(null,'year_or_age_suffix'));
$a_tag->setStyle('text-decoration','none');
$a_tag->href($page->getRaceYearSearchUrl($race->year));
$a_tag->print();
echo $umdb_date->getLimitedString('year_or_age_m_separator','year_or_age_m_separator');
$a_tag=new MkTagA($umdb_date->getLimitedString('month'));
$a_tag->setStyle('text-decoration','none');
if((
    $setting->horse_record_date==='umm' ||
    $race->date == null || $race->is_tmp_date)&&
    ($week!==false && !empty($week['id']) && !empty($week['umm_month_turn']))){
    // ウマ娘モードか仮日付で、有効な週が設定されているとターンで表示
    $urlparam="year={$race->year}&month={$week['month']}&turn={$week['umm_month_turn']}";
    $a_tag->href(APP_ROOT_REL_PATH."race/list/in_week.php?$urlparam");
    $a_tag->title("同ターンのレース");
}else if($race->date != null && !$race->is_tmp_date){
    // それ以外は正規日付があれば表示
    $a_tag->href($page->getDateRaceListUrl((new DateTime($race->date))->format('Y-m-d')));
    $a_tag->title("同日のレース");
}
$a_tag->print();
if($race->race_course_name){
    $a_tag=new MkTagA($race->race_course_name);
    if($race->date != null && !$race->is_tmp_date){
        // 正規日付があり仮日付でない
        $a_tag->href($page->getDateRaceListUrl(
            (new DateTime($race->date))->format('Y-m-d'),
            ['race_course_name'=>$race->race_course_name]
        ));
        $a_tag->setStyle('text-decoration','none');
        $a_tag->title("同日の [ {$race->race_course_name} ] のレース");
    }
    echo "　".$a_tag;
}
if($race->track_condition){
    if($race->course_type==="芝"||$race->course_type==="障"){
        print "　[芝：{$race->track_condition}]";
    }
    if($race->course_type==="ダ"||$race->course_type==="障"){
        print "　[ダ：{$race->track_condition}]";
    }
}
?></span></div>
<div class="race_number floatLeft">
    <?php print $race->race_number?($race->race_number."R"):'&nbsp;'; ?>
</div>  
<div class="race_grade_and_name">
    <span class="nowrap" style="display: inline-block; min-width:16em;border-bottom:solid 1px #CCC;"><?php echo $race->race_name; ?></span>
    <?php if($race->grade): ?>
    <span style="" class="ib grade race_grade_<?php print $grade_obj->css_class_suffix??''; ?>"><?php echo $grade_obj->short_name??$race->grade; ?></span>
    <?php endif; ?>
</div>
<div class="" style="font-size: 0.9em;"><?php
$age_name_row=RaceCategoryAge::getById($pdo,$race->age_category_id);
if(
    $setting->age_view_mode===Setting::AGE_VIEW_MODE_UMAMUSUME||
    $setting->age_view_mode===Setting::AGE_VIEW_MODE_UMAMUSUME_S){
    $age_name=$age_name_row['name_umamusume']."級";
}else{
    $age_name=$age_name_row['name']??'';
}
echo " ".($race->age?:$age_name);
$race_sex_name=RaceCategorySex::getShortNameById($pdo,$race->sex_category_id);
// 年齢形式がウマ娘の場合は牝限のみティアラ路線表記
if(
    $setting->age_view_mode===Setting::AGE_VIEW_MODE_UMAMUSUME||
    $setting->age_view_mode===Setting::AGE_VIEW_MODE_UMAMUSUME_S){
    if($race_sex_name ==="牝"){
        $race_sex_name="ティアラ";
    }else{
        $race_sex_name ="";
    }
}
if($race_sex_name!==''){
    echo "（{$race_sex_name}）";
}

(function($grade){
    if($grade==false){return; }
    if(empty($grade->category)){return;}
    echo " ".$grade->category??'';
})($grade_obj);

echo " {$race->course_type}{$race->distance}m";
?></div>
</div>
<hr class="clear">
<a href="<?php echo $page->getRaceResultUrl($race_id); ?>" title="着順">[結果]</a>
<a href="<?php echo APP_ROOT_REL_PATH; ?>race/syutsuba_simple.php?race_id=<?php echo $race_id; ?>" title="NK出馬表">[出馬表]</a>
<a href="<?php echo APP_ROOT_REL_PATH; ?>race/syutsuba.php?race_id=<?php echo $race_id; ?>" title="出馬表">■</a> 
<a href="<?php echo APP_ROOT_REL_PATH; ?>race/syutsuba_sp.php?race_id=<?php echo $race_id; ?>" title="Jスペシャル出馬表">■</a>
<?php if(in_array($race->grade,['G1','G2','G3','Jpn1','Jpn2','Jpn3','重賞'])): ?>
<a href="<?php echo APP_ROOT_REL_PATH; ?>race/j_thisweek.php?race_id=<?php echo $race_id; ?>" title="J今週の注目レース・出走馬情報">■</a>
<?php endif; ?>
<?php if(in_array($race->grade,['G1','Jpn1'])): ?>
<a href="<?php echo APP_ROOT_REL_PATH; ?>race/j_thisweek_sps.php?race_id=<?php echo $race_id; ?>" title="Jスペシャル出馬表紹介文">■</a>
<?php endif; ?>
