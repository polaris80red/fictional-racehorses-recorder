<?php
class RaceGradeRow extends TableRow {
    public const INT_COLUMNS=[
        'id',
        'sort_number',
        'show_in_select_box',
        'is_enabled',
    ];
    public const STR_COLUMNS=[
        'unique_name',
        'short_name',
        'search_grade',
        'category',
        'css_class_suffix',
    ];
    public $id;
    public $unique_name;
    public $short_name;
    public $search_grade;
    public $category;
    public $css_class_suffix;
    public $sort_number;
    public $show_in_select_box=1;
    public $is_enabled=1;
}
