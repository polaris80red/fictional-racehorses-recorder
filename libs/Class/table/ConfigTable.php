<?php
class ConfigTable{
    public const TABLE = 'sys_config';
    public const UNIQUE_KEY_COLUMN="number";
    private $timesamp_str='';

    private PDO $pdo;
    public function __construct(PDO $pdo) {
        $this->pdo=$pdo;
    }
    public function setTimestamp($timesamp_str){
        $this->timesamp_str=$timesamp_str;
        return $this;
    }
    public function getAllParams(){
        $sql="SELECT `config_key`,`config_value` FROM `".self::TABLE."` WHERE 1";
        $stmt=$this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function saveAllParams($input){
        $insert_list=[];
        $update_list=[];
        foreach($input as $key => $value){
            if($this->getParam($key)===false){
                $insert_list[$key]=$value;
                continue;
            }
            $update_list[$key]=$value;
        }
        $this->insert($insert_list);
        $this->update($update_list);
    }
    private function insert($data){
        $sql= "INSERT INTO `".self::TABLE."` (`config_key`,`config_value`,`created_at`,`updated_at`)";
        $sql.="VALUE(:key, :value, :dt, :dt)";
        $stmt=$this->pdo->prepare($sql);
        $insert_key=null;
        $insert_value=null;
        $stmt->bindParam(':key',$insert_key,PDO::PARAM_STR);
        $stmt->bindParam(':value',$insert_value,PDO::PARAM_STR);
        $stmt->bindValue(':dt',$this->timesamp_str,PDO::PARAM_STR);
        foreach($data as $key=>$value){
            $insert_key=$key;
            $insert_value=$value;
            $stmt->execute();
        }
    }
    private function update($data){
        $sql = "UPDATE `".self::TABLE."`";
        $sql.= " SET `config_value`=:value, `updated_at`=:dt";
        $sql.= " WHERE `config_key` LIKE :key";
        $stmt=$this->pdo->prepare($sql);
        $update_key=null;
        $update_value=null;
        $stmt->bindParam(':key',$update_key,PDO::PARAM_STR);
        $stmt->bindParam(':value',$update_value,PDO::PARAM_STR);
        $stmt->bindValue(':dt',$this->timesamp_str,PDO::PARAM_STR);
        foreach($data as $key=>$value){
            $update_key=SqlValueNormalizer::escapeLike($key);
            $update_value=$value;
            $stmt->execute();
        }
    }
    private function getParam($name){
        $sql="SELECT `config_key`,`config_value` FROM `".self::TABLE."` WHERE `config_key` LIKE :key LIMIT 1";
        $stmt=$this->pdo->prepare($sql);
        $stmt->bindValue(':key',$name,PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }
}
