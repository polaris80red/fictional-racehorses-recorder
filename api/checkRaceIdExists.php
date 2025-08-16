<?php
/**
 * race_id として指定したIDのレース結果レコードが存在するか確認
 */
require_once dirname(__DIR__).'/libs/init.php';
header('Content-Type: text/plain; charset=UTF-8');
$pdo=getPDO();
$race_id=trim(filter_input(INPUT_GET,'race_id'));
if(!$race_id){ exit; }
$race_data = new RaceResults($pdo, $race_id);
if(!$race_data->record_exists){
    echo "false";
}else{
    echo "true";
}
