<?php
class SurrogateKeyGenerator {
    public const TABLE = 'sys_surrogate_key_generator';
    public $last_insert_id=null;
    private $pdo;
    public function __construct(PDO $pdo)
    {
        $this->pdo=$pdo;
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
    public function generateId(int $world_id,$target=''){
        $world=new World($this->pdo,$world_id);
        if($world->record_exists===false){
            Elog::error(__CLASS__."：有効なワールドID未設定");
            exit;
        }
        $id_parts=[];
        $id_parts[0]=$world->auto_id_prefix;
        if(AUTO_ID_DATE_PART_FORMAT!==''){
            $id_parts[0].=(new DateTime())->format(AUTO_ID_DATE_PART_FORMAT);
        }
        $id_parts[1]= AUTO_ID_DATE_NUMBER_SEPARATOR;
        $this->Increment();
        $last_id_hex=dechex($this->last_insert_id);
        if(AUTO_ID_NUMBER_MIN_LENGTH > 1){
            $id_parts[2] = str_pad($last_id_hex,AUTO_ID_NUMBER_MIN_LENGTH,0,STR_PAD_LEFT);
        }else{
            $id_parts[2] = $last_id_hex;
        }
        $id_parts[3] ='';// 重複時に組み込むダミー部分

        // 不適切なタイミングでリセットした場合の重複対策
        $i=0;
        do{
            $id=implode('',$id_parts);
            if($target==='horse'){
                $horse_id_exists=(new Horse())->setDataById($this->pdo,$id);
                if(!$horse_id_exists){
                    if($i>0){ Elog::debug("競走馬自動ID重複調整：{$i}, {$id}"); }
                    break;
                }
            }else if($target==='race'){
                $race_id_exists=(new RaceResults())->setDataById($this->pdo,$id);
                if(!$race_id_exists){
                    if($i>0){ Elog::debug("レース自動ID重複調整：{$i}, {$id}"); }
                    break;
                }
            }else{
                echo "エラー：種類未設定";
                Elog::error(__CLASS__."：ID種類未設定エラー");
                exit;
            }
            $id_parts[3]="-".dechex(++$i);
        }while( $i<256 );
        return $id;
    }
}
