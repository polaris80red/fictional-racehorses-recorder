<?php
class RaceGradeRow {
    public const INT_COLUMNS=[
        'id',
        'sort_number',
        'is_enabled',
    ];
    public const STR_COLUMNS=[
        'unique_name',
        'grade_name',
        'short_name',
        'search_grade',
        'category',
        'css_class_suffix',
    ];
    public $id;
    public $unique_name;
    public $grade_name;
    public $short_name;
    public $search_grade;
    public $category;
    public $css_class_suffix;
    public $sort_number;
    public $is_enabled;
    public static function getRowNames(){
        return array_merge(self::INT_COLUMNS,self::STR_COLUMNS);
    }
}
