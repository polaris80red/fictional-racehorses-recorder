<?php
class Users extends Table{
    public const TABLE = 'mst_users';
    public const UNIQUE_KEY_COLUMN="id";
    protected const DEFAULT_ORDER_BY='`id` ASC';
    public const ROW_CLASS = UsersRow::class;

    public static function getById(PDO $pdo, $id, $pdo_param_mode=PDO::PARAM_INT){
        $result = self::getByUniqueKey($pdo,'id',$id,$pdo_param_mode);
        if($result==false){
            return false;
        }
        return (new (static::ROW_CLASS))->setFromArray($result);
    }
    public static function getByUsername(PDO $pdo, $username){
        $result = self::getByUniqueKey($pdo,'username',$username,PDO::PARAM_STR);
        if($result==false){
            return false;
        }
        return (new (static::ROW_CLASS))->setFromArray($result);
    }
    public static function getByToken(PDO $pdo, $login_url_token){
        $result = self::getByUniqueKey($pdo,'login_url_token',$login_url_token,PDO::PARAM_STR);
        if($result==false){
            return false;
        }
        return (new (static::ROW_CLASS))->setFromArray($result);
    }
}
