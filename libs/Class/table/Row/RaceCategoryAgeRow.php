<?php
class RaceCategoryAgeRow extends TableRow {
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
}
