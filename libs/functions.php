<?php
function ifZero2Null($var){
    if($var==0){ return null; }
    return $var;
}
function ifZero2Empty($var){
    if($var==0){ return ''; }
    return $var;
}
function printResultClass($int){
    print getResultClass($int);
}
function tmpModifyFormat(DateTime $dateTime,string $modify,string $format=''){
    $tmpDateTime = clone $dateTime;
    $tmpDateTime->modify($modify);
    $result = $tmpDateTime->format($format);
    unset($tmpDateTime);
    return $result;
}
function getWeekDayJa($int){
    $week = ['日','月','火','水','木','金','土'];
    return $week[$int];
}
function sex2String($input, int $length_format=1){
    if($input==3){
        if($length_format===1){ return "セ"; }
        if($length_format===2){ return "せん"; }
    }
    switch((int)$input){
        case 1:
            return "牡";
            break;
        case 2:
            return "牝";
            break;
    }
    return "";
}
function sexTo1Char($v){
    if($v==1){
        return "牡";
    }else if($v==2){
        return "牝";
    }else if($v==3){
        return "セ";
    }
    return "";
}
function getResultClass($int){
    switch($int){
        case 1:
            $add_class='result_1st';break;
        case 2:
            $add_class='result_2nd';break;
        case 3:
            $add_class='result_3rd';break;
        default:
            $add_class="";
        }
    return $add_class;
}
function printGradeClass($grade){ print getGradeClass($grade);}
function getGradeClass($grade){
    switch($grade){
        case "G1":
            $add_class='block_g1';break;
        case "G2":
            $add_class='block_g2';break;
        case "G3":
            $add_class='block_g3';break;
        case "L":
            $add_class='block_ls';break;
        case "OP":
            $add_class='block_op';break;
        default:
            $add_class="";
        }
    return $add_class;
}
/**
* 馬IDで1件取得
*
* @pdo PDO
* @horse_id strng 競走馬ID
*/
function getHorseById($pdo, string $horse_id){
    $sql="SELECT * FROM `".Horse::TABLE."` WHERE `horse_id` LIKE :horse_id LIMIT 1;";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':horse_id', $horse_id, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function explodeAndTrim(string $string,array $sub_separaters=["　","\r","\n"]){
    $explode_array = explode(' ',str_replace($sub_separaters," ",$string));
    $results=[];
    foreach($explode_array as $val){
        $val=trim($val);
        if($val===''){ continue; }
        $results[]=$val;
    }
    return $results;
}
/**
 * 日付から週を取得
 */
function getWeekByDate($input_date){
    $date=new DateTime($input_date);
    $year=(int)$date->format('Y');
    $month=(int)$date->format('n');
    $day=(int)$date->format('j');

    // 東京大賞典などは最終週に補正
    if($month===12 && $day>28){
        echo 52;
        exit;
    }
    // 金杯周辺を補正
    if($month===1){
        $d0106_weekday = (new DateTime("$year-01-06"))->format('w');
        if($d0106_weekday==0 && $day<6){
            // 1/5(土)金杯で1/6(日)シンザン記念の場合は1/5まで金杯週扱い
            return 1;
        }else if($day<=6){
            // それ以外は1/6までを金杯週扱い
            return 1;
        }
    }

    // 「次の日曜日」に補正する
    $date_w=$date->format('w');
    if($date_w>1){
        $to_next_sunday=7-$date_w;
        $date->modify("+{$to_next_sunday} day");
    }else if($date_w==1){
        // ただし月曜日は祝日開催用に前の日曜日の週に所属させる
        $date->modify("-1 day");
    }

    // 同じ年の有馬記念（12/28までの最後の日曜日を取得）
    $arima_date = new DateTime("$year-12-28");
    while ($arima_date->format('w') != 0) {
        $arima_date->modify('-1 day');
    }
    // 有馬記念より後は日数差に関係なく第52週に補正
    if($date>$arima_date){
        return 52;
    }
    // 有馬記念が52になる週数に補正
    $diff=$date->diff($arima_date);
    $diff_days=$diff->days;
    $diff_week=$diff_days/7;
    $result=52-floor($diff_week+0.5);
    // 1～52から外れる値は1と52に補正
    return max(min($result,52),1);
}