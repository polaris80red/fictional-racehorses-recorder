<?php
class RaceCourse extends Table{
    public const TABLE = 'mst_race_course';
    public const UNIQUE_KEY_COLUMN="id";
    protected const INT_COLUMNS=[
        'id',
        'sort_number',
        'show_in_select_box',
        'is_enabled',
    ];
    protected const STR_COLUMNS=[
        'unique_name',
        'short_name',
    ];
    protected const DEFAULT_ORDER_BY
    ='`sort_number` IS NULL, `sort_number` ASC, `id` ASC';

    public $id                  =0;
    public $unique_name         ='';
    public $short_name          ='';
    public $sort_number         =null;
    public $show_in_select_box  =1;
    public $is_enabled          =1;
    
    public $current_page=1;
    public $prev_page;
    public $next_page;
    public $one_page_record_num=25;

    public function __construct(PDO|null $pdo=null, int $key=0){
        if(!is_null($pdo)&& $key>0){
            $this->getDataById($pdo,$key);
        }
    }
    public function getDataById(PDO $pdo, int $id){
        $this->id = $id;
        $result=$this->getById($pdo,$id,PDO::PARAM_INT);
        if(!$result){ return false; }

        $this->record_exists=true;
        $column_names=array_merge(self::INT_COLUMNS,self::STR_COLUMNS);
        foreach($column_names as $name){
            $this->{$name}=$result[$name];
        }
        return $result;
    }
    public function InsertExec(PDO $pdo){
        return $this->SimpleInsertExec($pdo);
    }
    public function UpdateExec(PDO $pdo){
        return $this->SimpleUpdateExec($pdo);
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
        $this->next_page = $current_page+1;
        $this->prev_page = ($current_page>1)?($current_page-1):null;
        $sql_parts[]="LIMIT {$this->one_page_record_num}";
        if($current_page>1){
            $offset = $this->one_page_record_num * (max($current_page,1)-1);
            $sql_parts[]="OFFSET {$offset};";
        }
        $stmt = $pdo->prepare(implode(" ",$sql_parts));
        $stmt->execute();
        $data=$stmt->fetchAll(PDO::FETCH_ASSOC);
        if($data===false){return false;}
        if(count($data) < $this->one_page_record_num){ $this->next_page=null; }
        return $data;
    }
}
