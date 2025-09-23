<?php
/**
 * ユーザークラス
 */
class User {
    private int|null $id=null;
    private int|null $role=null;
    private bool $isSuperAdmin=false;
    public function __construct(int|null $id=null,int|null $role=null){
        $this->id=$id;
        $this->role=$role;
    }
    /**
     * 固定管理者の場合のインスタンス生成
     */
    public static function superAdmin(int|null $id=null,int|null $role=null):self {
        $user = new self($id,$role);
        $user->isSuperAdmin=true;
        return $user;
    }
    public function getId(){
        return $this->id;
    }
}
