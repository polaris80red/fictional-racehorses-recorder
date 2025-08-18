<?php
class Horse extends Table{
    public const TABLE = 'dat_horse';
    public const UNIQUE_KEY_COLUMN="horse_id";

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
    public $affiliation_id =null;
    public $tc ='';
    public $training_country ='JPN';
    public $is_affliationed_nar =0;
    public $sire_id =null;
    public $sire_name ='';
    public $mare_id =null;
    public $mare_name ='';
    public $bms_name ='';
    public $is_sire_or_dam =0;
    public $meaning ='';
    public $note ='';
    public $search_text ='';
    public $sort_number =null;
    public $is_enabled =1;

    public function __construct(){
    }
    public function setHorseId($horse_id){
        /*
        if(empty($horse_id)){
            $this->error_msgs[]="競走馬ID未入力";
            $this->error_exists=true;
            return false;
        }
        */
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
        $this->horse_id=$horse_id;
        return $this->error_exists?false:true;
    }
    public function setDataById(PDO $pdo, string $horse_id){
        $result=self::getById($pdo,$horse_id,PDO::PARAM_STR);
        if(!$result){ return false; }
        $result = (object)$result;
        $this->horse_id = $result->horse_id;
        $this->world_id = $result->world_id;
        $this->name_ja = $result->name_ja;
        $this->name_en = $result->name_en;
        $this->birth_year = $result->birth_year;
        $this->sex = $result->sex;
        $this->color = $result->color;
        $this->birth_year = $result->birth_year;
        $this->affiliation_id = $result->affiliation_id;
        $this->tc = $result->tc;
        $this->training_country = $result->training_country;
        $this->is_affliationed_nar = $result->is_affliationed_nar;
        $this->sire_id = $result->sire_id;
        $this->sire_name = $result->sire_name;
        $this->mare_id = $result->mare_id;
        $this->mare_name = $result->mare_name;
        $this->bms_name = $result->bms_name;
        $this->is_sire_or_dam = $result->is_sire_or_dam;
        $this->meaning = $result->meaning;
        $this->note = $result->note;
        $this->search_text = $result->search_text;
        $this->sort_number = $result->sort_number;
        $this->is_enabled = $result->is_enabled;

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
        $this->affiliation_id=filter_input(INPUT_POST,'affiliation_id');
        $this->tc=(string)(filter_input(INPUT_POST,'tc')?:filter_input(INPUT_POST,'tc_select'));
        $this->training_country=filter_input(INPUT_POST,'training_country');
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
        $search_texts=explodeAndTrim(filter_input(INPUT_POST,'search_text'));
        $this->search_text=implode(' ',$search_texts);
        
        $this->sort_number=intOrNull(filter_input(INPUT_POST,'sort_number'));
        $this->is_enabled=filter_input(INPUT_POST,'is_enabled');
        return $this->error_exists?false:true;
    }
    /**
     * Insert
     */
    public function InsertExec(PDO $pdo){
        $columns=['horse_id','world_id','name_ja','name_en','birth_year','sex','color'
        ,'affiliation_id','tc','training_country','is_affliationed_nar'
        ,'sire_id','sire_name','mare_id','mare_name','bms_name','is_sire_or_dam','meaning','note','search_text','sort_number','is_enabled'];
        $sql=SqlMake::InsertSql(self::TABLE,$columns);

        if($this->horse_id==''){
            // IDがない場合生成処理
            $world=new World($pdo,$this->world_id);
            $skey_gen=new SurrogateKeyGenerator($pdo,$world->auto_id_prefix);
            $id=$skey_gen->generateId();
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
        $stmt = $this->BindValues($stmt);

        try{
            $result = $stmt->execute();
        }catch (Exception $e){
            Elog::error('競走馬登録例外エラー',$e);
            echo "<pre>"; var_dump($stmt->debugDumpParams());echo "</pre>";
            return false;
        }
        return true;
    }
    public function UpdateExec(PDO $pdo){
        $update_columns=['world_id','name_ja','name_en','birth_year','sex','color'
            ,'affiliation_id','tc','training_country','is_affliationed_nar'
            ,'sire_id','sire_name','mare_id','mare_name','bms_name','is_sire_or_dam','meaning','note','search_text','sort_number','is_enabled'];

        $sql=SqlMake::UpdateSqlWhereRaw(self::TABLE,$update_columns, "`horse_id` LIKE :horse_id");

        $stmt = $pdo->prepare($sql);
        $stmt = $this->BindValues($stmt);
        $result = $stmt->execute();
        return;
    }
    private function BindValues($stmt){
        $stmt->bindValue(":affiliation_id",($this->affiliation_id?:null),PDO::PARAM_INT);
        $sort_number=(strval($this->sort_number)==='')?null:(int)$this->sort_number;
        $stmt->bindValue(":sort_number",$sort_number,PDO::PARAM_INT);
        $stmt=$this->BindValuesFromThis($stmt, [
            'horse_id','name_ja','name_en','color','tc','training_country',
            'sire_id','sire_name','mare_id','mare_name','bms_name','meaning','note','search_text'
        ],PDO::PARAM_STR);
        $stmt=$this->BindValuesFromThis($stmt, [
            'world_id','birth_year','sex','is_affliationed_nar','is_sire_or_dam','is_enabled'
        ],PDO::PARAM_INT);
        return $stmt;
    }
}
