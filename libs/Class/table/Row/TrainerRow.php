<?php
class TrainerRow extends TableRow {
    use TableRowValidate;
    public const INT_COLUMNS=[
        'id',
        'is_anonymous',
        'is_enabled',
    ];
    public const STR_COLUMNS=[
        'unique_name',
        'name',
        'short_name_10',
        'affiliation_name',
    ];
    public $id;
    public $unique_name;
    public $name;
    public $short_name_10;
    public $affiliation_name;
    public $is_anonymous=0;
    public $is_enabled=1;
    /**
     * SET済みのプロパティを検証して$hasErrorsをセットする
     * @return bool エラーがなければtrue
     */
    public function validate(): bool
    {
        $this->validateRequired($this->unique_name,'キー名称');
        $this->validateStrLength($this->unique_name,'キー名称',32);
        $this->validateStrLength($this->name,'氏名',50);
        $this->validateStrLength($this->short_name_10,'10字以内略名',10);
        $this->validateStrLength($this->affiliation_name,'所属',10);
        return !$this->hasErrors;
    }
}
