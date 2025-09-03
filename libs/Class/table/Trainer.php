<?php
class Trainer extends Table{
    public const TABLE = 'mst_trainer';
    public const UNIQUE_KEY_COLUMN="id";
    protected const DEFAULT_ORDER_BY ='`id` ASC';
    public const ROW_CLASS = TrainerRow::class;

    public static function getById(PDO $pdo, $id, $pdo_param_mode=PDO::PARAM_STR){
        $result = self::getByUniqueKey($pdo,'id',$id,PDO::PARAM_INT);
        if($result==false){
            return false;
        }
        return (new (static::ROW_CLASS))->setFromArray($result);
    }
    public static function getByUniqueName(PDO $pdo, $id){
        $result = self::getByUniqueKey($pdo,'unique_name',$id,PDO::PARAM_STR);
        if($result==false){
            return false;
        }
        return (new (static::ROW_CLASS))->setFromArray($result);
    }
}
