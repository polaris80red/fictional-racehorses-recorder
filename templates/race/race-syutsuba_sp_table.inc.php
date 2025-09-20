<?php
/**
 * スペシャル出馬表風出馬表のテンプレート
 * @var Page $page
 * @var Setting $setting
 */
?>
<table class="syutsuba sps">
<thead>
    <tr>
        <th>枠<br>番</th>
        <th>馬<br>番</th>
        <th></th>
        <th></th>
        <th>前走</th>
        <th>前々走</th>
        <th>3走前</th>
        <th>4走前</th>
    </tr>
</thead>
<tbody>
<?php foreach ($table_data as $data):?>
<?php
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
<td><?=h($data['horse_number'])?></td>
<td style="min-width:160px;">
<?php
    $training_country=$data['training_country']?:$data['horse_training_country'];
    $horse_name_line=[];
    if(($data['is_jra']==1 || $data['is_nar']==1)){
        // 中央競馬または地方競馬の場合、調教国・生産国でカク外・マル外マークをつける
        if($training_country!='' && $training_country!='JPN'){
            // 外国調教馬にカク外表記
            $horse_name_line[]="[外]";
        }else{
            // 中央競馬の場合のみ地方所属馬と元地方所属馬のカク地・マル地マーク
            if($data['is_jra']){
                if($data['is_affliationed_nar']==1){
                    $horse_name_line[]="[地]";
                }
                if($data['is_affliationed_nar']==2){
                    $horse_name_line[]="(地)";
                }
            }
            // 外国産馬のマル外表記
            if($data['breeding_country']!='' && $data['breeding_country']!='JPN'){
                $horse_name_line[]="(外)";
            }
        }
    }
    $aTag=new MkTagA($data['name_ja']?:$data['name_en'],InAppUrl::to('horse/',['horse_id'=>$data['horse_id']]));
    $aTag->addClass('horse_name');
    $horse_name_line[]=$aTag->__toString();
    if($data['is_jra']==0 && $data['is_nar']==0){
        $horse_name_line[]="<span style=\"\"> (".($data['training_country']?:$data['horse_training_country']).")</span> ";
    }
    ?>
    <?=implode('',$horse_name_line)?><br>
    <?php
    $trainerLine=[];
    $trainer=$data['trainer_name']?:($data['horse_trainer_name']?:'□□□□');
    if($data['race_trainer_mst_is_enabled']==1){
        if($data['race_trainer_mst_is_anonymous']==1){
            $trainer=(!$page->is_editable)?'□□□□':($data['race_trainer_mst_short_name_10']?:$data['trainer']);
        }else{
            $trainer=$data['race_trainer_mst_short_name_10']?:$data['trainer_name'];
        }
    }else if($data['trainer_mst_is_enabled']==1){
        if($data['trainer_mst_is_anonymous']==1){
            $trainer=(!$page->is_editable)?'□□□□':($data['trainer_mst_short_name_10']?:$data['horse_trainer_name']);
        }else{
            $trainer=$data['trainer_mst_short_name_10']?:$data['horse_trainer_name'];
        }
    }
    $trainerLine[]=$trainer;
    if(!empty($data['tc'])){
        $trainerLine[]="（{$data['tc']}）";
    }else{
        $trainerLine[]="（{$data['horse_tc']}）";
    }
?>
<?=h(implode('',$trainerLine))?><br>
父：<?=h(($data['sire_name_ja']?:$data['sire_name_en'])?:$data['sire_name']?:"□□□□□□")?><br>
母：<?=h(($data['mare_name_ja']?:$data['mare_name_en'])?:$data['mare_name']?:"□□□□□□")?><br>
母の父：<?=h(($data['bms_name_ja']?:$data['bms_name_en'])?:($data['mare_sire_name']?:($data['bms_name']?:"□□□□□□")))?><br>
</td>
<td>
    <span class="nowrap"><?=h($data['sex_str'].$data['age']."歳")?></span><?php
if($data['color']){ print_h("/".$data['color']);}
if($data['handicap']){ print_h(" ".$data['handicap']."kg");}
?><br>
<?php
$jockey=$data['jockey_name']?:'□□□□';
if($data['jockey_mst_is_enabled']==1){
    if($data['jockey_mst_is_anonymous']==1){
        $jockey=(!$page->is_editable)?'□□□□':($data['jockey_mst_short_name_10']?:$data['jockey_name']);
    }else{
        $jockey=$data['jockey_mst_short_name_10']?:$data['jockey_name'];
    }
}
?>
<?=h($jockey)?></td>
<?php
$i=1;

// 現在のレースに未登録の空きが設定あれば空セル追加
if(!empty($data['non_registered_prev_race_number']) && $data['non_registered_prev_race_number']>0){
    for($j=0;$j<$data['non_registered_prev_race_number'];$j++){
        if($i>$rr_count){ break; }
        $i++;
        if($page->is_editable){
            $url = InAppUrl::to(Routes::HORSE_RACE_RESULT_EDIT,[
                'horse_id'=>$data['horse_id'],
                'next_race_id'=>$data['race_id'],
            ]);
            print "<td class=\"race_result_column\"><a href=\"".h($url)."\">……</a></td>\n";
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
?><td class="race_result_column <?php printResultClass($result_number); ?> <?=h($r->grade_css_class??'')?>"><?php
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
        if($setting->syutsuba_year==0 && $setting->syutsuba_date=='none'){ $date_line=' '; }
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
        echo "<span>".h($date_line)."</span>";
        //echo "<span>".$r->year.".".str_pad($r->month,2,'0',STR_PAD_LEFT).".xx"."</span>";
        $course_name = $r->race_course_name;
        if(!empty($r->race_course_short_name)){ $course_name=$r->race_course_short_name; }
        if(!empty($r->race_course_short_name_m)){ $course_name=$r->race_course_short_name_m; }
        echo "<span style=\"display:inline-block;float:right;\">".h($course_name)."</span>";
        echo "</div>\n";

        echo "<div>";
        $url=$page->getRaceResultUrl($r->race_id);
        echo "<span style=\"\"><a class=\"race_name\" href=\"".h($url)."\" title=\"".h($r->caption)."\">";
        print_h($r->race_short_name==''?$r->race_name:$r->race_short_name);
        echo "</a></span>";
        echo "</div>\n";

        echo "<div>";
        echo "<div style=\"display:inline-block;float:left;\">";
        echo "<span style=\"\" class=\"ib grade\">".h($r->grade_short_name??$r->grade)."</span><br>";
        $jockey=$r->jockey_name?:'□□□□';
        if($r->jockey_mst_is_enabled==1){
            if($r->jockey_mst_is_anonymous==1){
                $jockey=(!$page->is_editable)?'□□□□':($r->jockey_mst_short_name_10?:$r->jockey_name);
            }else{
                $jockey=$r->jockey_mst_short_name_10?:$r->jockey_name;
            }
        }
        echo "<span>".h($jockey)."</span>";
        echo "</div>";
        echo "<span class=\"result_number\" style=\"display:inline-block;float:right;\">";
        echo h($r->result_text?($r->special_result_short_name_2?:$r->result_text):$r->result_number)?:"&nbsp;";
        echo "</span>";
        echo "</div>\n";
        echo "<div style=\"clear:both;\">\n";
        echo "<div>";
        echo "<span style=\"display:inline-block;float:left;\">".h($r->course_type.$r->distance)."</span>";
        echo "<span style=\"display:inline-block;float:right;\">";
        echo h($r->track_condition);
        echo h($r->time?'　'.$r->time:'');
        echo "</span>";
        echo "</div>\n";
        echo "<div style=\"clear:both;\">\n";
        if(!empty($r->winner_or_runner_up['horse_id'])){
            echo '<a href="'.$page->to_app_root_path.'horse/?horse_id='.h($r->winner_or_runner_up['horse_id']).'">';
            echo h($r->winner_or_runner_up['name_ja']?:$r->winner_or_runner_up['name_en']);
            echo '</a>';
        }else{
            echo '&nbsp;';
        }
        echo "</div>\n";
        echo "</div>\n";
    }
    ?></td><?php
    if(!empty($r->non_registered_prev_race_number) && $r->non_registered_prev_race_number>0){
        for($j=0;$j<$r->non_registered_prev_race_number;$j++){
            if($i>$rr_count){ break; }
            $i++;
            if($page->is_editable){
                $url = InAppUrl::to(Routes::HORSE_RACE_RESULT_EDIT,[
                    'horse_id'=>$data['horse_id'],
                    'next_race_id'=>$r->race_id,
                ]);
                print "<td class=\"race_result_column\"><a href=\"".h($url)."\">……</a></td>\n";
            }else{
                print "<td class=\"race_result_column\">……</td>\n";
            }
        }
    }
}
?>
</tr>
</tbody>
<?php endforeach;?>
</table>