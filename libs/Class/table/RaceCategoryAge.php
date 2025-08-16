<?php
/**
 * レース条件（馬齢）
 */
class RaceCategoryAge extends Table{
    public const TABLE = 'mst_race_category_age';
    public const UNIQUE_KEY_COLUMN="id";
    protected const DEFAULT_ORDER_BY
    ='`sort_number` IS NULL, `sort_number` ASC, `id` ASC';

    public static function getShortNameById(PDO $pdo, $id){
        return (string)self::getColumnByUniqueKey($pdo, self::UNIQUE_KEY_COLUMN,$id,'short_name_2');
    }
    public static function getNameById(PDO $pdo, $id){
        return (string)self::getColumnByUniqueKey($pdo, self::UNIQUE_KEY_COLUMN,$id,'name');
    }
}
