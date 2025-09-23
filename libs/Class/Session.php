<?php
class Session{
    public const PUBLIC_PARAM_KEY='public';
    /**
     * ログイン中かどうかの判定（新しい処理に置き換え終わったら廃止する）
     */
    public static function is_logined(){
        if(!isset($_SESSION[APP_INSTANCE_KEY]['isLoggedIn'])){ return false; }
        return $_SESSION[APP_INSTANCE_KEY]['isLoggedIn']?true:false;
    }
    public function Login(){
        $_SESSION[APP_INSTANCE_KEY]['isLoggedIn'] = true;
    }
    public static function Logout(){
        $_SESSION[APP_INSTANCE_KEY]['isLoggedIn'] = false;
        unset($_SESSION[APP_INSTANCE_KEY]['currentUser']);
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
    /**
     * 固定管理者でのログイン処理
     */
    public static function loginSuperAdmin(){
        $_SESSION[APP_INSTANCE_KEY]['currentUser']['isSuperAdmin'] = true;
    }
    /**
     * ログイン中ならUserインスタンス、ログイン中でなければnullを返す
     * @return User|null
     */
    public static function currentUser():User|null{
        static $user = null;
        if(!isset($_SESSION[APP_INSTANCE_KEY]['currentUser'])){
            return null;
        }
        if($user !== null){ return $user; }
        // 設定ファイルによる管理者の場合用のセット処理
        if(($_SESSION[APP_INSTANCE_KEY]['currentUser']['isSuperAdmin']??false)===true){
            $user=User::superAdmin();
            return $user;
        }
        $user =new User();
        // TODO: 通常のデータベースからのユーザー情報・権限セット処理
        return $user;
    }
}
