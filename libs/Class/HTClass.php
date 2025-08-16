<?php
class HTClass{
    public static function printGrade($grade){ print self::grade($grade); }
    public static function grade($grade){
        switch($grade){
            case "G1":
            case "Jpn1":
                $add_class='race_grade_1';break;
            case "G2":
            case "Jpn2":
                $add_class='race_grade_2';break;
            case "G3":
            case "Jpn3":
                $add_class='race_grade_3';break;
            case "重賞":
                $add_class='race_grade_4';break;
            case "L":
                $add_class='race_grade_l';break;
            case "OP":
                $add_class='race_grade_op';break;
            default:
                $add_class="race_grade_none";
            }
        return $add_class;
    }
}
