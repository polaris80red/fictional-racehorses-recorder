<?php
class RaceWeek extends Table{
    public const TABLE = 'mst_race_week';
    public const UNIQUE_KEY_COLUMN="id";
    protected const DEFAULT_ORDER_BY
    ='`sort_number` IS NULL, `sort_number` ASC, `id` ASC';
}
