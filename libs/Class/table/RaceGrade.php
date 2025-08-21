<?php
class RaceGrade extends Table{
    public const TABLE = 'mst_race_grade';
    public const UNIQUE_KEY_COLUMN="id";
    protected const DEFAULT_ORDER_BY ='`sort_number` IS NULL, `sort_number` ASC, `id` ASC';
    public const ROW_CLASS = RaceGradeRow::class;

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
        $obj=new RaceGradeRow();
        $columns=RaceGradeRow::getRowNames();
        foreach($columns as $col){
            $obj->{$col}=$result[$col];
        }
        return $obj;
    }
    public static function getForSelectbox($pdo){
        $sql_parts[]="SELECT * FROM ".self::QuotedTable();
        $sql_parts[]="WHERE `show_in_select_box`=1 AND `is_enabled`=1";
        $sql_parts[]="ORDER BY ".self::DEFAULT_ORDER_BY;
        $stmt = $pdo->prepare(implode(" ",$sql_parts));
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
