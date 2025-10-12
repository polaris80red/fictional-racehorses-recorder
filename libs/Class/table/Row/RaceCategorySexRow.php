<?php
class RaceCategorySexRow extends TableRow {
    use TableRowValidate;
    public const INT_COLUMNS=[
        'id',
        'sort_number',
        'is_enabled',
    ];
    public const STR_COLUMNS=[
        'name',
        'short_name_3',
        'umm_category',
    ];
    public $id;
    public $name;
    public $short_name_3;
    public $umm_category;
    public $sort_number;
    public $is_enabled=1;
    /**
     * SET済みのプロパティを検証して$hasErrorsをセットする
     * @return bool エラーがなければtrue
     */
    public function validate(): bool
    {
        $this->validateStrLength($this->name,'名称',50);
        $this->validateStrLength($this->short_name_3,'3字略名',3);
        $this->validateStrLength($this->umm_category,'擬人化用名称',16);
        $this->validateInt($this->sort_number,'表示順補正');
        return !$this->hasErrors;
    }
}
