<?php
class RaceResultsRow extends TableRow {
    public const INT_COLUMNS=[
        'number',
        'result_number',
        'result_order',
        'result_before_demotion',
        'frame_number',
        'horse_number',
        'corner_1',
        'corner_2',
        'corner_3',
        'corner_4',
        'h_weight',
        'favourite',
        'earnings',
        'syuutoku',
        'sex',
        'is_affliationed_nar',
        'non_registered_prev_race_number',
        'jra_thisweek_horse_sort_number',
    ];
    public const STR_COLUMNS=[
        'race_id',
        'horse_id',
        'result_text',
        'jockey_name',
        'handicap',
        'time',
        'margin',
        'f_time',
        'odds',
        'tc',
        'trainer_name',
        'training_country',
        'owner_name',
        'jra_thisweek_horse_1',
        'jra_thisweek_horse_2',
        'jra_sps_comment',
    ];
    public $number =0;
    public $race_id ='';
    public $horse_id ='';
    public $result_number =null;
    public $result_order =null;
    public $result_before_demotion =null;
    public $result_text =null;
    public $frame_number =0;
    public $horse_number =0;
    public $jockey_name =null;
    public $handicap =null;
    public $time ='';
    public $margin ='';
    public $corner_1 ='';
    public $corner_2 ='';
    public $corner_3 ='';
    public $corner_4 ='';
    public $f_time =null;
    public $h_weight ='';
    public $odds =null;
    public $favourite =0;
    public $earnings =null;
    public $syuutoku =null;
    public $sex =0;
    public $tc =null;
    public $trainer_name =null;
    public $training_country ='';
    public $owner_name =null;
    public $is_affliationed_nar =0;
    public $non_registered_prev_race_number =0;
    public $jra_thisweek_horse_1 ='';
    public $jra_thisweek_horse_2 ='';
    public $jra_thisweek_horse_sort_number =null;
    public $jra_sps_comment ='';
}
