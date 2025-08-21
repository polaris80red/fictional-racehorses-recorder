<?php
class RaceWeek extends Table{
    public const TABLE = 'mst_race_week';
    public const UNIQUE_KEY_COLUMN="id";
    protected const DEFAULT_ORDER_BY ='`sort_number` IS NULL, `sort_number` ASC, `id` ASC';
    public const ROW_CLASS = RaceWeekRow::class;

    public $one_page_record_num=60;

    public static function getById(PDO $pdo, $id, $pdo_param_mode=PDO::PARAM_STR){
        $result = self::getByUniqueKey($pdo,'id',$id,PDO::PARAM_INT);
        if($result==false){
            return false;
        }
        return (new (static::ROW_CLASS))->setFromArray($result);
    }
}
