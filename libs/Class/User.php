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
    /**
     * ユーザー管理を使用可能かどうかの判定
     */
    public function canUserManage():bool {
        return $this->isSuperAdmin||($this->role===Role::Administrator);
    }
    public function canHorseEdit(HorseRow $horse):bool {
        if($this->role===Role::Author && ($horse->created_by??null) !== $this->id){
            // 投稿者は自身で登録した馬以外は編集不可
            return false;
        }
        return true;
    }
    /**
     * ほかのユーザーが登録した競走馬を含む可能性がある一括編集画面を使用可能かどうかの判定
     */
    public function canEditOtherHorse():bool {
        if($this->role===Role::Author){
            return false;
        }
        return true;
    }
}
