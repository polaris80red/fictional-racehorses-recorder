<?php
/**
 * horse_id で指定したIDの競走馬レコードの馬名を返す
 */
require_once dirname(__DIR__).'/libs/init.php';
header('Content-Type: text/plain; charset=UTF-8');
$pdo=getPDO();
$horse_id=filter_input(INPUT_GET,'horse_id');
if(!$horse_id){exit;}
$horse=new Horse();
$horse->setDataById($pdo, $horse_id);
if(!$horse->record_exists){ exit; }
echo $horse->name_ja?:$horse->name_en;
