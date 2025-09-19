<?php
class AffiliationRow extends TableRow {
    use TableRowValidate;
    public const INT_COLUMNS=[
        'id',
        'sort_number',
        'show_in_select_box',
        'is_enabled',
    ];
    public const STR_COLUMNS=[
        'unique_name',
    ];
    public $id;
    public $unique_name;
    public $sort_number;
    public $show_in_select_box=1;
    public $is_enabled=1;
    /**
     * SET済みのプロパティを検証して$hasErrorsをセットする
     * @return bool エラーがなければtrue
     */
    public function validate(): bool
    {
        $this->validateRequired($this->unique_name,'キー名称');
        $this->validateStrLength($this->unique_name,'キー名称',10);
        $this->varidateInt($this->sort_number,'表示順補正');
        return !$this->hasErrors;
    }
}
