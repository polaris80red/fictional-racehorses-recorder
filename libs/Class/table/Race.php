<?php
class Race extends Table{
    public const TABLE = 'dat_race';
    public const ROW_CLASS = RaceRow::class;

    public $record_exists=false;
    public $error_exists = false;
    public $error_msgs =[];

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
    public $date ='';
    public $is_tmp_date =1;
    public $year =null;
    public $month =null;
    public $week_id =0;
    public $is_enabled =1;
    public $created_at =null;
    public $updated_at =null;

    public const UMM_MONTH_TURN_NAME = [
        0=>'',
        1=>'前半',
        2=>'後半',
    ];

    public const UNIQUE_KEY_COLUMN="race_id";
    public const COLUMNS=[
        'world_id','race_course_name','race_number',
        'course_type','distance','race_name','race_short_name','caption',
        'grade','age_category_id','age','sex_category_id','weather','track_condition',
        'note',
        'previous_note',
        'after_note',
        'number_of_starters','is_jra','is_nar',
        'date','is_tmp_date','year','month','week_id',
        'is_enabled'
        ];
    
    public function __construct(PDO|null $pdo=null, string $race_id=''){
        if(!is_null($pdo)&&$race_id!==''){
            $this->setDataById($pdo,$race_id);
        }
    }
    /**
     * @param string $race_id
     */
    public function setRaceId($race_id){
        if(exists_htmlspecialchars($race_id)){
            $this->error_msgs[]='IDに使用できない文字（HTML）を含んでいます';
            $this->error_exists=true;
        }
        $enc_race_id=urlencode($race_id);
        if($race_id!==$enc_race_id){
            $this->error_msgs[]='IDに使用できない文字（URL直接使用不可）を含んでいます';
            $this->error_msgs[]="エンコード結果：".$enc_race_id;
            $this->error_exists=true;
        }
        if(strpbrk($race_id,ITEM_ID_FORBIDDEN_CHARS)){
            $this->error_msgs[]="IDに使用できない文字（その他）を含んでいます";
            $this->error_exists=true;
        }
        $this->validateLength($race_id,'レースID',100);
        $this->race_id=$race_id;
        return $this->error_exists?false:true;
    }
    public function setDataById(PDO $pdo, string $race_id){
        if(!$this->setRaceId($race_id)){
            return false;
        }
        $result=self::getById(
            $pdo,$this->race_id,PDO::PARAM_STR);

        if(!$result){
            return false;
        }
        $result = (object)$result;
        $columns=(self::ROW_CLASS)::getColumnNames();
        foreach($columns as $column){
            $this->{$column}=$result->{$column};
        }
        $this->record_exists=true;
        return true;
    }
    public function setDataByPost(){
        // 送信された内容をセット
        $race_id=filter_input(INPUT_POST,'race_id');
        if($race_id!=='' && !$this->setRaceId($race_id)){return false;}
        $this->world_id=filter_input(INPUT_POST,'world_id');
        if(empty($this->world_id)){
            $this->error_msgs[]='ワールドID未設定';
            $this->error_exists=true;
        }
        $this->race_course_name=filter_input(INPUT_POST,'race_course_name')?:filter_input(INPUT_POST,'race_course_name_select');
        $this->race_number=filter_input(INPUT_POST,'race_number')?:null;

        $this->course_type=filter_input(INPUT_POST,'course_type');
        $this->distance=filter_input(INPUT_POST,'distance');
        $this->race_name=filter_input(INPUT_POST,'race_name');
        if(empty($this->race_name)){
            $this->error_msgs[]='レース名未設定';
            $this->error_exists=true;
        }
        $this->race_short_name=filter_input(INPUT_POST,'race_short_name');

        $this->caption=filter_input(INPUT_POST,'caption');

        $this->grade=filter_input(INPUT_POST,'grade')?:filter_input(INPUT_POST,'grade_select');

        $this->age_category_id=filter_input(INPUT_POST,'age_category_id');
        $this->age=filter_input(INPUT_POST,'age');

        $this->sex_category_id=filter_input(INPUT_POST,'sex_category_id');
        $this->weather=filter_input(INPUT_POST,'weather')?:filter_input(INPUT_POST,'weather_select');

        $this->track_condition=filter_input(INPUT_POST,'track_condition')?:filter_input(INPUT_POST,'track_condition_select');

        $this->note=filter_input(INPUT_POST,'note');

        $this->previous_note=filter_input(INPUT_POST,'previous_note');

        $this->after_note=filter_input(INPUT_POST,'after_note');

        $this->number_of_starters=filter_input(INPUT_POST,'number_of_starters');
        $this->is_jra=filter_input(INPUT_POST,'is_jra');
        $this->is_nar=filter_input(INPUT_POST,'is_nar');
        $this->date=filter_input(INPUT_POST,'date');
        $datetime=null;
        if($this->date!=''){
            $datetime=new dateTime($this->date);
        }
        $this->is_tmp_date=$this->date!=''?filter_input(INPUT_POST,'is_tmp_date'):1;
        $this->year=filter_input(INPUT_POST,'year')?:filter_input(INPUT_POST,'year_select');
        if(empty($this->year)){
            if($datetime!==null){
                $this->year=$datetime->format('Y');
            }else{
                $this->error_msgs[]='年度未設定';
                $this->error_exists=true;
            }
        }
        $this->month=filter_input(INPUT_POST,'month');
        if(empty($this->month)){
            if($datetime!==null){
                $this->month=$datetime->format('m');
            }else{
                $this->error_msgs[]='月未設定';
                $this->error_exists=true;
            }
        }
        $this->week_id=filter_input(INPUT_POST,'week_id',FILTER_VALIDATE_INT);
        if($this->week_id==0){
            $this->error_msgs[]='週未設定';
            $this->error_exists=true;
        }

        $this->is_enabled=filter_input(INPUT_POST,'is_enabled');
        $this->varidate();
        return $this->error_exists?false:true;
    }
    public function varidate(){
        $this->validateLength($this->course_type,'コース区分',2);
        $this->validateLength($this->race_name,'レース名',100);
        $this->validateLength($this->race_short_name,'レース略名',20);
        $this->validateLength($this->caption,'補足',100);
        $this->validateLength($this->grade,'格付',5);
        $this->validateLength($this->age,'馬齢条件',50);
        $this->validateLength($this->weather,'天候',10);
        $this->validateLength($this->track_condition,'馬場状態',100);
        $this->validateLength($this->note,'備考',100);
        $this->validateLength($this->previous_note,'レース前メモ',10000);
        $this->validateLength($this->after_note,'レース後メモ',10000);
        return !$this->error_exists;
    }
    /**
     * Insert
     */
    public function InsertExec(PDO $pdo){
        $insert_columns=(self::ROW_CLASS)::getColumnNames();
        $sql=SqlMake::InsertSql(self::TABLE,$insert_columns);
        if($this->race_id==''){
            // IDがない場合生成処理
            $world=World::getById($pdo,$this->world_id);
            if($world===false){
                $msgs[]="id'{$this->world_id}'のワールドが見つかりません。";
                throw new ErrorException(implode("\n",$msgs));
                return false;
            }
            $skey_gen=new SurrogateKeyGenerator($pdo,$world->auto_id_prefix);
            $id=$skey_gen->generateId();
            if(false===(new self())->setRaceId($id)){
                $msgs[]="自動生成されたIDに使用できない文字が含まれています。";
                $msgs[]="config.inc.phpやワールド設定を確認してください。\n";;
                throw new ErrorException(implode("\n",$msgs));
                return false;
            }
            do {
                $duplicate_check_tgt=new self($pdo,$id);
                if(!$duplicate_check_tgt->record_exists){
                    // 重複していなければ成功してループ離脱
                    break;
                }
                $id=$skey_gen->retryId();
            } while(true);
            $this->race_id=$id;
        }
        $stmt = $pdo->prepare($sql);
        $stmt = $this->BindValues($stmt);
        $stmt->bindValue(':race_id',$this->race_id,PDO::PARAM_STR);
        $stmt->bindValue(':created_at',$this->created_at?:null,PDO::PARAM_STR);
        $result = $stmt->execute();
        return;
    }
    public function UpdateExec(PDO $pdo){
        $sql=SqlMake::UpdateSqlWhereRaw(
            self::TABLE,
            (self::ROW_CLASS)::getColumnNames([self::UNIQUE_KEY_COLUMN,'created_at']),
            "`".self::UNIQUE_KEY_COLUMN."` LIKE :".self::UNIQUE_KEY_COLUMN);
        $stmt = $pdo->prepare($sql);
        $stmt = $this->BindValues($stmt);
        $stmt->bindValue(':race_id',SqlValueNormalizer::escapeLike($this->race_id),PDO::PARAM_STR);
        $result = $stmt->execute();
        return;
    }
    /**
     * 処理によって調整が必要な箇所以外をバインドする
     */
    private function BindValues($stmt){
        $stmt=$this->BindValuesFromThis($stmt, (self::ROW_CLASS)::getStrColmunNames([
            'race_course_name','grade','weather','track_condition','date','updated_at',
            'created_at', // 処理によってバインドしないため除外するカラム
        ]),PDO::PARAM_STR);
        $stmt=$this->BindValuesFromThis($stmt, (self::ROW_CLASS)::getIntColmunNames([
            'number_of_starters'
        ]),PDO::PARAM_INT);
        // NULLかどうかの個別調整
        $stmt->BindValue(':race_course_name',$this->race_course_name?:'',PDO::PARAM_STR);
        $stmt->BindValue(':grade',$this->grade?:'',PDO::PARAM_STR);
        $stmt->BindValue(':weather',$this->weather?:null,PDO::PARAM_STR);
        $stmt->BindValue(':track_condition',$this->track_condition?:'',PDO::PARAM_STR);
        // 日付が空の場合はNULL
        if($this->date!=''){
            $stmt->BindValue(':date',$this->date,PDO::PARAM_STR);
        }else{
            $stmt->BindValue(':date',null);
        }
        // 頭数0の場合はNULL
        $stmt->BindValue(':number_of_starters',intOrNullIfZero($this->number_of_starters),PDO::PARAM_INT);
        $stmt->BindValue(':updated_at',$this->updated_at?:null,PDO::PARAM_STR);
        return $stmt;
    }
}
