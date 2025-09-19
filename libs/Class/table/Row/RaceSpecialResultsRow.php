<?php
class RaceSpecialResultsRow extends TableRow {
    use TableRowValidate;
    public const INT_COLUMNS=[
        'id',
        'is_registration_only',
        'is_excluded_from_race_count',
        'sort_number',
        'is_enabled',
    ];
    public const STR_COLUMNS=[
        'unique_name',
        'name',
        'short_name_2',
    ];
    public $id;
    public $unique_name;
    public $name;
    public $short_name_2;
    public $is_registration_only;
    public $is_excluded_from_race_count;
    public $sort_number;
    public $is_enabled=1;
    /**
     * SET済みのプロパティを検証して$hasErrorsをセットする
     * @return bool エラーがなければtrue
     */
    public function validate(): bool
    {
        $this->validateRequired($this->unique_name,'キー名称');
        $this->validateStrLength($this->unique_name,'キー名称',8);
        $this->validateStrLength($this->name,'名称',50);
        $this->validateStrLength($this->short_name_2,'2字略名',2);
        $this->varidateInt($this->sort_number,'表示順補正');
        return !$this->hasErrors;
    }
}
