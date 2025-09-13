<?php
/**
 * 基本的な戦績表
 * @var Page $page
 * @var Setting $setting
 * @var HorseRaceHistory $race_history
 */
?><table class="horse_history">
<tr>
    <th><?php
    echo $setting->horse_record_date==='umm'?'時期 ':'年月 ';
    $order=$setting->hors_history_sort_is_desc?"asc":"desc";
    $a_tag=new MkTagA($setting->hors_history_sort_is_desc?"↑":"↓");
    $a_tag->href("?".$page_urlparam->toString(['horse_history_order'=>$order]));
    $a_tag->setStyle('text-decoration','none');
    $a_tag->print();
    ?></th>
    <th>開催</th>
    <th>レース名</th>
    <th>格付</th>
    <th>距離</th>
    <th>馬場</th>
    <th><?=$mode_umm?'人数':'頭数'?></th>
    <th>人気</th>
    <th>着順</th>
    <?php if(!$mode_umm): ?><th>騎手</th><?php endif; ?>
    <th>斤量</th>
    <?php if(!$mode_umm): ?><th>馬体重</th><?php endif; ?>
    <th>タイム</th>
    <th>1着馬<span class="nowrap">(2着馬)</span></th><th>記</th>
    <?php if($page->is_editable): ?><th></th><?php endif; ?>
</tr><?php
$FUNC_print_empty_row=function($non_registered_prev_race_number,$next_race_id='') use($page,$horse_id,$mode_umm){
    $ret_text='';
    if($non_registered_prev_race_number>0){
        $ret_text.="<tr><td style=\"color:#999999;\">（{$non_registered_prev_race_number}戦～）</td>";
        $ret_text.=str_repeat("<td></td>",2)."<td>……</td>".str_repeat("<td></td>",9);
        if(!$mode_umm) { $ret_text.=str_repeat("<td></td>",2); }
        if($page->is_editable){
            $params=['horse_id'=>$horse_id];
            if($next_race_id!==''){
                $params['next_race_id']=$next_race_id;
            }
            $url =InAppUrl::to(Routes::HORSE_RACE_RESULT_EDIT,$params);
            $ret_text.="<td><a href=\"".h($url)."\">新</td>";
        }
        $ret_text.="</tr>\n";
    }
    return $ret_text;
};
$registration_only_race_is_exists=false;
$latest_race_is_exists=false;
?>
<?php foreach ($race_history as $data):?>
    <?php
        if(empty($data->race_id)){ continue; }
        $race = $data->race_row;
        $grade = $data->grade_row;
        $jockey=$data->jockey_row;
    ?>
    <?php if($date_order=='ASC'):// 日付昇順の場合の過去未登録行の追加処理 ?>
        <?=$FUNC_print_empty_row($data->non_registered_prev_race_number,$race->race_id)?>
    <?php endif; ?>
    <?php
        if(!empty($session->latest_race['id'])&&
            $session->latest_race['id']===$race->race_id)
            {
                $latest_race_is_exists=true;
            }
        $tr_class=new Imploader(' ');
        // 特別登録のみのデータは表示フラグがなければスキップ
        $race_url_add_param='';
        if($data->is_registration_only==1){
            $registration_only_race_is_exists=true;
            if(!$show_registration_only){
                continue;
            }else{
                $tr_class->add('disabled_row');
                $race_url_add_param='&show_registration_only=true';
            }
        }
        $tr_class->add($grade->css_class);
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
                    $url=$page->getTurnRaceListUrl($race->year,$month,$data->umm_month_turn);
                }
            }else if($datetime!==null){
                $url=$page->getDateRaceListUrl($datetime);
            }else{
                $url=$page->getTurnRaceListUrl($race->year,$month,null,['week'=>$race->week_id]);
            }
        ?>
        <td><?=(new MkTagA($date_str,$url))?></td>
        <?php
            $race_course_show_name = $data->course_row->short_name??$race->race_course_name;
            $a_tag=new MkTagA($race_course_show_name);
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
            <?=(new MkTagA($race->race_name,$page->getRaceResultUrl($race->race_id).$race_url_add_param))->title($race->race_name.($race->caption?'：'.$race->caption:''))?>
        </td>
        <td class="grade"><?=h(($grade->short_name??'')?:$race->grade)?></td>
        <td class="distance"><?=h($race->course_type.$race->distance)?></td>
        <td class="track_condition"><?=h($race->track_condition)?></td>
        <td class="number_of_starters"><?=h($race->number_of_starters)?></td>
        <td class="favourite favourite_<?=h($data->favourite)?>"><?=h($data->favourite)?></td>
        <?php
            $add_class=getResultClass($data->result_number);
            $h_result_txt='';
            if($data->result_text!=''){
                $h_result_txt=h($data->special_result_short_name_2?:$data->result_text);
            }else if($data->result_number > 0){
                if($data->result_before_demotion > 0){
                    $h_result_txt.="<span title=\"※".h($data->result_before_demotion)."位入線降着\">(降)</span>";
                }
                $h_result_txt.=h($data->result_number."着");
            }
        ?>
        <td class="result_number <?=h($add_class)?>"><?=$h_result_txt?></td>
        <?php if(!$mode_umm): ?>
            <td class="jockey" <?=(!$jockey->is_anonymous?'':'style="color:#999;"')?>><?=h($data->getJockeyName($page->is_editable))?></td>
        <?php endif; ?>
        <td class="handicap"><?=h($data->handicap)?></td>
        <?php if(!$mode_umm): ?>
            <td class="h_weight"><?=h($data->h_weight)?></td>
        <?php endif; ?>
        <td class="time"><?=h($data->time)?></td>
        <?php
            $a_tag=new MkTagA($data->r_name_ja?:$data->r_name_en,InAppUrl::to('horse/',['horse_id'=>$data->r_horse_id]));
        ?>
        <td class="r_horse"><?=$data->result_number==1?"({$a_tag})":$a_tag?></td>
        <td>
            <?php
                $list=[
                    !$data->race_previous_note?'':(new MkTagA('前',InAppUrl::to('race/race_previous_note.php',['race_id'=>$race->race_id])))->title("レース前メモ"),
                    !$data->race_after_note?'':(new MkTagA('後',InAppUrl::to('race/race_after_note.php',['race_id'=>$race->race_id])))->title("レース後メモ"),
                    !$data->has_jra_thisweek?'':(new MkTagA('記',InAppUrl::to('race/j_thisweek.php',['race_id'=>$race->race_id,'show_registration_only'=>($race_url_add_param?true:null)])))->title("今週の注目レース"),
                ]
            ?>
            <?=implode('｜',array_diff($list,['']))?>
        </td>
        <?php if($page->is_editable): ?>
        <td class="edit_link"><?=(new MkTagA('編',InAppUrl::to(Routes::HORSE_RACE_RESULT_EDIT,[
                    'race_id'=>$race->race_id,
                    'horse_id'=>$horse->horse_id,
                    'edit_mode'=>1,
                ])))?></td>
        <?php endif; ?>
    </tr>
    <?php if($date_order=='DESC'):// 日付降順の場合の過去未登録行の追加処理 ?>
        <?=$FUNC_print_empty_row($data->non_registered_prev_race_number,$race->race_id)?>
    <?php endif; ?>
<?php endforeach; ?>
</table>
