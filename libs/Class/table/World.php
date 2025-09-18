<?php
class World extends Table{
    public const TABLE = 'mst_world';
    public const UNIQUE_KEY_COLUMN="id";
    protected const DEFAULT_ORDER_BY='`sort_priority` DESC, `sort_number` IS NULL, `sort_number` ASC, `id` ASC';
    public const ROW_CLASS = WorldRow::class;

    public static function getById(PDO $pdo, $id, $pdo_param_mode=PDO::PARAM_INT){
        $result = self::getByUniqueKey($pdo,'id',$id,$pdo_param_mode);
        if($result==false){
            return false;
        }
        return (new (static::ROW_CLASS))->setFromArray($result);
    }
}
