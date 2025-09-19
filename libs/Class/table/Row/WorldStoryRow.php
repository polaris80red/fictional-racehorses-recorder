<?php
class WorldStoryRow extends TableRow {
    use TableRowValidate;
    public const STR_COLUMNS=[
        'name',
        'config_json',
    ];
    public const INT_COLUMNS=[
        'id',
        'guest_visible',
        'sort_priority',
        'sort_number',
        'is_read_only',
        'is_enabled',
    ];
    public int $id=0;
    public $name='';
    public $guest_visible=1;
    public $config_json=null;
    public $sort_priority=0;
    public $sort_number=null;
    public $is_read_only=0;
    public $is_enabled=1;

    public function getDecodedConfig(){
        return $this->config_json===null?[]:json_decode($this->config_json);
    }
    public function setConfig($config_obj){
        $this->config_json = json_encode($config_obj,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
    }
    public function validate(): bool
    {
        $this->validateRequired($this->name,'名称');
        $this->validateStrLength($this->name,'名称',50);
        $this->varidateInt($this->sort_priority,'表示順優先度');
        $this->varidateInt($this->sort_number,'表示順補正');
        return !$this->hasErrors;
    }
}
