<?php
/**
 * レース条件（馬齢）
 */
class RaceCategoryAge extends Table{
    public const TABLE = 'mst_race_category_age';
    public const UNIQUE_KEY_COLUMN="id";
    protected const DEFAULT_ORDER_BY ='`sort_number` IS NULL, `sort_number` ASC, `id` ASC';
    public const ROW_CLASS = RaceCategoryAgeRow::class;

    public $current_page=1;
    public $has_next_page=false;
    public $one_page_record_num=25;

    public static function getShortNameById(PDO $pdo, $id){
        return (string)self::getColumnByUniqueKey($pdo, self::UNIQUE_KEY_COLUMN,$id,'short_name_2');
    }
    public static function getNameById(PDO $pdo, $id){
        return (string)self::getColumnByUniqueKey($pdo, self::UNIQUE_KEY_COLUMN,$id,'name');
    }
    public static function getById(PDO $pdo, $id, $pdo_param_mode=PDO::PARAM_STR){
        $result = self::getByUniqueKey($pdo,'id',$id,PDO::PARAM_INT);
        if($result==false){
            return false;
        }
        return (new (static::ROW_CLASS))->setFromArray($result);
    }
    public function getPage($pdo, $current_page=1, $show_disabled=false){
        $sql_parts[]="SELECT * FROM ".self::QuotedTable();
        if(!$show_disabled){
            $sql_parts[]="WHERE `is_enabled`=1";
        }
        $sql_parts[]="ORDER BY ".self::DEFAULT_ORDER_BY;
        $this->current_page = $current_page;
        $sql_parts[]="LIMIT {$this->one_page_record_num}";
        if($current_page>1){
            $offset = $this->one_page_record_num * (max($current_page,1)-1);
            $sql_parts[]="OFFSET {$offset};";
        }
        $stmt = $pdo->prepare(implode(" ",$sql_parts));
        $stmt->execute();
        $results=[];
        $count=0;
        while(($row=$stmt->fetch())!=false){
            $count++;
            $results[]=(new (static::ROW_CLASS))->setFromArray($row);
        }
        if($count >= $this->one_page_record_num){
            $this->has_next_page=true;
        }
        return $results;
    }
}
