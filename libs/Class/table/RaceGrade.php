<?php
class RaceGrade extends Table{
    public const TABLE = 'mst_race_grade';
    public const UNIQUE_KEY_COLUMN="unique_name";
    protected const DEFAULT_ORDER_BY
    ='`sort_number` IS NULL, `sort_number` ASC, `id` ASC';

    public $current_page=1;
    public $has_next_page=false;
    public $one_page_record_num=25;

    public static function getById(PDO $pdo, $id, $pdo_param_mode=PDO::PARAM_STR){
        $result = self::getByUniqueKey($pdo,'id',$id,PDO::PARAM_INT);
        if($result==false){
            return false;
        }
        return (new RaceGradeRow())->setFromArray($result);
    }

    public static function getByRaceResultsKey(PDO $pdo, $id){
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
    public function getPage($pdo, $current_page=1){
        $sql_parts[]="SELECT * FROM ".self::QuotedTable();
        $sql_parts[]="WHERE 1";
        $sql_parts[]="ORDER BY ".self::DEFAULT_ORDER_BY;
        $this->current_page = $current_page;
        $sql_parts[]="LIMIT {$this->one_page_record_num}";
        if($current_page>1){
            $offset = $this->one_page_record_num * (max($current_page,1)-1);
            $sql_parts[]="OFFSET {$offset};";
        }
        $stmt = $pdo->prepare(implode(" ",$sql_parts));
        $stmt->execute();
        //$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
        $results=[];
        $count=0;
        while(($row=$stmt->fetch())!=false){
            $count++;
            $results[]=(new RaceGradeRow())->setFromArray($row);
        }
        if($count >= $this->one_page_record_num){
            $this->has_next_page=true;
        }
        return $results;
    }
    public static function InsertFromObj(PDO $pdo, RaceGradeRow $row_obj){
        $exclude_columns=['id'];
        $int_columns=array_diff(RaceGradeRow::INT_COLUMNS,$exclude_columns);
        $str_columns=array_diff(RaceGradeRow::STR_COLUMNS,$exclude_columns);

        $sql=SqlMake::InsertSql(self::TABLE,array_merge($str_columns,$int_columns));
        $stmt = $pdo->prepare($sql);
        foreach($int_columns as $i_col){
            $stmt->bindValue(":{$i_col}",$row_obj->{$i_col},PDO::PARAM_INT);
        }
        foreach($str_columns as $s_col){
            $stmt->bindValue(":{$s_col}",$row_obj->{$s_col},PDO::PARAM_STR);
        }
        try{
            $stmt->execute();
            return true;
        }catch (Exception $e){
            echo "<pre>"; var_dump($stmt->debugDumpParams());echo "</pre>";
            Elog::error(__CLASS__.__METHOD__,[$stmt,$e]);
            return false;
        }
    }
    public static function UpdateFromObj(PDO $pdo, RaceGradeRow $row_obj){
        $colmuns=array_merge(RaceGradeRow::INT_COLUMNS,RaceGradeRow::STR_COLUMNS);
        $update_set_columns=array_diff($colmuns,['id']);
        $sql=SqlMake::UpdateSqlWhereRaw(static::TABLE,$update_set_columns,"`id`=:id");
        $stmt = $pdo->prepare($sql);
        foreach(RaceGradeRow::INT_COLUMNS as $i_col){
            $stmt->bindValue(":{$i_col}",$row_obj->{$i_col},PDO::PARAM_INT);
        }
        foreach(RaceGradeRow::STR_COLUMNS as $s_col){
            $stmt->bindValue(":{$s_col}",$row_obj->{$s_col},PDO::PARAM_STR);
        }
        try{
            $stmt->execute();
            return true;
        }catch (Exception $e){
            echo "<pre>"; var_dump($stmt->debugDumpParams());echo "</pre>";
            Elog::error(__CLASS__.__METHOD__,[$stmt,$e]);
            return false;
        }
    }
}
