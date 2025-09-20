<?php
class Imploader{
    private $list=[];
    private $separator='';
    public function __construct(string $separator='',array $list=[]){
        $this->separator($separator);
        if(count($list)>0){
            $this->list = $list;
        }
    }
    /**
     * 区切り文字を設定
     */
    public function separator(string $input)
    {
        $this->separator=$input;
        return $this;
    }
    /**
     * 項目を追加（nullの場合は何もしない）
     */
    public function add(string|array|null $input){
        if($input===null){ return $this; }
        if(is_array($input)){
            foreach($input as $value){ $this->list[]=$value; }
        }else{
            $this->list[]=$input;
        }
        return $this;
    }
    /**
     * 項目を除去
     */
    public function remove(string|array $input){
        if(!is_array($input)){
            $remove_array=[$input];
        }else{
            $remove_array=$input;
        }
        $this->list=array_diff($this->list,$remove_array);
        return $this;
    }
    public function count(){
        return count($this->list);
    }
    public function get():string {
        return implode($this->separator,$this->list);
    }
    public function __toString()
    {
        return $this->get();
    }
}
