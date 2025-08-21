<?php
class RaceWeekRow extends TableRow {
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
}
