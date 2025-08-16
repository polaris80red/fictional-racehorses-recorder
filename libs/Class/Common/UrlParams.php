<?php
class UrlParams{
    protected array $params=[];
    protected array $exclude_params=[];
    protected string $latest_set_param_name='';
    public function __construct(array $params=[])
    {
        if(count($params)>0){
            $this->merge($params);
        }
    }
    /**
     * パラメータをセット、既にあるキーなら上書き
     */
    public function set(string $param_name, $param_value=''){
        $this->params[$param_name]=$param_value;
        $this->latest_set_param_name=$param_name;
        return $this;
    }
    /**
     * key=>value形式の連想配列を一括セット
     */
    public function merge(array $params){
        $this->params=array_merge($this->params,$params);
    }
    public function get(string $param_name=''){
        if($param_name===''){
            return $this->params[$this->latest_set_param_name];
        }
        return $this->params[$param_name];
    }
    public function setFromGet($var_name,$filter_flag=FILTER_DEFAULT){
        $input= filter_input(INPUT_GET,$var_name,$filter_flag);
        $this->params[$var_name]=$input;
        $this->latest_set_param_name=$var_name;
        return $this;
    }
    /**
     * 指定した第1階層のパラメータを削除
     * @param string|array $remove_target_param 削除するパラメータ名またはパラメータ名のリスト
     */
    public function remove(string|array $remove_target_param){
        $params=is_array($remove_target_param)?$remove_target_param:[$remove_target_param];
        if(count($params)>0){
            foreach($params as $name){
                unset($this->params[$name]);
            }
        }
        return $this;
    }
    /**
     * 空のパラメータを全て除去
     */
    public function removeIfEmpty(){
        foreach($this->params as $key=>$value){
            if(empty($value)){
                unset($this->params[$key]);
            }
        }
        return $this;
    }
    /**
     * 継続して除外するパラメータ名のリストをセット（空配列で実行すると解除）
     * @param $exclude_params 除外するパラメータ名のリスト ['hoge','fuga']
     */
    public function setExclude(array $exclude_params=[]){
        $this->exclude_params=$exclude_params;
    }
    /**
     * @return array
     */
    public function toArray(){
        return $this->params;
    }
    /**
     * URLパラメータを取得
     * @param array $tmp_add_params 今回だけ追加するkey=>value形式のパラメータ ['hoge'=>1]
     * @param array $tmp_exclude_params 今回だけ除外するパラメータ名のリスト ['hoge','fuga']
     * @param bool $remove_empty trueなら空のパラメータは出力しない
     */
    public function toString(array $tmp_add_params=[],array $tmp_exclude_params=[],bool $remove_empty=false){
        $export_array=$this->params;
        // パラメータの除去
        $diff_keys=array_merge($this->exclude_params,$tmp_exclude_params);
        if(count($diff_keys)>0){
            $diff_key_array=array_fill_keys($diff_keys,0);
            $export_array=array_diff_key($export_array,$diff_key_array);
        }
        // 空パラメータの除去
        if($remove_empty){
            foreach($export_array as $key=>$value){
                if(empty($value)){
                    unset($export_array[$key]);
                }
            }
        }
        // 一時的に追加するパラメータの反映
        $export_array=array_merge($export_array,$tmp_add_params);
        return http_build_query($export_array);
    }
    public function __set($name, $value)
    {
        $this->params[$name]=$value;
    }
    public function __get($name)
    {
        return $this->params[$name]??null;
    }
    public function __toString(){ return $this->toString(); }
}
