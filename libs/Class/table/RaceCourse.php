<?php
class RaceCourse extends Table{
    public const TABLE = 'mst_race_course';
    protected const DEFAULT_ORDER_BY
    ='`sort_number` IS NULL, `sort_number` ASC, `number` ASC';
}
