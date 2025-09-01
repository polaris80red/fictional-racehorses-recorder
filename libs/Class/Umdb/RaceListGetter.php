<?php
class RaceListGetter{
    protected PDO $pdo;
    public $limit;
    public $offset;
    public $where_and_parts=[];
    public $order_parts=[];

    public function __construct(PDO $pdo) {
        $this->pdo=$pdo;
    }
    public function setLimit(int $limit){ $this->limit=$limit; }
    public function setOffset(int $offset){ $this->offset=$offset; }
    public function addWhere(string $where_str){ $this->where_and_parts[]=$where_str; }
    public function addWhereParts(array $where_parts){
        $this->where_and_parts=array_merge($this->where_and_parts,$where_parts);
    }
    public function addOrder(string $order){ $this->order_parts[]=$order; }
    public function addOrderParts(array $order_parts){
        $this->order_parts=array_merge($this->order_parts,$order_parts);
    }
    public function getPDOStatement(){
        $tbl=Race::TABLE;
        $week_tbl=RaceWeek::TABLE;
        $age_tbl=RaceCategoryAge::TABLE;
        $grade_tbl=RaceGrade::TABLE;
        $course_mst_tbl=RaceCourse::TABLE;
        $sql=<<<END
        SELECT
            r.*
            ,w.month AS 'w_month'
            ,w.umm_month_turn
            ,g.short_name as grade_short_name
            ,g.css_class_suffix as grade_css_class_suffix
            ,c.short_name as race_course_mst_short_name
            ,c.short_name_m as race_course_mst_short_name_m
        FROM `{$tbl}` AS r
        LEFT JOIN `{$week_tbl}` as w ON r.week_id=w.id
        LEFT JOIN `{$age_tbl}` as age ON r.age_category_id=age.id
        LEFT JOIN `{$grade_tbl}` as g ON r.grade LIKE g.unique_name AND g.is_enabled=1
        LEFT JOIN `{$course_mst_tbl}` as c ON r.race_course_name LIKE c.unique_name AND c.is_enabled=1
        END;
        if(count($this->where_and_parts)>0){
            $sql.=" WHERE ".implode(' AND ',$this->where_and_parts);
        }
        if(count($this->order_parts)>0){
            $sql.=" ORDER BY ".implode(',',$this->order_parts);
        }
        if(isset($this->limit)){
            $sql.=" LIMIT ".$this->limit;
        }
        if($this->offset>0){
            $sql.=" OFFSET ".$this->offset;
        }
        return $this->pdo->prepare($sql);
    }
}
