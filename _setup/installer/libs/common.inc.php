<?php
class InstallerSession {
    public static function isLogdIn(){
        return $_SESSION[APP_INSTANCE_KEY]['installer']['isLogdIn']??false;
    }
    public static function login(){
        $_SESSION[APP_INSTANCE_KEY]['installer']['isLogdIn']=true;
    }
    public static function logout(){
        $_SESSION[APP_INSTANCE_KEY]['installer']['isLogdIn']=false;
    }
}
