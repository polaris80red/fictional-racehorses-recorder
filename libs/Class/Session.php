<?php
class Session{
    public const PUBLIC_PARAM_KEY='public';
    /**
     * ログイン中かどうかの判定
     */
    public static function isLoggedIn(){
        if(isset($_SESSION[APP_INSTANCE_KEY]['currentUser'])){
            return true;
        }
        return false;
    }
    /**
     * 通常のログイン処理
     */
    public static function Login(UsersRow $user){
        $_SESSION[APP_INSTANCE_KEY]['currentUser']['id'] = $user->id;
        $_SESSION[APP_INSTANCE_KEY]['currentUser']['role'] = $user->role;
        $_SESSION[APP_INSTANCE_KEY]['currentUser']['isSuperAdmin'] = false;
    }
    public static function Logout(){
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
    public static function loginSuperAdmin(UsersRow|null $user=null){
        $_SESSION[APP_INSTANCE_KEY]['currentUser']['id'] = $user!==null?$user->id:null;
        $_SESSION[APP_INSTANCE_KEY]['currentUser']['role'] = $user!==null?$user->role:null;
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
        $id=$_SESSION[APP_INSTANCE_KEY]['currentUser']['id']??null;
        $role=$_SESSION[APP_INSTANCE_KEY]['currentUser']['role']??null;
        // 設定ファイルによる管理者の場合用のセット処理
        if(($_SESSION[APP_INSTANCE_KEY]['currentUser']['isSuperAdmin']??false)===true){
            $user=User::superAdmin($id,$role);
            return $user;
        }
        $user =new User($id,$role);
        // TODO: 通常のデータベースからのユーザー情報・権限セット処理
        return $user;
    }
}
