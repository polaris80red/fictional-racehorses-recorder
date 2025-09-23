<?php
/**
 * ユーザークラス
 */
class User {
    private bool $isSuperAdmin=false;
    /**
     * 固定管理者の場合のインスタンス生成
     */
    public static function superAdmin():self {
        $user = new self();
        $user->isSuperAdmin=true;
        return $user;
    }
}
