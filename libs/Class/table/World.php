<?php
class World extends Table{
    public const TABLE = 'mst_world';
    public const UNIQUE_KEY_COLUMN="id";
    protected const STR_COLUMNS=[
        'name',
        'auto_id_prefix',
    ];
    protected const INT_COLUMNS=[
        'id',
        'guest_visible',
        'sort_priority',
        'is_enabled',
    ];
    protected const DEFAULT_ORDER_BY='`sort_priority` DESC, `id` ASC';

    public $record_exists = false;

    public $id=0;
    public $name='';
    public $guest_visible=1;

    public $auto_id_prefix='';

    public $sort_priority=0;
    public $is_enabled=1;
    
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
    public function InsertExec(PDO $pdo){ return $this->SimpleInsertExec($pdo); }
    public function UpdateExec(PDO $pdo){
        return $this->SimpleUpdateExec($pdo);
    }
}
