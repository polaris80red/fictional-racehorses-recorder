<?php
class HorseTagRow extends TableRow {
    public const INT_COLUMNS=[
        'number',
        'is_enabled',
    ];
    public const STR_COLUMNS=[
        'horse_id',
        'tag_text',
        'created_at',
        'updated_at',
    ];
    public $number;
    public $horse_id;
    public $tag_text;
    public $is_enabled=1;
    public $created_at;
    public $updated_at;
}
