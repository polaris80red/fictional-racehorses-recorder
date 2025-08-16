<?php
class WorldStory extends Table{
    public const TABLE = 'mst_world_story';
    public const UNIQUE_KEY_COLUMN="id";
    protected const INT_COLUMNS=[
        'id',
        'sort_priority',
        'sort_number',
        'is_read_only',
        'is_enabled',
    ];
    protected const STR_COLUMNS=[
        'name',
        'config_json',
    ];
    protected const DEFAULT_ORDER_BY
    ='`sort_priority` DESC, `sort_number` IS NULL, `sort_number` ASC, `id` ASC';

    public $record_exists = false;

    public $id=0;
    public $name='';
    public $config_json=null;
    public $sort_priority=0;
    public $sort_number=null;
    public $is_read_only=0;
    public $is_enabled=1;

    public function __construct(PDO|null $pdo=null, int $story_id=0){
        if(!is_null($pdo)&&$story_id>0){
            $this->getDataById($pdo,$story_id);
        }
    }
    public function getDataById(PDO $pdo, int $story_id){
        $this->id = $story_id;

        $sql="SELECT * FROM `".self::TABLE."` WHERE `id`=:id LIMIT 1;";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!$result){ return false; }
        $this->record_exists=true;

        $this->name = $result['name'];
        $this->config_json = json_decode($result['config_json']);
        $this->sort_priority = $result['sort_priority'];
        $this->sort_number = $result['sort_number'];
        $this->is_read_only = $result['is_read_only'];
        $this->is_enabled = $result['is_enabled'];

        return $result;
    }
    public function getConfigJsonEncodeText(){
        if($this->config_json===null){return '';}
        $text=json_encode($this->config_json,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        return $text;
    }
    public function InsertExec(PDO $pdo){
        $excluded_columns=[
            self::UNIQUE_KEY_COLUMN,
            'config_json',
        ];
        $result = self::InsertExecFromThisProp($pdo,$excluded_columns);
        return $result;
    }
    public function UpdateExec(PDO $pdo, array $excluded_columns=[]){
        $columns=array_merge(self::STR_COLUMNS,self::INT_COLUMNS);
        $excluded_columns[]='config_json';

        // UPDATE対象からユニークキーと設定JSONを取り除く
        $update_set_columns=array_diff($columns,[self::UNIQUE_KEY_COLUMN],$excluded_columns);
        $sql=SqlMake::UpdateSqlWhereRaw(self::TABLE,$update_set_columns, "`id`=:id");
        $stmt = $pdo->prepare($sql);
        $stmt=$this->BindValuesFromThis($stmt, array_diff(self::STR_COLUMNS,$excluded_columns),PDO::PARAM_STR);
        $stmt=$this->BindValuesFromThis($stmt, array_diff(self::INT_COLUMNS,$excluded_columns),PDO::PARAM_INT);
        try{
            $stmt->execute();
        }catch (Exception $e){
            echo "<pre>"; print_r($stmt->debugDumpParams());echo "</pre>";
            echo "<pre>"; print_r($e);echo "</pre>";
            return false;
        }
        return true;
    }
    /**
     * 対象のレコードを上書きする
     */
    public function updateConfigExec(PDO $pdo){
        if($this->id==0){
            return false;
        }
        $json_str=json_encode($this->config_json,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);

        $sql="UPDATE `".self::TABLE."` SET `config_json`=:config_json WHERE `id`=:id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':config_json', $json_str, PDO::PARAM_STR);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
