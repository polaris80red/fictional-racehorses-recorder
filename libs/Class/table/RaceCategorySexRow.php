<?php
class RaceCategorySexRow extends TableRow {
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
}
