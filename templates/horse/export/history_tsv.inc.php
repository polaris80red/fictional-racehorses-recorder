<?php
/**
 * スプレッドシートやExcelへのペーストを想定したタブ区切りテキスト形式で戦績を出力します
 * @var string $horse_id
 * @var string $date_order ASC|DESC
 */
$race_history=new HorseRaceHistory($pdo,$horse_id);
$race_history->setDateOrder($date_order);
$race_history->getData();

$mode_umm=false;
switch($setting->age_view_mode){
    case Setting::AGE_VIEW_MODE_UMAMUSUME:
    case Setting::AGE_VIEW_MODE_UMAMUSUME_S:
        $mode_umm = true;
    break;
}

header('Content-Type: text/plain');
$header_line=[
    $setting->horse_record_date==='umm'?'時期 ':'年月',
    '開催','距離','馬場','格付','レース名',
    $mode_umm?'人数':'頭数',
    '枠','人気','着順','騎手','斤量','タイム','1着馬(2着馬)',
];
$export_str = implode("\t",$header_line)."\n";
foreach ($race_history as $data){
    $row=[];
    $race = $data->race_row;
    $grade = $data->grade_row;
    $jockey=$data->jockey_row;
    if($data->is_registration_only){ continue; }

    $datetime=null;
    if($race->date!=null && $race->is_tmp_date==0){
        $datetime=new DateTime($race->date);
    }
    $month=$race->month;
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
    $row[]=$date_str;
    $row[]=$data->course_row->short_name??$race->race_course_name;
    $row[]=$race->course_type.$race->distance;
    $row[]=$race->track_condition;
    $row[]=($grade->short_name??'')?:$race->grade;
    $row[]=$race->race_name;
    $row[]=$race->number_of_starters;
    $row[]=$data->frame_number;
    $row[]=$data->favourite;

    $result_txt='';
    if($data->result_text!=''){
        $result_txt=$data->special_result_short_name_2?:$data->result_text;
    }else if($data->result_number > 0){
        if($data->result_before_demotion > 0){
            $result_txt.="(降)";
        }
        $result_txt.=$data->result_number."着";
    }
    $row[]=$result_txt;

    $row[]=$data->getJockeyName(true);
    $row[]=$data->handicap;
    $row[]=$data->time;

    $r_name = $data->r_name_ja?:$data->r_name_en;
    $row[]=$data->result_number==1?"($r_name)":$r_name;
    $export_str .= implode("\t",$row)."\n";
}
echo $export_str;
exit;
