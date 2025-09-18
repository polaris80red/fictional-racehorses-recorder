<?php
abstract class Table{
    private const TABLE ='';
    private const UNIQUE_KEY_COLUMN='';
    protected const DEFAULT_ORDER_BY='';
    protected const STR_COLUMNS=[];
    protected const INT_COLUMNS=[];
    public $record_exists = false;

    // __toString可能なインスタンスとしてクォート済みテーブル名を取得する
    public static function QuotedTable(string $table_name = ''){
        return new MkSqlQuotedTable($table_name?:static::TABLE);
    }
    // 文字列内に展開できるようにテーブル名を取得する
    public function tableName(){ return static::TABLE;}

    public static function getAll(PDO $pdo, bool $show_disabled=false, string|null $order_by=null){
        if($order_by===null){ $order_by=static::DEFAULT_ORDER_BY; }
        self::checkTableName();
        $sql="SELECT * FROM `".static::TABLE."`";
        if($show_disabled===false){
            $sql.=" WHERE `is_enabled`=1";
        }
        if($order_by!==''){
            $sql.=" ORDER BY {$order_by}";
        }
        $sql .= ";";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    private static function checkTableName(){
        if(static::TABLE==''){ throw new ErrorException('テーブル名未設定');}
    }
    /**
     * 1つのカラムをユニークキーで取得
     */
    public static function getColumnByUniqueKey(
        PDO $pdo,
        string $unique_key_colUmn,
        $id,
        string $column_name,
        int $pdo_param_mode=PDO::PARAM_STR
    ) {
        $result = self::getByUniqueKey($pdo, $unique_key_colUmn, $id, $pdo_param_mode);
        if(isset($result[$column_name])){
            return $result[$column_name];
        }
        return null;
    }
    /**
     * staticのテーブルのUNIQUE_KEYで取得
     * @param PDO $pdo
     * @param string|int $id
     */
    public static function getById(PDO $pdo, $id, $pdo_param_mode=PDO::PARAM_STR){
        if(static::UNIQUE_KEY_COLUMN==''){ throw new ErrorException('カラム名未設定');}
        return self::getByUniqueKey($pdo, static::UNIQUE_KEY_COLUMN, $id, $pdo_param_mode);
    }
    /**
     * staticのテーブルからユニークキーで取得
     * @param PDO $pdo
     * @param string $unique_key_colUmn
     * @param string|int $unique_key_value
     */
    public static function getByUniqueKey(
        PDO $pdo, 
        string $unique_key_colUmn,
        $unique_key_value,
        int $pdo_param_mode=PDO::PARAM_STR
    ) {
        self::checkTableName();
        $sql="SELECT * FROM `".static::TABLE."`";
        if($pdo_param_mode===PDO::PARAM_INT){
            $sql.=" WHERE `$unique_key_colUmn` = :unique_key";
        }else{
            $sql.=" WHERE `$unique_key_colUmn` LIKE :unique_key";
            $unique_key_value = SqlValueNormalizer::escapeLike($unique_key_value);
        }
        $sql.=" LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':unique_key',$unique_key_value,$pdo_param_mode);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    /**
     * メンバ変数と共通の名前のカラムを配列で指定して一括バインド
     */
    protected function BindValuesFromThis($stmt, array $targets,$mode){
        foreach($targets as $val){
            $stmt->bindValue(":{$val}", $this->{$val}, $mode);
        }
        return $stmt;
    }
    // 競走馬・レースなど改修までの暫定
    public $error_msgs=[];
    public $error_exists=false;
    /**
     * 文字数チェック（エラー有無判定をテーブルクラスに持つクラス用の暫定）
     */
    protected function validateLength(string|null $target_value,string $name,int $max_length){
        if($target_value===null){
            return;
        }
        if(mb_strlen($target_value)>$max_length){
            $this->error_msgs[]="{$name}は{$max_length}文字以内で設定してください。";
            $this->error_exists=true;
        }
        return;
    }
    // 行クラスを使用する新形式対応
    public const ROW_CLASS = TableRow::class;
    public static function InsertFromRowObj(PDO $pdo, TableRow $row_obj){
        $exclude_columns=[static::UNIQUE_KEY_COLUMN];
        $int_columns=array_diff((static::ROW_CLASS)::INT_COLUMNS,$exclude_columns);
        $str_columns=array_diff((static::ROW_CLASS)::STR_COLUMNS,$exclude_columns);

        $sql=SqlMake::InsertSql(static::TABLE,array_merge($str_columns,$int_columns));
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
            ELog::error(__CLASS__.__METHOD__,[$stmt,$e]);
            return false;
        }
    }
    public static function UpdateFromRowObj(PDO $pdo, TableRow $row_obj){
        $colmuns=array_merge((static::ROW_CLASS)::INT_COLUMNS,(static::ROW_CLASS)::STR_COLUMNS);
        $id =static::UNIQUE_KEY_COLUMN;
        $update_set_columns=array_diff($colmuns,[$id]);
        $sql=SqlMake::UpdateSqlWhereRaw(static::TABLE,$update_set_columns,"`{$id}`=:{$id}");
        $stmt = $pdo->prepare($sql);
        foreach((static::ROW_CLASS)::INT_COLUMNS as $i_col){
            $stmt->bindValue(":{$i_col}",$row_obj->{$i_col},PDO::PARAM_INT);
        }
        foreach((static::ROW_CLASS)::STR_COLUMNS as $s_col){
            $stmt->bindValue(":{$s_col}",$row_obj->{$s_col},PDO::PARAM_STR);
        }
        try{
            $stmt->execute();
            return true;
        }catch (Exception $e){
            echo "<pre>"; var_dump($stmt->debugDumpParams());echo "</pre>";
            ELog::error(__CLASS__.__METHOD__,[$stmt,$e]);
            return false;
        }
    }
    public $current_page=1;
    public $has_next_page=false;
    public $one_page_record_num=25;

    /**
     * @return TableRow 実際はTableRowインスタンスの配列だがvscodeでエラーになるため指定
     */
    public function getPage($pdo, $current_page=1, $show_disabled=false){
        $sql_parts[]="SELECT * FROM ".self::QuotedTable();
        if(!$show_disabled){
            $sql_parts[]="WHERE `is_enabled`=1";
        }
        $sql_parts[]="ORDER BY ".static::DEFAULT_ORDER_BY;
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
    /**
     * カラム名にPrefixをつけてSELECTのカラム指定部分を生成する
     * @param string $table_alias_name テーブル名またはテーブル名のエイリアス、空ならテーブル名をそのまま使用
     * @param string $prefix 付与するprefix、なければテーブル名とアンダースコア2つ '__'
     * @return string prefixを追加済みのカラム名の配列をカンマ,でimplodeした文字列
     */
    public static function getPrefixedSelectClause(string $table_alias_name='',string $prefix=''){
        return implode(',',self::getPrefixedSelectColmuns($table_alias_name,$prefix));
    }
    /**
     * カラム名にPrefixをつけてSELECTのカラム指定部分を生成する
     * @param string $table_alias_name テーブル名またはテーブル名のエイリアス、空ならテーブル名をそのまま使用
     * @param string $prefix 付与するprefix、なければテーブル名とアンダースコア2つ '__'
     * @return array prefixを追加済みのカラム名の配列
     */
    public static function getPrefixedSelectColmuns(string $table_alias_name='',string $prefix=''){
        $table_alias_name = $table_alias_name?:static::TABLE;
        $prefix = $prefix?:static::TABLE.'__';
        $columns=(static::ROW_CLASS)::getColumnNames();
        $parts=[];
        foreach($columns as $column){
            $parts[]="`{$table_alias_name}`.`{$column}` AS `{$prefix}{$column}`";
        }
        return $parts;
    }
}
