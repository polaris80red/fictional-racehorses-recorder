<?php
class LoginAttemptIp extends Table{
    public const TABLE = 'sys_login_attempt_ip';
    public const UNIQUE_KEY_COLUMN="ip";
    private PDO $pdo;
    private $ipAddr;
    public function __construct(PDO $pdo,$ipAddr) {
        $this->pdo=$pdo;
        $this->ipAddr=$ipAddr;
    }
    public function get(){
        return $this->getByUniqueKey($this->pdo,self::UNIQUE_KEY_COLUMN,$this->ipAddr);
    }
    public function increment(){
        $row = $this->getByUniqueKey($this->pdo,self::UNIQUE_KEY_COLUMN,$this->ipAddr);
        if(!$row){
            $sql="INSERT INTO `".self::TABLE."` (`ip`,`login_failed_attempts`,`last_attempt_at`) VALUES(:ip,:login_failed_attempts,:last_attempt_at)";
            $stmt=$this->pdo->prepare($sql);
            $stmt->bindValue(':ip',$this->ipAddr,PDO::PARAM_STR);
            $stmt->bindValue(':login_failed_attempts',1,PDO::PARAM_INT);
            $stmt->bindValue(':last_attempt_at',PROCESS_STARTED_AT,PDO::PARAM_STR);
            $stmt->execute();
        }else{
            $sql="UPDATE`".self::TABLE."` SET `login_failed_attempts`=`login_failed_attempts`+1,`last_attempt_at`=:last_attempt_at WHERE `ip`=:ip";
            $stmt=$this->pdo->prepare($sql);
            $stmt->bindValue(':ip',$this->ipAddr,PDO::PARAM_STR);
            $stmt->bindValue(':last_attempt_at',PROCESS_STARTED_AT,PDO::PARAM_STR);
            $stmt->execute();
        }
    }
    public function lock($login_locked_until){
        $sql="UPDATE`".self::TABLE."` SET `login_failed_attempts`=0, `login_locked_until`=:login_locked_until,`last_attempt_at`=:last_attempt_at WHERE `ip`=:ip";
        $stmt=$this->pdo->prepare($sql);
        $stmt->bindValue(':ip',$this->ipAddr,PDO::PARAM_STR);
        $stmt->bindValue(':login_locked_until',$login_locked_until,PDO::PARAM_STR);
        $stmt->bindValue(':last_attempt_at',PROCESS_STARTED_AT,PDO::PARAM_STR);
        $stmt->execute();
    }
    public function reset(){
        $sql="UPDATE`".self::TABLE."` SET `login_failed_attempts`=0, `login_locked_until`=null,`last_attempt_at`=:last_attempt_at WHERE `ip`=:ip";
        $stmt=$this->pdo->prepare($sql);
        $stmt->bindValue(':ip',$this->ipAddr,PDO::PARAM_STR);
        $stmt->bindValue(':last_attempt_at',PROCESS_STARTED_AT,PDO::PARAM_STR);
        $stmt->execute();
    }
}
