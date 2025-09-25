<?php
class UsersRow extends TableRow {
    use TableRowValidate;
    public const STR_COLUMNS=[
        'username',
        'password_hash',
        'display_name',
        'login_enabled_from',
        'login_enabled_until',
        'last_login_at',
        'login_locked_until',
        'created_at',
        'updated_at',
    ];
    public const INT_COLUMNS=[
        'id',
        'role',
        'failed_login_attempts',
        'is_enabled',
        'created_by',
        'updated_by',
    ];
    public int|null $id = null;
    public string $username ='';
    public string $password_hash='';
    public string $display_name = '';
    public int|null $role = null;
    public string|null $login_enabled_from = null;
    public string|null $login_enabled_until = null;
    public string|null $last_login_at = null;
    public int $failed_login_attempts = 0;
    public string|null $login_locked_until = null;
    public int $is_enabled = 1;
    public int|null $created_by = null;
    public int|null $updated_by = null;
    public string|null $created_at = null;
    public string|null $updated_at = null;

    public function validate(): bool
    {
        $this->validateStrLength($this->username,'ログインユーザー名',50);
        $this->validateStrLength($this->display_name,'表示名',50);
        return !$this->hasErrors;
    }
}
