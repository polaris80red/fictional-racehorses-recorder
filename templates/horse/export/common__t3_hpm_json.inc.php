<?php
/**
 * Tank-x3氏の「競走馬・オリウマ プロフィールメーカー」の保存データを再現したJSON形式でエクスポートします
 * https://tank-x3.github.io/Horses_Profiles_Maker/index.html
 * @var PDO $pdo
 * @var HorseRow $horse
 */
$race_history=new HorseRaceHistory($pdo,$horse->horse_id);
$race_history->setDateOrder('DESC');
$race_history->getData();

header('Content-Type: application/json');
$mWinName='';
$mWinEarnings=0;
$raceArray=[];
foreach ($race_history as $data){
    $row=[];
    $race = $data->race_row;
    $grade = $data->grade_row;
    $jockey=$data->jockey_row;
    if($data->is_registration_only==1){ continue; }

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
    $row['date']=$date_str->__toString();
    $row['course'] =$data->course_row->short_name??$race->race_course_name;
    $row['course'].=$race->race_number?"{$race->race_number}R":'';
    $row['name']=$race->race_name;
    $row['grade']=mb_convert_kana(($grade->short_name??'')?:$race->grade,'a');
    $row['distance']=$race->course_type.$race->distance.'m';
    $row['pop']=$data->favourite??'';
    $row['rank']=$data->result_number?:'';
    $row['jockey']=$data->getJockeyName();
    $row['weight']=$data->handicap??'';
    $row['odds']=$data->odds??'';
    $row['prize']=$data->earnings??'';
    $row['fans']=$data->earnings??'';
    $raceArray[]=$row;
    if($data->result_number==1 && $data->earnings >= $mWinEarnings){
        $mWinName=$race->race_name;
        $mWinEarnings=$data->earnings;
    }
}
$export_dat=[];
$birthday=(function()use($horse){
    if($horse->birth_month==''){ return ''; }
    if($horse->birth_day_of_month==''){ return ''; }
    if($horse->birth_year==''){ return ''; }
    return str_pad($horse->birth_year,4,'0',STR_PAD_LEFT).'/'.str_pad($horse->birth_month,2,'0',STR_PAD_LEFT).'/'.str_pad($horse->birth_day_of_month,2,'0',STR_PAD_LEFT);
})();
$export_dat['fictional']=[
    'horseName'=>$horse->name_ja,
    'horseNameEn'=>$horse->name_en,
    'father'=>$horse->sire_name,
    'mother'=>$horse->mare_name,
    'bms'=>$horse->bms_name,
    'sexAge'=>sex2String($horse->sex),
    'affiliationSelect'=>in_array($horse->tc,['美浦','栗東','地方','海外'])?$horse->tc:'',
    'affiliationText'=>(in_array($horse->tc,['美浦','栗東','地方','海外'])?'':($horse->tc?:'')).$horse->trainer_name,
    'owner'=>$horse->owner_name??'',
    'breeder'=>$horse->breeder_name??'',
    'totalResults'=>'',
    'totalPrize'=>'',
    'mainWin'=>$mWinName?:'',
    'birthday'=>$birthday,
    'meaning'=>$horse->meaning,
    'nextRace'=>'',
];
$sex_to_ear_list=[1=>'右',2=>'左'];
$export_dat['original']=[
    'name'=>$horse->name_ja,
    'nameEn'=>$horse->name_en,
    'ear'=>$sex_to_ear_list[$horse->sex]??'',
    'grade'=>'',
    'dormSelect'=>in_array($horse->tc,['美浦','栗東','地方','海外'])?$horse->tc:'',
    'dormText'=>'',
    'totalFans'=>'',
    'mainWin'=>$mWinName?:'',
    'birthday'=>'',
    'meaning'=>$horse->meaning??'',
    'nextRace'=>$birthday,
];
$export_dat['races']=$raceArray;
echo json_encode($export_dat,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
exit;
