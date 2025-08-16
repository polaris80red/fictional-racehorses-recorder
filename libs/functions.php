<?php
function ifZero2Null($var){
    if($var==0){ return null; }
    return $var;
}
function ifZero2Empty($var){
    if($var==0){ return ''; }
    return $var;
}
function printResultClass($int){
    print getResultClass($int);
}
function tmpModifyFormat(DateTime $dateTime,string $modify,string $format=''){
    $tmpDateTime = clone $dateTime;
    $tmpDateTime->modify($modify);
    $result = $tmpDateTime->format($format);
    unset($tmpDateTime);
    return $result;
}
function getWeekDayJa($int){
    $week = ['日','月','火','水','木','金','土'];
    return $week[$int];
}
function sex2String($input, int $length_format=1){
    if($input==3){
        if($length_format===1){ return "セ"; }
        if($length_format===2){ return "せん"; }
    }
    switch((int)$input){
        case 1:
            return "牡";
            break;
        case 2:
            return "牝";
            break;
    }
    return "";
}
function sexTo1Char($v){
    if($v==1){
        return "牡";
    }else if($v==2){
        return "牝";
    }else if($v==3){
        return "セ";
    }
    return "";
}
function getResultClass($int){
    switch($int){
        case 1:
            $add_class='result_1st';break;
        case 2:
            $add_class='result_2nd';break;
        case 3:
            $add_class='result_3rd';break;
        default:
            $add_class="";
        }
    return $add_class;
}
function printGradeClass($grade){ print getGradeClass($grade);}
function getGradeClass($grade){
    switch($grade){
        case "G1":
            $add_class='block_g1';break;
        case "G2":
            $add_class='block_g2';break;
        case "G3":
            $add_class='block_g3';break;
        case "L":
            $add_class='block_ls';break;
        case "OP":
            $add_class='block_op';break;
        default:
            $add_class="";
        }
    return $add_class;
}
/**
* 馬IDで1件取得
*
* @pdo PDO
* @horse_id strng 競走馬ID
*/
function getHorseById($pdo, string $horse_id){
    $sql="SELECT * FROM `".Horse::TABLE."` WHERE `horse_id` LIKE :horse_id LIMIT 1;";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':horse_id', $horse_id, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function explodeAndTrim(string $string,array $sub_separaters=["　","\r","\n"]){
    $explode_array = explode(' ',str_replace($sub_separaters," ",$string));
    $results=[];
    foreach($explode_array as $val){
        $val=trim($val);
        if($val===''){ continue; }
        $results[]=$val;
    }
    return $results;
}