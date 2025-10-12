<?php
class RaceWeekRow extends TableRow {
    use TableRowValidate;
    public const INT_COLUMNS=[
        'id',
        'month',
        'month_grouping',
        'umm_month_turn',
        'sort_number',
        'is_enabled',
    ];
    public const STR_COLUMNS=[
        'name',
    ];
    public $id;
    public $name;
    public $month;
    public $month_grouping;
    public $umm_month_turn=0;
    public $sort_number;
    public $is_enabled=1;
    /**
     * SET済みのプロパティを検証して$hasErrorsをセットする
     * @return bool エラーがなければtrue
     */
    public function validate(): bool
    {
        $this->validateStrLength($this->name,'名称',50);
        $this->validateInt($this->month,'月',1,12);
        $this->validateInt($this->month_grouping,'月',0,129);
        $this->validateInt($this->umm_month_turn,'ターン',0,4);
        $this->validateInt($this->sort_number,'表示順補正');
        return !$this->hasErrors;
    }
}
