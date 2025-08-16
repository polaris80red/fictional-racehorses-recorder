<?php
class RaceGrade extends Table{
    public const TABLE = 'mst_race_grade';
    public const UNIQUE_KEY_COLUMN="race_results_key";
    protected const DEFAULT_ORDER_BY
    ='`sort_number` IS NULL, `sort_number` ASC, `id` ASC';

    public static function getByRaceResultsKey(PDO $pdo, $id){
        $result = self::getByUniqueKey($pdo,'race_results_key',$id,PDO::PARAM_STR);
        if($result==false){
            return false;
        }
        $obj=new RaceGradeRow();
        $columns=RaceGradeRow::getRowNames();
        foreach($columns as $col){
            $obj->{$col}=$result[$col];
        }
        return $obj;
    }
}
