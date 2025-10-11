<?php
class RaceResultsRow extends TableRow {
    use TableRowValidate;
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
        'race_previous_note',
        'race_after_note',
        'jra_thisweek_horse_1',
        'jra_thisweek_horse_2',
        'jra_sps_comment',
        'created_at',
        'updated_at',
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
    public $race_previous_note ='';
    public $race_after_note ='';
    public $jra_thisweek_horse_1 ='';
    public $jra_thisweek_horse_2 ='';
    public $jra_thisweek_horse_sort_number =null;
    public $jra_sps_comment ='';
    public $created_at =null;
    public $updated_at =null;
    /**
     * SET済みのプロパティを検証して$hasErrorsをセットする
     * @return bool エラーがなければtrue
     */
    public function validate(): bool
    {
        $this->varidateInt($this->result_number,'着順',0,99);
        $this->varidateInt($this->result_order,'表示順補正',0,99);
        $this->validateStrLength($this->result_text,'特殊結果',8);
        $this->varidateInt($this->result_before_demotion,'降着前入線順',0,99);
        if( // 着順と降着前着順が設定されていて、降着前のほうが着順が大きい
            intval($this->result_before_demotion)>0 &&
            intval($this->result_number)>0 &&
            $this->result_number<=$this->result_before_demotion
            ){
                $this->addErrorMessage("降着前着順が入力されていますが、降着で同値または着順が高くなっています\n（{$this->result_before_demotion}→{$this->result_number}）");
        }
        $this->varidateInt($this->frame_number,'枠番',0,99);
        $this->varidateInt($this->horse_number,'馬番',0,99);
        $this->validateStrLength($this->jockey_name,'騎手名',32);
        $this->validateStrLength($this->handicap,'斤量',4);
        $this->validateStrLength($this->odds,'単勝オッズ',6);
        $this->validateStrLength($this->time,'タイム',7);
        $this->validateStrLength($this->margin,'着差',5);
        
        $this->varidateInt($this->corner_1,'コーナー通過順位1',0,99);
        $this->varidateInt($this->corner_2,'コーナー通過順位2',0,99);
        $this->varidateInt($this->corner_3,'コーナー通過順位3',0,99);
        $this->varidateInt($this->corner_4,'コーナー通過順位4',0,99);
        $this->varidateInt($this->favourite,'人気',0,99);

        $this->validateStrLength($this->f_time,'上り3f(平地)／平均1f(障害)',4);
        $this->varidateInt($this->h_weight,'馬体重',0,2000);
        $this->validateStrLength($this->tc,'所属',10);
        $this->validateStrLength($this->trainer_name,'調教師名',32);
        $this->validateStrLength($this->training_country,'調教国コード',3);
        $this->validateStrLength($this->owner_name,'馬主名',50);
        
        $this->varidateInt($this->earnings,'賞金',0,null);
        $this->varidateInt($this->syuutoku,'収得賞金',0,null);
        $this->varidateInt($this->non_registered_prev_race_number,'未登録前走',0,30);

        $this->validateStrLength($this->race_previous_note,'レース前メモ',10000);
        $this->validateStrLength($this->race_after_note,'レース後メモ',10000);
        $this->validateStrLength($this->jra_thisweek_horse_1,'出走馬情報(火曜)',500);
        $this->validateStrLength($this->jra_thisweek_horse_2,'出走馬情報(木曜)',500);
        $this->validateStrLength($this->jra_sps_comment,'スペシャル出馬表紹介',200);
        return !$this->hasErrors;
    }
    /**
     * $_POSTからセットする
     */
    public function setFromPost(){
        $this->race_id=filter_input(INPUT_POST,'race_id');
        $this->horse_id=filter_input(INPUT_POST,'horse_id');
        $this->result_number = (int)filter_input(INPUT_POST,'result_number');
        if($this->result_number==0){
            $this->result_number = (int)filter_input(INPUT_POST,'result_number_select');
        }
        if($this->result_number==0){ $this->result_number==null; }
        $this->result_order=filter_input(INPUT_POST,'result_order',FILTER_VALIDATE_INT)?:null;

        $this->result_before_demotion = (int)filter_input(INPUT_POST,'result_before_demotion');
        $this->result_text = filter_input(INPUT_POST,'result_text');

        $this->frame_number = filter_input(INPUT_POST,'frame_number',FILTER_VALIDATE_INT)?:null;
        $this->horse_number = (int)filter_input(INPUT_POST,'horse_number');
        if($this->horse_number==0){
            $this->horse_number = (int)filter_input(INPUT_POST,'horse_number_select');
        }
        if($this->horse_number==0){ $this->horse_number==null; }
        $this->jockey_name = filter_input(INPUT_POST,'jockey')?:null;
        $this->handicap = filter_input(INPUT_POST,'handicap');
        $this->odds = filter_input(INPUT_POST,'odds')?:null;
        $this->favourite = (int)filter_input(INPUT_POST,'favourite');
        if($this->favourite==0){
            $this->favourite = (int)filter_input(INPUT_POST,'favourite_select');
        }
        if($this->favourite==0){ $this->favourite==null; }
        $this->time = filter_input(INPUT_POST,'time');
        $this->margin = filter_input(INPUT_POST,'margin');

        $this->corner_1 = filter_input(INPUT_POST,'corner_1',FILTER_VALIDATE_INT)?:null;
        $this->corner_2 = filter_input(INPUT_POST,'corner_2',FILTER_VALIDATE_INT)?:null;
        $this->corner_3 = filter_input(INPUT_POST,'corner_3',FILTER_VALIDATE_INT)?:null;
        $this->corner_4 = filter_input(INPUT_POST,'corner_4',FILTER_VALIDATE_INT)?:null;

        $this->f_time = filter_input(INPUT_POST,'f_time');

        $this->h_weight = filter_input(INPUT_POST,'h_weight');
        $this->earnings = (int)filter_input(INPUT_POST,'earnings',FILTER_VALIDATE_INT)?:null;
        $this->syuutoku = (int)filter_input(INPUT_POST,'syuutoku',FILTER_VALIDATE_INT)?:null;
        $this->sex = filter_input(INPUT_POST,'sex');
        $this->tc = filter_input(INPUT_POST,'tc');

        $this->trainer_name = filter_input(INPUT_POST,'trainer_name')?:null;
        $this->training_country = filter_input(INPUT_POST,'training_country');
        $this->owner_name = filter_input(INPUT_POST,'owner_name')?:null;
        $this->is_affliationed_nar = (int)filter_input(INPUT_POST,'is_affliationed_nar');
        $this->non_registered_prev_race_number = (int)filter_input(INPUT_POST,'non_registered_prev_race_number');

        $this->race_previous_note = filter_input(INPUT_POST,'race_previous_note');
        $this->race_after_note = filter_input(INPUT_POST,'race_after_note');

        $this->jra_thisweek_horse_1 = filter_input(INPUT_POST,'jra_thisweek_horse_1');
        $this->jra_thisweek_horse_2 = filter_input(INPUT_POST,'jra_thisweek_horse_2');
        $this->jra_thisweek_horse_sort_number = filter_input(INPUT_POST,'jra_thisweek_horse_sort_number');
        $this->jra_sps_comment = filter_input(INPUT_POST,'jra_sps_comment');
        return;
    }
}
