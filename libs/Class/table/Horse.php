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
    public $birth_month=null;
    public $birth_day_of_month=null;
    public $sex =0;
    public $color ='';
    public $tc ='';
    public $trainer_name =null;
    public $training_country ='JPN';
    public $owner_name =null;
    public $breeder_name =null;
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
    public $profile ='';
    public $is_enabled =1;
    public $created_at =null;
    public $updated_at =null;

    public function __construct(){
    }
    /**
     * 行クラス形式用の暫定的な新しい取得処理
     */
    public static function getByHorseId(PDO $pdo, string $horse_id){
        $result = self::getByUniqueKey($pdo,'horse_id',$horse_id,PDO::PARAM_STR);
        return $result==false ? false : (new (static::ROW_CLASS))->setFromArray($result);
    }
    /**
     * Insert実行（ID生成処理を割り込み）
     * @param HorseRow $row_obj
     * @param bool $exclude_unique_key ※ クラス継承のため付与しているが使用しない
     */
    public static function InsertFromRowObj(PDO $pdo, TableRow $row_obj, bool $exclude_unique_key=true){
        if($row_obj->horse_id==''){
            // IDがない場合生成処理
            $world=World::getById($pdo,$row_obj->world_id);
            if($world===false){
                $msgs[]="id'{$row_obj->world_id}'のワールドが見つかりません。";
                throw new ErrorException(implode("\n",$msgs));
                return false;
            }
            $skey_gen=new SurrogateKeyGenerator($pdo,$world->auto_id_prefix);
            $id=$skey_gen->generateId();
            $id_check_obj=clone $row_obj;
            $id_check_obj->horse_id=$id;
            if(false===$id_check_obj->validate()){
                $msgs[]="自動生成されたIDに使用できない文字が含まれています。";
                $msgs[]="config.inc.phpやワールド設定を確認してください。\n";
                throw new ErrorException(implode("\n",$msgs));
                return false;
            }
            do {
                $duplicate_check_tgt=self::getByHorseId($pdo,$id);
                if(!$duplicate_check_tgt){
                    // 重複していなければ成功してループ離脱
                    break;
                }
                $id=$skey_gen->retryId();
            } while(true);
            $row_obj->horse_id=$id;
        }
        parent::InsertFromRowObj($pdo,$row_obj,false);
    }
}
