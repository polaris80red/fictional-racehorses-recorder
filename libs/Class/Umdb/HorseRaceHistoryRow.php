<?php
class HorseRaceHistoryRow{
    const IMPORT_PARAM_NAMES=[
        'race_id',

        'favourite',
        'odds',
        'earnings',
        'syuutoku',
        'result_number',
        'result_order',
        'result_before_demotion',
        'result_text',
        'frame_number',
        'horse_number',
        'jockey_name',
        'handicap',
        'time',
        'margin',
        'corner_1',
        'corner_2',
        'corner_3',
        'corner_4',
        'f_time',
        'h_weight',
        
        'tc',
        'trainer_name',
        'training_country',
        'sex',
        'is_affliationed_nar',

        'w_month',
        'umm_month_turn',
        'is_registration_only',
        'non_registered_prev_race_number',
    
        'special_result_short_name_2',
    ];

    // レース結果詳細テーブルのパラメータ
    public $race_id;

    public $result_number;  // 着順
    public $result_order;  // 着順補正
    public $result_before_demotion;    // 降着馬入線順
    public $result_text;    // 特殊着順
    public $frame_number;
    public $horse_number;
    public $jockey_name; // 騎手
    public $handicap;       // 斤量
    public $time;       // タイム
    public $margin;
    public $corner_1;
    public $corner_2;
    public $corner_3;
    public $corner_4;
    public $f_time;       // 上り
    public $h_weight;       // 馬体重
    public $favourite;      // 単勝人気
    public $odds;      // 単勝オッズ
    public $is_registration_only;
    public $earnings;
    public $syuutoku;
    public $non_registered_prev_race_number;
    
    public $tc;
    public $trainer_name;
    public $training_country;
    public $sex;
    public $is_affliationed_nar;

    // 週マスタから使用するデータ
    public $w_month;
    public $umm_month_turn;

    // 1・2着馬
    public $r_horse_id;
    public $r_name_ja;
    public $r_name_en;

    public $has_jra_thisweek=false;
    
    // 特殊結果のマスタ版略称
    public $special_result_short_name_2;

    public JockeyRow $jockey_row;
    public RaceRow $race_row;
    public RaceGradeRow $grade_row;
    public RaceCourseRow $course_row;

    // 指定したパラメータだけを取り込む
    public function setByArray(array $row_data){
        $import_params=array_fill_keys(self::IMPORT_PARAM_NAMES,0);
        $row_data=array_intersect_key($row_data,$import_params);
        foreach($row_data as $key => $value){
            $this->$key=$value;
        }
    }
    /**
     * 騎手名を取得
     */
    public function getJockeyName(bool $show_anonymous = false){
        $jockey=$this->jockey_row;
        $jockey_name=$this->jockey_name;
        if($jockey->is_enabled===1){
            if($jockey->is_anonymous==1 && $show_anonymous===false){
                // 匿名フラグがオンで、匿名レコードを表示しない場合は伏せる
                $jockey_name='□□□□';
            }else{
                $jockey_name = $jockey->short_name_10?:$this->jockey_name;
            }
        }
        return (string)$jockey_name;
    }
}
