<?php
class SqlMakeSelectcolumns{
    public $target_table='';
    public $columns=[];
    public $raw_columns=[];
    public $sql_select_column='';
    public function __construct(string $table_name){
        $this->target_table=$table_name;
    }
    public function addRawColumn(string $column_str){
        $this->raw_columns[]=$column_str;
    }
    public function addColumnName(string $column_name){
        $this->addRawColumn("`{$column_name}`");
    }
    public function addcolumnsByArray(array $columns){
        if(count($columns)>0){
            foreach($columns as $row){
                $this->addColumnName($row);
            }
            return true;
        }
        return false;
    }
    public function addRawcolumnsByArray(array $columns){
        if(count($columns)>0){
            foreach($columns as $row){
                $this->addRawColumn($row);
            }
            return true;
        }
        return false;
    }
    public function addColumnAs(string $column,string $as){
        $this->raw_columns[]="`{$column}` AS '{$as}'";
        return true;
    }
    public function get($add_lf=false){
        if($this->target_table===''){
            throw new Exception("テーブル名未設定エラー");
            return false;
        }
        $suffix="";
        if($add_lf){
            $suffix="\n";
        }
        $select_column_parts=[];
        if(count($this->raw_columns)>0){
            foreach($this->raw_columns as $row){
                $select_column_parts[]="`{$this->target_table}`.{$row}";
            }

        }
        return implode(','.$suffix, $select_column_parts);
    }
}