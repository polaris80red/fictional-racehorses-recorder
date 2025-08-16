<?php
class SqlMake{
    /**
     * INSERT SQL生成
     * @param string $table テーブル名
     * @param array $insert_columns カラム名配列
     */
    public static function InsertSql(string $table, array $insert_columns){
        $columns=[];
        $values=[];
        foreach($insert_columns as $column){
            $columns[]="`{$column}`";
            $values[]=":{$column}";
        }
        $columns_str = implode(",",$columns);
        $values_str = implode(", ",$values);
        return "INSERT INTO `{$table}` ( {$columns_str} ) VALUES ( {$values_str} );";
    }
    /**
     * SELECT SQL 生成
     * @return string
     */
    public static function SelectSqlWhereRaw(
        string $table, 
        string $where_raw_text='`is_enabled`=1', 
        string $order_raw_text=''){
        $sql = "SELECT * FROM `{$table}` WHERE $where_raw_text";
        if($order_raw_text!=''){
            $sql .= " ORDER BY {$order_raw_text}";
        }
        return $sql;
    }
    /**
     * UPDATE SQL 生成
     * @param string $table テーブル名
     * @param array $update_columns SETするカラム
     * @param string $where_text WHERE条件
     */
    public static function UpdateSqlWhereRaw(string $table, array $update_columns, string $where_text){
        $columns=[];
        foreach($update_columns as $column){
            $columns_parts[]="`{$column}`=:{$column}";
        }
        $columns_part_str = implode(",\n",$columns_parts);
        return "UPDATE `{$table}` SET \n{$columns_part_str} \nWHERE $where_text;";
    }
    /**
     * カラム名を配列で渡すとバッククォートで囲んだ配列を返す
     */
    public static function quoteColumns(array $column_names,string $table_name=''){
        $prefix= $table_name !=='' ? "`{$table_name}`.":"";
        $results=array_map(function($col) use ($prefix) {
            return "{$prefix}`{$col}`";
        }, $column_names);
        return $results;
    }
    /**
     * カラム名を配列で渡すとプレースホルダの配列を返す
     */
    public static function placeholders(array $column_names){
        $results=array_map(function($col) {
            return ":$col";
        }, $column_names);
        return $results;
    }
    /**
     * カラム名を配列で渡すとUPDATE SET用の「クォート済みカラム=プレースホルダ」の配列を返す
     */
    public static function updateSetClauses(array $column_names,string $table_name=''):array {
        $prefix= $table_name !=='' ? "`{$table_name}`.":"";
        // 1回ごとに判定になるのでprefixは先に作る
        $results=array_map(function($col) use ($prefix) {
            return $prefix.self::updateSetClause($col);
        }, $column_names);
        return $results;
    }
    /**
     * カラム名からUPDATE SET用の「クォート済みカラム=プレースホルダ」の文字列を1組返す
     */
    public static function updateSetClause(string $column_name,string $table_name=''):string {
        $prefix= $table_name !=='' ? "`{$table_name}`.":"";
        return $prefix."`{$column_name}`=:{$column_name}";
    }
}