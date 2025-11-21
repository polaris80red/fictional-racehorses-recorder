<?php
/**
 * レース結果の表テンプレート（netkeiba風並び順）
 * @var Page $page
 * @var Setting $setting
 * @var RaceRow $race
 * @var RaceResultsGetter $resultsGetter
 * @var RaceResultsPageRow[] $table_data
 * @var bool $mode_umm
 */
?><?php
$empty_row_2="<td>&nbsp;</td><td></td><td class=\"horse_name\"></td>";
$empty_row_2.=str_repeat('<td></td>',10);
if(!$mode_umm){ $empty_row_2.=str_repeat('<td></td>',4); }
?>
<table class="race_results">
<tr>
    <th>着<br>順</th>
    <th>枠<br>番</th>
    <th>馬<br>番</th>
    <th style="min-width:12em;">馬名</th>
    <th><?php if(
        $setting->age_view_mode===Setting::AGE_VIEW_MODE_UMAMUSUME||
        $setting->age_view_mode===Setting::AGE_VIEW_MODE_UMAMUSUME_S){
            print '級';
        }else{ print '性齢'; }
    ?></th>
    <th>斤量</th>
    <?php if(!$mode_umm): ?>
        <th>騎手</th>
    <?php endif; ?>
    <th>タイム</th>
    <th>着差</th>
    <th>通過</th>
    <th><?=$race->course_type==='障'?'平均<br>1f':'推定<br>上り'?></th>
    <th>単勝</th>
    <th>人気</th>
    <?php if(!$mode_umm): ?>
        <th>馬体重</th>
    <?php endif; ?>
    <th>所属</th>
    <?php if(!$mode_umm): ?>
        <th>調教師</th>
        <th>馬主</th>
    <?php endif; ?>
    <th>賞金</th>
    <?php if($page->is_editable): ?><th>編</th><?php endif; ?>
</tr><?php
$i=0;
?>
<?php foreach ($table_data as $data): ?>
    <?php
        $horse=$data->horseRow;
        $raceResult=$data->resultRow;
        $spr=$data->specialResultRow;
        $i++;
        $tr_class=new Imploader(' ');
        if($horse->horse_id==($session->latest_horse['id']??'')){
            $latest_horse_exists=true;
        }
        // 特別登録のみのデータは表示フラグがなければスキップ
        $horse_url_add_param='';
        if($spr->is_registration_only){
            $registration_only_horse_is_exists=true;
            if(!$show_registration_only){
                continue;
            }else{
                $horse_url_add_param='&show_registration_only=true';
                $tr_class->add('disabled_row');
            }
        }
    ?>
    <?php if($raceResult->result_number>$i && $raceResult->result_number<=18): ?>
        <?php /* 18着以内かつ現在処理中の行より先の着順が出てきたときはその着順まで空行を挟む。*/ ?>
        <?php for($j=$i;$j<$raceResult->result_number;$j++):?>
            <tr class="result_number_<?=$j?>">
                <td><?=$j?></td>
                <?=$empty_row_2?>
                <?php if($page->is_editable):?>
                    <td>
                    <?php if(!empty($horse->horse_id)):?>
                        <?php $url=InAppUrl::to(Routes::HORSE_RACE_RESULT_EDIT,['race_id'=>$race->race_id,'result_number'=>$i]);?>
                        <a href="<?=h($url)?>" title="新規登録">新</a>
                    <?php endif;?>
                    </td>
                <?php endif;?>
            </tr>
            <?php $i++; ?>
        <?php endfor;?>
    <?php endif;?>
    <tr class="<?=$tr_class->add('result_number_'.$raceResult->result_number)?>">
        <?php
            $h_result_str='';
            if($raceResult->result_text!=''){
                $h_result_str=h($spr->short_name_2?:$raceResult->result_text);
            }else if($raceResult->result_number>0){
                $h_result_str=h($raceResult->result_number);
                if($raceResult->result_before_demotion>0){
                    $h_result_str.="<span title=\"※".h($raceResult->result_before_demotion)."位入線降着\">(降)</span>";
                }
            }
        ?>
        <td><?=$h_result_str?></td>
        <td class="waku_<?=h($raceResult->frame_number)?>"><?=h($raceResult->frame_number)?></td>
        <td><?=h($raceResult->horse_number)?></td>
        <?php
            $is_affliationed_nar=0;
            if($raceResult->is_affliationed_nar===null){
                $is_affliationed_nar=$horse->is_affliationed_nar;
            }else{
                $is_affliationed_nar=$raceResult->is_affliationed_nar;
            }
            $marks=new Imploader('');
            if(($race->is_jra==1 || $race->is_nar==1)){
                // 中央競馬または地方競馬の場合、調教国・生産国でカク外・マル外マークをつける
                if($data->trainingCountry!='' && $data->trainingCountry!='JPN'){
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
                    if($horse->breeding_country!='' && $horse->breeding_country!='JPN'){
                        $marks->add("(外)");
                    }
                }
            }
            $a_tag=new MkTagA($horse->name_ja?:$horse->name_en);
            $a_tag->href($page->to_app_root_path.'horse/?horse_id='.$horse->horse_id);
            $country=($race->is_jra==0 && $race->is_nar==0)?"<span>(".h($data->trainingCountry).")</span> ":'';
        ?>
        <td class="horse_name"><?=implode(' ',[$marks,$a_tag,$country])?></td>
        <?php
            $s_str='';
            if($setting->age_view_mode===Setting::AGE_VIEW_MODE_DEFAULT){
                // 通常表記の場合
                $s_str.=$data->sexStr;
            }
            $s_str.=$setting->getAgeSexSpecialFormat($data->age,$data->sex);
        ?>
        <td class="sex_<?=h($data->sex)?>"><?=h($s_str)?></td>
        <td><?=h($raceResult->handicap)?></td>
        <?php if($setting->age_view_mode!==1): ?>
            <td style="<?=$data->jockeyRow->is_anonymous?'color:#999;':''?>">
                <?=!$data->jockeyName?'':(new MkTagA($data->jockeyName,InAppUrl::to('race/list/in_week_jockey.php',[
                    'year'=>$race->year,
                    'week'=>$race->week_id,
                    'jockey'=>$raceResult->jockey_name
                    ])))?>
            </td>
        <?php endif; ?>
        <td><?=h($raceResult->time)?></td>
        <td><?=h($raceResult->margin)?></td>
        <?php
            $corner_numbers=[];
            if($raceResult->corner_1>0){ $corner_numbers[]=$raceResult->corner_1; }
            if($raceResult->corner_2>0){ $corner_numbers[]=$raceResult->corner_2; }
            if($raceResult->corner_3>0){ $corner_numbers[]=$raceResult->corner_3; }
            if($raceResult->corner_4>0){ $corner_numbers[]=$raceResult->corner_4; }
        ?>
        <td class="col_corner_numbers"><?=h(implode('-',$corner_numbers))?></td>
        <?php
            $f_time_class='';
            if($raceResult->f_time!=''){
                $f_time_class=$resultsGetter->f_time_class_list[$raceResult->f_time]??'';
            }
        ?>
        <td class="f_time <?=h($f_time_class)?>"><?=h($raceResult->f_time)?></td>
        <td><?=h($raceResult->odds)?></td>
        <td class="col_favourite favourite_<?=h($raceResult->favourite)?>">
            <?=h($raceResult->favourite)?>
        </td>
        <?php if(!$mode_umm): ?>
            <td><?=h($raceResult->h_weight)?></td>
        <?php endif; ?>
        <td><?=h($data->tc)?></td>
        <?php if(!$mode_umm): ?>
            <td style="<?=$data->trainerRow->is_anonymous?'color:#999;':''?>">
                <?=h($data->trainerName??'')?>
            </td>
            <td><?=h($raceResult->owner_name?:$horse->owner_name)?></td>
        <?php endif; ?>
        <td><?=h($raceResult->earnings?:'')?></td>
        <?php
            if(!empty($horse->horse_id)){
                $url=InAppUrl::to(Routes::HORSE_RACE_RESULT_EDIT,[
                    'race_id'=>$race->race_id,
                    'horse_id'=>$horse->horse_id,
                    'edit_mode'=>1
                ]);
            }
        ?>
        <?php if($page->is_editable): ?>
            <?php
            $editTag=new MkTagA('編');
            if(Session::currentUser()->canEditHorse($horse)){
                $editTag->href($url)->title('編集');            
            }
            ?>
            <td><?=$editTag?></td>
        <?php endif; ?>
    </tr>
<?php endforeach; ?>
</table>