<?php
class Horse extends Table{
    public const TABLE = 'dat_horse';
    public const UNIQUE_KEY_COLUMN="horse_id";
    public const ROW_CLASS = HorseRow::class;
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
