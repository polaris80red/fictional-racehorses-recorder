<?php
class Search{
    protected const SESSION_PARENT_KEY='';
    protected $params=[];
    protected $setting=null;
    public function setSetting(Setting $setting){
        $this->setting=$setting;
        return $this;
    }
    protected function getSessionOrGet($param_name){
        $param='';
        if(isset($_SESSION[static::SESSION_PARENT_KEY][$param_name])){
            $param=$_SESSION[static::SESSION_PARENT_KEY][$param_name];
        }
        if(isset($_GET[$param_name])){
            $param=filter_input(INPUT_GET,$param_name);
        }
        return $param;
    }
    protected function getBySession($param_name, $default=''){
        $param=$default;
        
        if(isset($_SESSION[static::SESSION_PARENT_KEY][$param_name])){
            $param=$_SESSION[static::SESSION_PARENT_KEY][$param_name];
        }
        return $param;
    }
    protected function setSessionAndParam(string $param_name,$value){
        $this->setToThis($param_name,$value);
        $this->setToSessionByParamName($param_name);
    }
    protected function setToThis(string $param_name,$value){
        $this->{$param_name}=$value;
    }
    protected function setToSession(string $param_name,$value){
        $_SESSION[static::SESSION_PARENT_KEY][$param_name]=$value;
    }
    protected function setToSessionByParamName(string $param_name){
        $this->setToSession($param_name,$this->{$param_name});
    }
    protected function setToSessionByParamNameArray(array $params){
        foreach($params as $param_name){
            $this->setToSessionByParamName($param_name);
        }
    }
    protected function setBySessionByParamNameArray(array $params){
        if(!isset($_SESSION[static::SESSION_PARENT_KEY])){
            return false;
        }
        $session=$_SESSION[static::SESSION_PARENT_KEY];
        foreach($params as $param_name){
            if(isset($session[$param_name])){
                $this->{$param_name}=$session[$param_name];
            }
        }
        return;
    }
    /**
     * 配列で名称を指定したメンバ変数を配列にして取得
     * @param array $param_names param name list
     * @param array $remove_params remove param name list
     * @return array
     */
    protected function toArray(array $param_names, array $remove_param_names=[]){
        $params=[];
        foreach($param_names as $param_name){
            $params[$param_name]=$this->{$param_name};
        }
        // 指定したキーを除去
        if(count($remove_param_names)>0){
            $remove_tmp_array=array_fill_keys($remove_param_names, false);
            $params=array_diff_key($params,$remove_tmp_array);
        }
        return $params;
    }
    /**
     * @param array $param_name_array param name
     * @param array $remove_params remove param name
     * @return string
     */
    protected function getUrlParamByArray(array $param_name_array, array $remove_param_names=[]){
        $params=$this->toArray($param_name_array,$remove_param_names);
        if(count($params)==0){
            return '';
        }
        return http_build_query($params);
    }
    public function __set($property, $value){
        $this->params[$property]=$value;
    }
    public function __get($property){
        if(isset($this->params[$property])){
            return $this->params[$property];
        }
        return null;
    }
}
