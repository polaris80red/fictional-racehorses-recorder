<?php
/**
 * ユーザークラス
 */
class User {
    private int|null $id=null;
    private bool $isSuperAdmin=false;
    public function __construct(int|null $id=null){
        $this->id=$id;
    }
    /**
     * 固定管理者の場合のインスタンス生成
     */
    public static function superAdmin():self {
        $user = new self();
        $user->isSuperAdmin=true;
        return $user;
    }
    public function getId(){
        return $this->id;
    }
}
