<?php
class JockeyRow extends TableRow {
    public const INT_COLUMNS=[
        'id',
        'is_anonymous',
        'is_enabled',
    ];
    public const STR_COLUMNS=[
        'unique_name',
        'name',
        'short_name_10',
    ];
    public $id;
    public $unique_name;
    public $name;
    public $short_name_10;
    public $is_anonymous=0;
    public $is_enabled=1;
}
