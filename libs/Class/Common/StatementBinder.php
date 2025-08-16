<?php
/**
 * SQL作成処理中にbindValue予定の値を仮設定・一括bind
 */
class StatementBinder {
    private $bind_data=[];
    public function add(string $param_name,$value,$type=PDO::PARAM_STR){
        $bind=[
            'param'=>$param_name,
            'value'=>$value,
            'type'=>$type?:PDO::PARAM_STR,
        ];
        $this->bind_data[]=$bind;
    }
    /**
     * 配列からbindValueを実行
     */
    public function bindTo(PDOStatement &$stmt){
        foreach($this->bind_data as $row){
            $stmt->bindValue($row['param'],$row['value'],$row['type']);
        }
        return $stmt;
    }
    public function __invoke(PDOStatement $stmt){
        return $this->bindTo($stmt);
    }
}
