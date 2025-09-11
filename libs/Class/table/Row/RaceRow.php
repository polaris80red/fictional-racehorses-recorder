<?php
class RaceRow extends TableRow {
    public const INT_COLUMNS=[
        'world_id',
        'race_number',
        'distance',
        'age_category_id',
        'sex_category_id',
        'number_of_starters',
        'is_jra',
        'is_nar',
        'is_tmp_date',
        'year',
        'month',
        'week_id',
        'is_enabled',
    ];
    public const STR_COLUMNS=[
        'race_id',
        'race_course_name',
        'race_name',
        'race_short_name',
        'caption',
        'grade',
        'age',
        'weather',
        'track_condition',
        'note',
        'date',
        'course_type',
    ];
    public $race_id ='';
    public $world_id =null;
    public $race_course_name ='';
    public $race_number =null;
    public $course_type ='';
    public $distance =null;
    public $race_name ='';
    public $race_short_name ='';
    public $caption ='';
    public $grade ='';
    public $age_category_id =0;
    public $age ='';
    public $sex_category_id =0;
    public $weather =null;
    public $track_condition ='';
    public $note ='';
    public $number_of_starters =null;
    public $is_jra =1;
    public $is_nar =0;
    public $date ='';
    public $is_tmp_date =1;
    public $year =null;
    public $month =null;
    public $week_id =0;
    public $is_enabled =1;
}
