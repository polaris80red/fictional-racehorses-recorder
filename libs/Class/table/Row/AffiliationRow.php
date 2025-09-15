<?php
class AffiliationRow extends TableRow {
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
}
