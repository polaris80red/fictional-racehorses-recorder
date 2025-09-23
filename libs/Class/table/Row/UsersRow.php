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
        'created_at',
        'updated_at',
    ];
    public const INT_COLUMNS=[
        'id',
        'is_enabled',
        'created_by',
        'updated_by',
    ];
    public int|null $id = null;
    public string $username ='';
    public string $password_hash='';
    public string $display_name = '';
    public string|null $login_enabled_from = null;
    public string|null $login_enabled_until = null;
    public string|null $last_login_at = null;
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
