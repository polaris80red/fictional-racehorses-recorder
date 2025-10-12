<?php
class RaceGradeRow extends TableRow {
    use TableRowValidate;
    public const INT_COLUMNS=[
        'id',
        'sort_number',
        'show_in_select_box',
        'is_enabled',
    ];
    public const STR_COLUMNS=[
        'unique_name',
        'short_name',
        'search_grade',
        'category',
        'css_class',
    ];
    public $id;
    public $unique_name;
    public $short_name;
    public $search_grade;
    public $category;
    public $css_class;
    public $sort_number;
    public $show_in_select_box=1;
    public $is_enabled=1;
    /**
     * SET済みのプロパティを検証して$hasErrorsをセットする
     * @return bool エラーがなければtrue
     */
    public function validate(): bool
    {
        $this->validateStrLength($this->unique_name,'キー名称',8);
        $this->validateStrLength($this->short_name,'略名',10);
        $this->validateStrLength($this->search_grade,'検索用略名',8);
        $this->validateStrLength($this->category,'結果画面などの大分類',50);
        $this->validateStrLength($this->css_class,'CSSクラス',50);
        $this->validateInt($this->sort_number,'表示順補正');
        return !$this->hasErrors;
    }
}
