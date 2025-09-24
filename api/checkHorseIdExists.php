<?php
/**
 * horse_id として指定したIDの競走馬レコードが存在するか確認
 */
require_once dirname(__DIR__).'/libs/init.php';
header('Content-Type: text/plain; charset=UTF-8');
$pdo=getPDO();
$horse_id=trim(filter_input(INPUT_GET,'horse_id'));
if(!$horse_id){exit;}
$horse=Horse::getByHorseId($pdo, $horse_id);
if($horse){
    echo "true";
}else{
    echo "false";
}
