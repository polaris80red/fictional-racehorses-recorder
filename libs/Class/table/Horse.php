<?php
class Horse extends Table{
    public const TABLE = 'dat_horse';
    public const UNIQUE_KEY_COLUMN="horse_id";
    public const ROW_CLASS = HorseRow::class;

    public $record_exists = false;
    public $error_exists = false;
    public $error_msgs =[];

    public $horse_id ='';
    public $world_id =0;
    public $name_ja ='';
    public $name_en ='';
    public $birth_year =null;
    public $sex =0;
    public $color ='';
    public $tc ='';
    public $trainer_unique_name =null;
    public $training_country ='JPN';
    public $breeding_country ='';
    public $is_affliationed_nar =0;
    public $sire_id =null;
    public $sire_name ='';
    public $mare_id =null;
    public $mare_name ='';
    public $bms_name ='';
    public $is_sire_or_dam =0;
    public $meaning ='';
    public $note ='';
    public $is_enabled =1;

    public function __construct(){
    }
    /**
     * 行クラス形式用の暫定的な新しい取得処理
     */
    public static function getByHorseId(PDO $pdo, string $horse_id){
        $result = self::getByUniqueKey($pdo,'horse_id',$horse_id,PDO::PARAM_STR);
        return $result==false ? false : (new (static::ROW_CLASS))->setFromArray($result);
    }
    public function setHorseId($horse_id){
        if(exists_htmlspecialchars($horse_id)){
            $this->error_msgs[]="IDに使用できない文字（HTML）を含んでいます";
            $this->error_exists=true;
        }
        $enc_horse_id=urlencode($horse_id);
        if($horse_id!==$enc_horse_id){
            $this->error_msgs[]="IDに使用できない文字（URL直接使用不可）を含んでいます";
            $this->error_msgs[]="エンコード結果：".$enc_horse_id;
            $this->error_exists=true;
        }
        if(strpbrk($horse_id,ITEM_ID_FORBIDDEN_CHARS)){
            $this->error_msgs[]="IDに使用できない文字（その他）を含んでいます";
            $this->error_exists=true;
        }
        $this->horse_id=$horse_id;
        return $this->error_exists?false:true;
    }
    public function setDataById(PDO $pdo, string $horse_id){
        $result=self::getById($pdo,$horse_id,PDO::PARAM_STR);
        if(!$result){ return false; }

        $colmun_names=(self::ROW_CLASS)::getColumnNames();
        foreach($colmun_names as $name){
            $this->{$name} = $result[$name];
        }
        $this->record_exists=true;
        return true;
    }
    public function setDataByPost(){
        // 送信された内容をセット
        $horse_id=(string)filter_input(INPUT_POST,'horse_id');
        if(!$this->setHorseId($horse_id)){ return false; }

        $this->world_id=(int)filter_input(INPUT_POST,'world_id');
        $this->name_ja=filter_input(INPUT_POST,'name_ja');
        $this->name_en=filter_input(INPUT_POST,'name_en');
        // 生年は未定義と0を区別
        $birth_year=(string)(filter_input(INPUT_POST,'birth_year')?:filter_input(INPUT_POST,'birth_year_select'));
        $this->birth_year=$birth_year===''?null:(int)$birth_year;

        $this->sex=(int)filter_input(INPUT_POST,'sex');
        if($this->sex!==2 && $this->birth_year<=0){
            //$this->error_msgs[]="ダミー母用の牝馬以外は生年必須。";
            //$this->error_exists=true;
        }
        $this->color=(string)(filter_input(INPUT_POST,'color')?:filter_input(INPUT_POST,'color_select'));
        $this->tc=(string)(filter_input(INPUT_POST,'tc')?:filter_input(INPUT_POST,'tc_select'));
        $this->trainer_unique_name=filter_input(INPUT_POST,'trainer_unique_name')?:null;
        $this->training_country=filter_input(INPUT_POST,'training_country');
        $this->breeding_country=filter_input(INPUT_POST,'breeding_country')?:null;
        $this->is_affliationed_nar=filter_input(INPUT_POST,'is_affliationed_nar');
        $this->sire_id=filter_input(INPUT_POST,'sire_id');
        $this->sire_name=filter_input(INPUT_POST,'sire_name');
        $this->mare_id=filter_input(INPUT_POST,'mare_id');
        $this->mare_name=filter_input(INPUT_POST,'mare_name');
        $this->bms_name=filter_input(INPUT_POST,'bms_name');
        $this->is_sire_or_dam=filter_input(INPUT_POST,'is_sire_or_dam',FILTER_VALIDATE_INT);
        if(($this->sex===0||$this->sex===3)&&$this->is_sire_or_dam===1){
            $this->error_msgs[]="牡馬・牝馬以外は繁殖馬に設定できません。";
            $this->error_exists=true;
        }
        $this->meaning=filter_input(INPUT_POST,'meaning');
        $this->note=filter_input(INPUT_POST,'note');
        
        $this->is_enabled=filter_input(INPUT_POST,'is_enabled');
        return $this->error_exists?false:true;
    }
    /**
     * Insert
     */
    public function InsertExec(PDO $pdo){
        $columns=(self::ROW_CLASS)::getColumnNames();
        $sql=SqlMake::InsertSql(self::TABLE,$columns);

        if($this->horse_id==''){
            // IDがない場合生成処理
            $world=new World($pdo,$this->world_id);
            $skey_gen=new SurrogateKeyGenerator($pdo,$world->auto_id_prefix);
            $id=$skey_gen->generateId();
            if(false===(new self())->setHorseId($id)){
                $msgs[]="自動生成されたIDに使用できない文字が含まれています。";
                $msgs[]="config.inc.phpやワールド設定を確認してください。\n";;
                throw new ErrorException(implode("\n",$msgs));
                return false;
            }
            do {
                $duplicate_check_tgt=new self();
                $duplicate_check_tgt->setDataById($pdo,$id);
                if(!$duplicate_check_tgt->record_exists){
                    // 重複していなければ成功してループ離脱
                    break;
                }
                $id=$skey_gen->retryId();
            } while(true);
            $this->horse_id=$id;
        }
        $stmt = $pdo->prepare($sql);
        $stmt=$this->BindValuesFromThis($stmt,(self::ROW_CLASS)::getStrColmunNames(),PDO::PARAM_STR);
        $stmt=$this->BindValuesFromThis($stmt,(self::ROW_CLASS)::getIntColmunNames(),PDO::PARAM_INT);
        try{
            $result = $stmt->execute();
        }catch (Exception $e){
            ELog::error('競走馬登録例外エラー',$e);
            echo "<pre>"; var_dump($stmt->debugDumpParams());echo "</pre>";
            return false;
        }
        return true;
    }
    public function UpdateExec(PDO $pdo){
        $update_where_column='horse_id';
        $update_set_columns=(self::ROW_CLASS)::getColumnNames($update_where_column);

        $sql=SqlMake::UpdateSqlWhereRaw(self::TABLE,$update_set_columns, "`horse_id` LIKE :horse_id");

        $stmt = $pdo->prepare($sql);
        $stmt=$this->BindValuesFromThis($stmt,(self::ROW_CLASS)::getStrColmunNames($update_where_column),PDO::PARAM_STR);
        $stmt->bindValue(':horse_id',SqlValueNormalizer::escapeLike($this->horse_id),PDO::PARAM_STR);
        $stmt=$this->BindValuesFromThis($stmt,(self::ROW_CLASS)::getIntColmunNames(),PDO::PARAM_INT);
        $result = $stmt->execute();
        return;
    }
}
