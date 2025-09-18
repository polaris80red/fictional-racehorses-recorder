<?php
class WorldRow extends TableRow {
    public const STR_COLUMNS=[
        'name',
        'auto_id_prefix',
    ];
    public const INT_COLUMNS=[
        'id',
        'guest_visible',
        'sort_priority',
        'sort_number',
        'is_enabled',
    ];
    public int $id=0;
    public $name;
    public $guest_visible=1;
    public $auto_id_prefix='';
    public $sort_priority=0;
    public $sort_number=null;
    public $is_enabled=1;
}
