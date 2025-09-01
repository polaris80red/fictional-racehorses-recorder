<?php
class RaceYearWeek{
    public $year;
    public $week;
    public function __construct($year,$week){
        $this->year=$year;
        $this->week=$week;
    }
    public function nextWeek(int $week_num=1){
        $this->year += floor($week_num/52);
        $this->week += $week_num % 52;
        if($this->week > 52){
            $this->year += 1;
            $this->week = 1;
        }else if($this->week < 0){
            $this->year -= 1;
            $this->week = 52;
        }
        return $this;
    }
}
