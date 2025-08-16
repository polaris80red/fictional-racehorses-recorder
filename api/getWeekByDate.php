<?php
/**
 * 日付から1年間の中の開催週を判定
 */
require_once dirname(__DIR__).'/libs/init.php';
header('Content-Type: text/plain; charset=UTF-8');
$pdo=getPDO();
$input_date=filter_input(INPUT_GET,'date');
if($input_date==''){ exit; }
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
        echo 1;
        exit;
    }else if($day<=6){
        // それ以外は1/6までを金杯週扱い
        echo 1;
        exit;
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
// 有馬記念が52になる週数に補正
$diff=$date->diff($arima_date);
$diff_days=$diff->days;
$diff_week=$diff_days/7;
$result=52-floor($diff_week+0.5);
// 1～52から外れる値は1と52に補正
echo max(min($result,52),1);
