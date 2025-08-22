<?php
class RaceCourseRow extends TableRow {
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
}
