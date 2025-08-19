<?php
/**
 * ユニークキーと利用する紐づけ個所の一括変更機能用
 */
class IdUpdater{
    private PDO $pdo;
    private $old_key;
    private $new_key;
    private $pdo_param_type;
    private array $stmts;
    public function __construct(PDO $pdo,$old_key,$new_key,$bind_type=PDO::PARAM_STR) {
        $this->pdo=$pdo;
        $this->old_key=$old_key;
        $this->new_key=$new_key;
        $this->pdo_param_type=$bind_type===PDO::PARAM_INT?PDO::PARAM_INT:PDO::PARAM_STR;
    }
    /**
     * 新IDの存在チェック
     */
    public function new_id_exists(string $table_name, string $target_column){
        $sql="SELECT * FROM `{$table_name}`";
        if($this->pdo_param_type===PDO::PARAM_INT){
            $sql.=" WHERE `{$target_column}` = :id";
        }else{
            $sql.=" WHERE `{$target_column}` LIKE :id";
        }
        $sql.= " LIMIT 1;";
        $stmt=$this->pdo->prepare($sql);
        $stmt->bindValue(':id',$this->new_key,$this->pdo_param_type);
        $stmt->execute();
        $results=$stmt->fetch();
        return ($results==false)?false:true;
    }
    /**
     * 基本的な置き換えの対象追加
     */
    public function addUpdateTarget(string $table_name, string $update_column){
        if($table_name==''){
            echo "テーブル未設定エラー";
            exit;
         }
        if($table_name==''){
            echo "カラム未設定エラー";
            exit;
        }
        $sql="UPDATE `{$table_name}` SET `{$update_column}`=:new_id";
        if($this->pdo_param_type===PDO::PARAM_INT){
            $sql.=" WHERE `{$update_column}` = :old_id;";
        }else{
            $sql.=" WHERE `{$update_column}` LIKE :old_id;";
        }
        $stmt=$this->pdo->prepare($sql);
        $stmt->bindValue(':new_id',$this->new_key,$this->pdo_param_type);
        $stmt->bindValue(':old_id',$this->old_key,$this->pdo_param_type);
        $this->stmts[]=$stmt;
        return $this;
    }
    /**
     * 同トランザクションで実行する必要のあるステートメントを追加
     */
    public function addRawStmt(PDOStatement $stmt){
        $this->stmts[]=$stmt;
        return $this;
    }
    /**
     * 処理実行
     * @param bool $use_this_transaction trueならこの処理内でトランザクションを使用する
     * ※ 外部でトランザクションを使う場合にfalseにする
     */
    public function execute(bool $use_this_transaction=true){
        if($use_this_transaction){ $this->pdo->beginTransaction();}
        try{ 
            foreach($this->stmts as $stmt){
                $stmt->execute();
            }
            if($use_this_transaction){ $this->pdo->commit();}
        }catch(Exception $e){
            Elog::error(__CLASS__,['stmts'=>$this->stmts,'error'=>$e,'this_obj'=>$this]);
            if($use_this_transaction){ $this->pdo->rollBack();}
            exit;
        }
        return true;
    }
}
