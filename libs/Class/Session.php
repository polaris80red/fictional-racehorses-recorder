<?php
class Session{
    public const PUBLIC_PARAM_KEY='public';
    private $is_logined=false;

    public function __construct(){
        if(isset($_SESSION[APP_INSTANCE_KEY]['isLoggedIn'])){
            $this->is_logined=$_SESSION[APP_INSTANCE_KEY]['isLoggedIn']?true:false;
        }
    }
    public static function is_logined(){
        if(!isset($_SESSION[APP_INSTANCE_KEY]['isLoggedIn'])){ return false; }
        return $_SESSION[APP_INSTANCE_KEY]['isLoggedIn']?true:false;
    }
    public function Login(){
        $this->is_logined = $_SESSION[APP_INSTANCE_KEY]['isLoggedIn'] = true;
    }
    public function Logout(){
        $this->is_logined = $_SESSION[APP_INSTANCE_KEY]['isLoggedIn'] = false;
    }
    public function __set($property, $value){
        if(!isset($_SESSION[APP_INSTANCE_KEY][self::PUBLIC_PARAM_KEY])){
            $_SESSION[APP_INSTANCE_KEY][self::PUBLIC_PARAM_KEY]=[];
        }
        $_SESSION[APP_INSTANCE_KEY][self::PUBLIC_PARAM_KEY][$property]=$value;
    }
    public function __get($property){
        if(isset($_SESSION[APP_INSTANCE_KEY][self::PUBLIC_PARAM_KEY][$property])){
            return $_SESSION[APP_INSTANCE_KEY][self::PUBLIC_PARAM_KEY][$property];
        }
        return null;
    }
}
