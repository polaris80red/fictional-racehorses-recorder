<?php
class RaceRow extends TableRow {
    use TableRowValidate;
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
        'created_by',
        'updated_by',
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
        'previous_note',
        'after_note',
        'date',
        'course_type',
        'created_at',
        'updated_at',
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
    public $previous_note ='';
    public $after_note ='';
    public $number_of_starters =null;
    public $is_jra =1;
    public $is_nar =0;
    public $date =null;
    public $is_tmp_date =1;
    public $year =null;
    public $month =null;
    public $week_id =0;
    public $is_enabled =1;
    public $created_by =null;
    public $updated_by =null;
    public $created_at =null;
    public $updated_at =null;

    /**
     * インスタンスにset済みのパラメータを検証
     */
    public function validate():bool{
        if(exists_htmlspecialchars($this->race_id)){
            $this->addErrorMessage("IDに使用できない文字（HTML）を含んでいます");
        }
        $enc_race_id=urlencode($this->race_id);
        if($this->race_id!==$enc_race_id){
            $this->addErrorMessage("IDに使用できない文字（URL直接使用不可）を含んでいます");
        }
        if(strpbrk($this->race_id,ITEM_ID_FORBIDDEN_CHARS)){
            $this->addErrorMessage("IDに使用できない文字（その他）を含んでいます");
        }
        $this->validateStrLength($this->race_id,'レースID',100);
        if(empty($this->world_id)){
            $this->addErrorMessage('ワールドID未設定');
        }
        $this->validateInt($this->race_number,'レース番号',0,99);
        $this->validateStrLength($this->course_type,'コース区分',2);
        $this->validateInt($this->distance,'距離',0,10000);
        if(empty($this->race_name)){
            $this->addErrorMessage('レース名未設定');
        }
        $this->validateStrLength($this->race_name,'レース名',100);
        $this->validateStrLength($this->race_short_name,'レース略名',20);
        $this->validateStrLength($this->caption,'補足',100);
        $this->validateStrLength($this->grade,'格付',5);
        $this->validateStrLength($this->age,'馬齢条件',50);
        $this->validateStrLength($this->weather,'天候',10);
        $this->validateStrLength($this->track_condition,'馬場状態',100);
        $this->validateInt($this->number_of_starters,'頭数',0,99);
        if(strval($this->year)===''){
            $this->addErrorMessage('年未設定');
        }
        if($this->month==0){
            $this->addErrorMessage('月未設定');
        }
        $this->validateInt($this->month,'月',1,12);
        if($this->week_id==0){
            $this->addErrorMessage('週未設定');
        }
        $this->validateStrLength($this->note,'備考',100);
        $this->validateStrLength($this->previous_note,'レース前メモ',10000);
        $this->validateStrLength($this->after_note,'レース後メモ',10000);
        return !$this->hasErrors;
    }
    /**
     * $_POSTから内容をセット
     */
    public function setFromPost(){
        $this->race_id=filter_input(INPUT_POST,'race_id');
        $this->world_id=filter_input(INPUT_POST,'world_id');
        $this->race_course_name=filter_input(INPUT_POST,'race_course_name')?:filter_input(INPUT_POST,'race_course_name_select');
        $this->race_number=filter_input(INPUT_POST,'race_number')?:null;

        $this->course_type=filter_input(INPUT_POST,'course_type');
        $this->distance=filter_input(INPUT_POST,'distance');
        $this->race_name=filter_input(INPUT_POST,'race_name');
        $this->race_short_name=filter_input(INPUT_POST,'race_short_name');
        $this->caption=filter_input(INPUT_POST,'caption');

        $this->grade=filter_input(INPUT_POST,'grade')?:filter_input(INPUT_POST,'grade_select');

        $this->age_category_id=filter_input(INPUT_POST,'age_category_id');
        $this->age=filter_input(INPUT_POST,'age');

        $this->sex_category_id=filter_input(INPUT_POST,'sex_category_id');
        $this->weather=filter_input(INPUT_POST,'weather')?:filter_input(INPUT_POST,'weather_select');

        $this->track_condition=filter_input(INPUT_POST,'track_condition')?:filter_input(INPUT_POST,'track_condition_select')?:'';

        $this->note=filter_input(INPUT_POST,'note');

        $this->previous_note=filter_input(INPUT_POST,'previous_note');

        $this->after_note=filter_input(INPUT_POST,'after_note');

        $this->number_of_starters=filter_input(INPUT_POST,'number_of_starters')?:null;
        $this->is_jra=filter_input(INPUT_POST,'is_jra');
        $this->is_nar=filter_input(INPUT_POST,'is_nar');
        $date=(string)filter_input(INPUT_POST,'date');
        $datetime=$date===''?false:DateTime::createFromFormat('Y-m-d',$date);
        if($datetime){
            $this->date = $datetime->format('Y-m-d');
        }else{
            $this->date = null;
        }
        if(empty($this->year) && $datetime){
            $this->year=$datetime->format('Y');
        }
        $this->month=filter_input(INPUT_POST,'month');
        if(empty($this->month) && $datetime){
            $this->month=$datetime->format('m');
        }
        $this->is_tmp_date=$this->date!=''?filter_input(INPUT_POST,'is_tmp_date'):1;
        $this->year=filter_input(INPUT_POST,'year')?:filter_input(INPUT_POST,'year_select');
        $this->week_id=filter_input(INPUT_POST,'week_id',FILTER_VALIDATE_INT);
        $this->is_enabled=filter_input(INPUT_POST,'is_enabled');
        return;
    }
}
