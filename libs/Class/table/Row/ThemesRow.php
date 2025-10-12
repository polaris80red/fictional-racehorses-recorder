<?php
class ThemesRow extends TableRow {
    use TableRowValidate;
    public const INT_COLUMNS=[
        'id',
        'sort_priority',
        'sort_number',
        'is_enabled',
    ];
    public const STR_COLUMNS=[
        'name',
        'dir_name',
    ];
    public $id              =0;
    public $name            ='';
    public $dir_name        ='';
    public $sort_priority   =0;
    public $sort_number     =null;
    public $is_enabled      =1;
    /**
     * SET済みのプロパティを検証して$hasErrorsをセットする
     * @return bool エラーがなければtrue
     */
    public function validate(): bool
    {
        $this->validateStrLength($this->name,'名称',32);
        $this->validateStrLength($this->dir_name,'ディレクトリ名',100);
        $this->validateInt($this->sort_priority,'表示順優先度');
        $this->validateInt($this->sort_number,'表示順補正');
        return !$this->hasErrors;
    }
}
