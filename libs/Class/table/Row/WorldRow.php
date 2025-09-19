<?php
class WorldRow extends TableRow {
    use TableRowValidate;
    public const STR_COLUMNS=[
        'name',
        'auto_id_prefix',
    ];
    public const INT_COLUMNS=[
        'id',
        'guest_visible',
        'sort_priority',
        'sort_number',
        'is_enabled',
    ];
    public int $id=0;
    public $name;
    public $guest_visible=1;
    public $auto_id_prefix='';
    public $sort_priority=0;
    public $sort_number=null;
    public $is_enabled=1;
    public function validate(): bool
    {
        $this->validateRequired($this->name,'名称');
        $this->validateStrLength($this->name,'名称',50);
        $this->varidateInt($this->sort_priority,'表示順優先度');
        $this->varidateInt($this->sort_number,'表示順補正');
        $this->validateStrLength($this->auto_id_prefix,'自動ID接頭辞',30);
        return !$this->hasErrors;
    }
}
