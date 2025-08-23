<?php
class RaceSpecialResultsRow extends TableRow {
    public const INT_COLUMNS=[
        'id',
        'is_registration_only',
        'sort_number',
        'is_enabled',
    ];
    public const STR_COLUMNS=[
        'unique_name',
        'name',
        'short_name_2',
    ];
    public $id;
    public $unique_name;
    public $name;
    public $short_name_2;
    public $is_registration_only;
    public $sort_number;
    public $is_enabled=1;
}
