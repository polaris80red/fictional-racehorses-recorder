<?php
class SurrogateKeyGenerator {
    public const TABLE = 'sys_surrogate_key_generator';
    public $last_insert_id=null;
    private $pdo;
    private $prefix;

    private $id_parts=[];
    private int $retry_counter=0;
    public function __construct(PDO $pdo,$prefix='')
    {
        $this->pdo=$pdo;
        $this->prefix=$prefix;
    }
    public function Increment(){
        $date_time_str=(new DateTime())->format("Y-m-d H:i:s");
        $sql="INSERT INTO ".self::TABLE." (`created_at`) VALUE (:created_at);";
        $stmt=$this->pdo->prepare($sql);
        $stmt->bindValue(':created_at',$date_time_str,PDO::PARAM_STR);
        $stmt->execute();
        $this->last_insert_id = $this->pdo->lastInsertId();
        return $this->last_insert_id;
    }
    public function Reset(){
        $sql="TRUNCATE TABLE `".self::TABLE."`;";
        $this->pdo->exec($sql);
    }
    /**
     * 最後に登録したレコードの登録日時を取得
     */
    public function getLastCreatedAt(){
        $sql="SELECT `created_at` FROM `".self::TABLE."` ORDER BY `id` DESC LIMIT 1;";
        $stmt=$this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
    public function autoReset(){
        // リセットモードがONで、ジェネレータのレコードが存在する場合に
        if(AUTO_ID_RESET_MODE!='' && false!== $result=$this->getLastCreatedAt()){
            $datetime_now=new DateTime();
            $datetime_c=new DateTime($result['created_at']);
            if(AUTO_ID_RESET_MODE==='d'){
                $now=$datetime_now->format('Y-m-d');
                $last=$datetime_c->format('Y-m-d');
                if($now!==$last){
                    Elog::debug("自動IDリセット実行（日付単位）直近ID登録:{$last}, 現在:{$now}");
                    $this->Reset();
                }
            }else if(AUTO_ID_RESET_MODE==='m'){
                $now=$datetime_now->format('Y-m');
                $last=$datetime_c->format('Y-m');
                if($now!==$last){
                    Elog::debug("自動IDリセット実行（月単位）直近ID登録:{$last}, 現在:{$now}");
                    $this->Reset();
                }
            }else if(AUTO_ID_RESET_MODE==='y'){
                $now=$datetime_now->format('Y');
                $last=$datetime_c->format('Y');
                if($now!==$last){
                    Elog::debug("自動IDリセット実行（年単位）直近ID登録:{$last}, 現在:{$now}");
                    $this->Reset();
                }
            }
        }
    }
    public function retryId(){
        $this->id_parts[3]="-".dechex(++$this->retry_counter);
        return implode('',$this->id_parts);
    }
    public function generateId(){
        $this->id_parts=[];
        $this->id_parts[0]=$this->prefix;
        if(AUTO_ID_DATE_PART_FORMAT!==''){
            $this->id_parts[0].=(new DateTime())->format(AUTO_ID_DATE_PART_FORMAT);
        }
        $this->id_parts[1]= AUTO_ID_DATE_NUMBER_SEPARATOR;
        $last_id_hex=dechex($this->Increment());
        if(AUTO_ID_NUMBER_MIN_LENGTH > 1){
            $this->id_parts[2] = str_pad($last_id_hex,AUTO_ID_NUMBER_MIN_LENGTH,0,STR_PAD_LEFT);
        }else{
            $this->id_parts[2] = $last_id_hex;
        }
        $this->id_parts[3] ='';
        return implode('',$this->id_parts);
    }
}
