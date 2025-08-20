<?php
class AffiliationRow extends TableRow {
    public const INT_COLUMNS=[
        'id',
        'sort_number',
        'show_in_select_box',
        'is_enabled',
    ];
    public const STR_COLUMNS=[
        'name',
    ];
    public $id;
    public $name;
    public $sort_number;
    public $show_in_select_box=1;
    public $is_enabled=1;
}
