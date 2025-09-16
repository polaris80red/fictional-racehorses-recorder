<?php
/**
 * レース画面アクセス履歴
 */
class RaceAccessHistory{
    protected array $access_list=[];
    protected int $max_history_number=20;
    const Session_Key='race_history';

    public function __construct(bool $load_execute=true){
        if($load_execute===true){
            $this->loadFromSession();
        }
    }
    public function set(string $race_id){
        // 存在する場合は先頭に移すために除去する
        $this->access_list=array_diff($this->access_list,[$race_id]);
        // 配列の先頭に項目を追加する
        array_unshift($this->access_list,$race_id);
        $remove_num = count($this->access_list) - $this->max_history_number;
        if($remove_num>0){
            for ($i=0; $i < $remove_num; $i++) { 
                array_pop($this->access_list);
            }
        }
        return $this;
    }
    public function loadFromSession(){
        if(isset($_SESSION[APP_INSTANCE_KEY][self::Session_Key])){
            $this->access_list=$_SESSION[APP_INSTANCE_KEY][self::Session_Key];
        }
        return $this;
    }
    public function saveToSession(){
        $_SESSION[APP_INSTANCE_KEY][self::Session_Key]=$this->access_list;
        return $this;
    }
    public function toArray(){
        return $this->access_list;
    }
    public function count(){
        return count($this->access_list);
    }
}
