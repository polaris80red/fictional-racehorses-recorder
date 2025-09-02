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
    public static function getColumnNames(){
        return array_merge(static::INT_COLUMNS,static::STR_COLUMNS);
    }
}
