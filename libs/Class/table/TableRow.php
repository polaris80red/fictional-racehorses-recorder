<?php
abstract class TableRow {
    public const INT_COLUMNS=[];
    public const STR_COLUMNS=[];
    /**
     * 行相当の配列からこのオブジェクトにセットする
     */
    public function setFromArray(array $input_data){
        $columns=static::getColumnNames();
        foreach($columns as $col){
            $this->{$col}=$input_data[$col];
        }
        return $this;
    }
    /**
     * カラム名を配列で取得
     * @param string|array $exclude 除外するカラム名またはカラム名のリスト
     */
    public static function getColumnNames(string|array $exclude=''){
        $result=array_merge(static::INT_COLUMNS,static::STR_COLUMNS);
        return self::removeColumns($result,$exclude);
    }
    /**
     * 文字列型カラムの名前のリストを配列で取得
     * @param string|array $exclude 除外するカラム名またはカラム名のリスト
     */
    public static function getStrColmunNames(string|array $exclude=''):array {
        return self::removeColumns(static::STR_COLUMNS,$exclude);
    }
    /**
     * 整数型カラムの名前のリストを配列で取得
     * @param string|array $exclude 除外するカラム名またはカラム名のリスト
     */
    public static function getIntColmunNames(string|array $exclude=''):array {
        return self::removeColumns(static::INT_COLUMNS,$exclude);
    }
    /**
     * カラム名の配列から、名前またはリストで指定したカラム名を除去
     * @param string|array $exclude 除外するカラム名またはカラム名のリスト
     */
    private static function removeColumns($column_names,string|array $exclude=''){
        if($exclude===''){
            return $column_names;
        }
        if($exclude===[]){
            return $column_names;
        }
        if(is_array($exclude)){
            return array_diff($column_names,$exclude);
        }
        return array_diff($column_names,[$exclude]);
    }
}
