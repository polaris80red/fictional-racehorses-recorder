<?php
class HorseRaceHistoryRow{
    const IMPORT_PARAM_NAMES=[
        'race_id',
        'year',
        'month',
        'week_id',
        'race_course_name',
        'race_number',
        'course_type',
        'distance',
        'grade',
        'race_name',
        'race_short_name',
        'track_condition',

        'number_of_starters',
        'favourite',
        'result_number',
        'result_before_demotion',
        'result_text',
        'frame_number',
        'horse_number',
        'handicap',

        'date',
        'is_tmp_date',
        'w_month',
        'umm_month_turn',
        'grade_short_name',
        'grade_css_class_suffix',
        'is_registration_only',
        'non_registered_prev_race_number',
    ];

    // レース結果詳細テーブルのパラメータ
    public $result_number;  // 着順
    public $result_before_demotion;    // 降着馬入線順
    public $result_text;    // 特殊着順
    public $frame_number;
    public $horse_number;
    public $handicap;       // 斤量
    public $favourite;      // 単勝人気
    public $is_registration_only;
    public $non_registered_prev_race_number;

    // レース結果テーブルに存在する列
    public $race_id;
    public $world_id;
    public $race_course_name;
    public $race_number;
    public $course_type;
    public $distance;
    public $race_name;
    public $race_short_name;
    public $caption;
    public $grade;
    public $age_category_id;
    public $age;
    public $sex_category_id;
    public $track_condition;
    public $note;
    public $number_of_starters;
    public $is_jra;
    public $is_nar;
    public $date;
    public $is_tmp_date;
    public $year;
    public $month;
    public $week_id;
    public $sort_number;
    public $is_enabled;

    // 週マスタから使用するデータ
    public $w_month;
    public $umm_month_turn;

    // グレードマスタから使用するデータ
    public $grade_short_name;
    public $grade_css_class_suffix;


    // 1・2着馬
    public $r_horse_id;
    public $r_name_ja;
    public $r_name_en;

    public $has_jra_thisweek=false;

    // 指定したパラメータだけを取り込む
    public function setByArray(array $row_data){
        $import_params=array_fill_keys(self::IMPORT_PARAM_NAMES,0);
        $row_data=array_intersect_key($row_data,$import_params);
        foreach($row_data as $key => $value){
            $this->$key=$value;
        }
    }
}
