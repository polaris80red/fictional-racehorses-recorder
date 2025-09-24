<?php
/**
 * レースページ上部の共通部分
 * @var Page $page
 * @var Setting $setting
 * @var RaceRow $race
 */
// 未定義なら定義する
$hasThisweek=$hasThisweek??false;
$hasSps=$hasSps??false;
?><div class="race_header">
<div>
<div style="border-bottom:solid 1px #CCC;margin-bottom: 4px;float:left;"><?php
$day=($race->date == null || $race->is_tmp_date)?null:(new DateTime($race->date))->format('d');
$week=(new RaceWeek())->getById($pdo,$race->week_id);
$umdb_date = $setting->getRaceListDate([
    'year'=>$race->year,
    'month'=>(($race->date == null || $race->is_tmp_date || $setting->horse_record_date==='umm')&& isset($week->month))?$week->month:$race->month,
    'day'=>$day,
    'turn'=>$week_data->umm_month_turn??null
]);
$grade_obj=RaceGrade::getByUniqueName($pdo,$race->grade);

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
    ($week!==false && !empty($week->id) && !empty($week->umm_month_turn))){
    // ウマ娘モードか仮日付で、有効な週が設定されているとターンで表示
    $url=InAppUrl::to('race/list/in_week.php',[
        'year'=>$race->year,
        'month'=>$race->month,
        'turn'=>$week->umm_month_turn,
    ]);
    $a_tag->href($url);
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
if($race->weather){
    print_h("　[天候：{$race->weather}]");
}
if($race->track_condition){
    if($race->course_type==="芝"||$race->course_type==="障"){
        print_h("　[芝：{$race->track_condition}]");
    }
    if($race->course_type==="ダ"||$race->course_type==="障"){
        print_h("　[ダ：{$race->track_condition}]");
    }
}
?></div>
<div style="float: right;"><a href="<?=$page->getRaceResultUrl($race_id); ?>#edit_menu" style="text-decoration: none;" title="レース結果下部編集メニュー">▽</a></div>
</div>
<div style="clear: both;"><!-- race title block -->
<div class="race_number floatLeft">
    <?php print $race->race_number?h($race->race_number."R"):'&nbsp;'; ?>
</div>
<div class="race_grade_and_name">
    <span class="nowrap" style="display: inline-block; min-width:16em;border-bottom:solid 1px #CCC;"><?=h($race->race_name)?></span>
    <?php if($race->grade): ?>
    <span style="" class="ib grade <?=h($grade_obj->css_class??'')?>"><?=h(($grade_obj->short_name??'')?:$race->grade)?></span>
    <?php endif; ?>
</div>
<div class="" style="font-size: 0.9em;"><?php
$age_name_row=RaceCategoryAge::getById($pdo,$race->age_category_id);
if(
    $setting->age_view_mode===Setting::AGE_VIEW_MODE_UMAMUSUME||
    $setting->age_view_mode===Setting::AGE_VIEW_MODE_UMAMUSUME_S){
    $age_name=$age_name_row->name_umamusume."級";
}else{
    $age_name=$age_name_row->name??'';
}
print_h(" ".($race->age?:$age_name));
$race_category_sex=RaceCategorySex::getById($pdo,$race->sex_category_id);
$race_sex_name=$race_category_sex->short_name_3??'';
// 年齢形式がウマ娘の場合は変換
if(
    $setting->age_view_mode===Setting::AGE_VIEW_MODE_UMAMUSUME||
    $setting->age_view_mode===Setting::AGE_VIEW_MODE_UMAMUSUME_S){
        $race_sex_name=(string)$race_category_sex->umm_category;
}
if($race_sex_name!==''){
    print_h("（{$race_sex_name}）");
}

(function($grade){
    if($grade==false){return; }
    if(empty($grade->category)){return;}
    print_h(" ".$grade->category??'');
})($grade_obj);

print_h(" {$race->course_type}{$race->distance}m");
?></div>
</div><!-- /race title block -->
<hr class="clear no-css-fallback">
<div class="race_header_navigation">
<a href="<?=h($page->getRaceResultUrl($race_id))?>" title="着順">結果</a>
| <a href="<?=h(InAppUrl::to('race/syutsuba.php',['race_id'=>$race_id]))?>" title="NK出馬表">出馬表</a>
| <a href="<?=h(InAppUrl::to('race/syutsuba_sp.php',['race_id'=>$race_id]))?>" title="Jスペシャル出馬表">出馬表(4走)</a>
<?php if(in_array($race->grade,['G1','G2','G3','Jpn1','Jpn2','Jpn3','重賞']) && $hasThisweek): ?>
| <a href="<?=h(InAppUrl::to('race/j_thisweek.php',['race_id'=>$race_id]))?>" title="J今週の注目レース・出走馬情報">出走馬情報</a>
<?php endif; ?>
<?php if(in_array($race->grade,['G1','Jpn1']) && $hasSps): ?>
| <a href="<?=h(InAppUrl::to('race/j_thisweek_sps.php',['race_id'=>$race_id]))?>" title="Jスペシャル出馬表紹介文">出馬表コメント</a>
<?php endif; ?>
</div>
</div>
<hr class="clear no-css-fallback">