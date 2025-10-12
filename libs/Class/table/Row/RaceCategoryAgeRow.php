<?php
class RaceCategoryAgeRow extends TableRow {
    use TableRowValidate;
    public const INT_COLUMNS=[
        'id',
        'search_id',
        'sort_number',
        'is_enabled',
    ];
    public const STR_COLUMNS=[
        'name',
        'short_name_2',
        'name_umamusume',
    ];
    public $id;
    public $search_id;
    public $name;
    public $short_name_2;
    public $name_umamusume;
    public $sort_number;
    public $is_enabled=1;
    /**
     * SET済みのプロパティを検証して$hasErrorsをセットする
     * @return bool エラーがなければtrue
     */
    public function validate(): bool
    {
        $this->validateInt($this->search_id,'検索ID',0,100);
        $this->validateStrLength($this->name,'名称',100);
        $this->validateStrLength($this->short_name_2,'2字略名',2);
        $this->validateStrLength($this->name_umamusume,'擬人化用名称',100);
        $this->validateInt($this->sort_number,'表示順補正');
        return !$this->hasErrors;
    }
}
