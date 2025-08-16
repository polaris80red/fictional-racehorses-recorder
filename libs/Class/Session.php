<?php
class Session{
    public const PUBLIC_PARAM_KEY='public';
    private $is_logined=false;

    public function __construct(){
        if(isset($_SESSION['is_logined'])){
            $this->is_logined=$_SESSION['is_logined']?true:false;
        }
    }
    public static function is_logined(){
        if(!isset($_SESSION['is_logined'])){ return false; }
        return $_SESSION['is_logined']?true:false;
    }
    public function Login(){
        $this->is_logined = $_SESSION['is_logined'] = true;
    }
    public function Logout(){
        $this->is_logined = $_SESSION['is_logined'] = false;
    }
    public function __set($property, $value){
        if(!isset($_SESSION[self::PUBLIC_PARAM_KEY])){
            $_SESSION[self::PUBLIC_PARAM_KEY]=[];
        }
        $_SESSION[self::PUBLIC_PARAM_KEY][$property]=$value;
    }
    public function __get($property){
        if(isset($_SESSION[self::PUBLIC_PARAM_KEY][$property])){
            return $_SESSION[self::PUBLIC_PARAM_KEY][$property];
        }
        return null;
    }
}
