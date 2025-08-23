<?php
/**
 * 日付から1年間の中の開催週を判定
 */
require_once dirname(__DIR__).'/libs/init.php';
header('Content-Type: text/plain; charset=UTF-8');
$pdo=getPDO();
$input_date=filter_input(INPUT_GET,'date');
if($input_date==''){ exit; }
echo getWeekByDate($input_date);
