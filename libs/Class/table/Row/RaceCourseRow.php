<?php
class RaceCourseRow extends TableRow {
    use TableRowValidate;
    public const INT_COLUMNS=[
        'id',
        'sort_priority',
        'sort_number',
        'show_in_select_box',
        'is_enabled',
    ];
    public const STR_COLUMNS=[
        'unique_name',
        'short_name',
        'short_name_m',
    ];
    public $id;
    public $unique_name;
    public $short_name;
    public $short_name_m;
    public $sort_priority;
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
        $this->validateStrLength($this->unique_name,'キー名称',32);
        $this->validateStrLength($this->short_name,'短縮名',5);
        $this->validateStrLength($this->short_name_m,'出馬表向け短縮名',10);
        $this->validateInt($this->sort_priority,'表示順優先度');
        $this->validateInt($this->sort_number,'表示順補正');
        return !$this->hasErrors;
    }
}
