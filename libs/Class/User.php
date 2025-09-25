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
    public function isSuperAdmin():bool{
        return $this->isSuperAdmin;
    }
    /**
     * ユーザー管理を使用可能かどうかの判定
     */
    public function canUserManage():bool {
        $allowRoles=[Role::Administrator];
        return $this->isSuperAdmin?:in_array($this->role,$allowRoles);
    }
    public function canHorseEdit(HorseRow $horse):bool {
        $allowRoles=[Role::Administrator,Role::Maintainer,Role::Editor];
        if($this->isSuperAdmin||in_array($this->role,$allowRoles)){
            return true;
        }
        if($this->role===Role::Author && ($horse->created_by??null) === $this->id){
            // 投稿者は自身で登録した馬のみ編集可能
            return true;
        }
        return false;
    }
    /**
     * ほかのユーザーが登録した競走馬を含む可能性がある一括編集画面を使用可能かどうかの判定
     */
    public function canEditOtherHorse():bool {
        $allowRoles=[Role::Administrator,Role::Maintainer,Role::Editor];
        return $this->isSuperAdmin?:in_array($this->role,$allowRoles);
    }
    /**
     * レース情報の編集権限
     */
    public function canEditRace(RaceRow $race):bool {
        $allowRoles=[Role::Administrator,Role::Maintainer,Role::Editor];
        if($this->isSuperAdmin||in_array($this->role,$allowRoles)){
            return true;
        }
        if($this->role===Role::Author && ($race->created_by??null) === $this->id){
            // 投稿者は自身で登録したレースのみ編集可能
            return true;
        }
        return false;
    }
    /**
     * レース情報の削除
     */
    public function canDeleteRace():bool {
        $allowRoles=[Role::Administrator,Role::Maintainer,Role::Editor];
        return $this->isSuperAdmin?:in_array($this->role,$allowRoles);
    }
    /**
     * 通常マスタの管理権限
     */
    public function canManageMaster(){
        $allowRoles=[Role::Administrator,Role::Maintainer];
        return $this->isSuperAdmin?:in_array($this->role,$allowRoles);
    }
    /**
     * システム設定の変更権限
     */
    public function canManageSystemSettings():bool{
        $allowRoles=[Role::Administrator];
        return $this->isSuperAdmin?:in_array($this->role,$allowRoles);
    }
}
