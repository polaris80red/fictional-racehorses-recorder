<?php
class ThemesRow extends TableRow {
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
}
