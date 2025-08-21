<?php
/**
 * レース条件（性別）
 */
class RaceCategorySex extends Table{
    public const TABLE = 'mst_race_category_sex';
    public const UNIQUE_KEY_COLUMN="id";
    protected const DEFAULT_ORDER_BY ='`sort_number` IS NULL, `sort_number` ASC, `id` ASC';
    public const ROW_CLASS = RaceCategorySexRow::class;

    public static function getShortNameById(PDO $pdo, $id){
        return (string)self::getColumnByUniqueKey($pdo, self::UNIQUE_KEY_COLUMN,$id,'short_name_3');
    }
    public static function getById(PDO $pdo, $id, $pdo_param_mode=PDO::PARAM_STR){
        $result = self::getByUniqueKey($pdo,'id',$id,PDO::PARAM_INT);
        if($result==false){
            return false;
        }
        return (new (static::ROW_CLASS))->setFromArray($result);
    }
}
