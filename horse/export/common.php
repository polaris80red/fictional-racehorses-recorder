<?php
/**
 * カスタマイズを想定した追加のエクスポート処理のベースになるファイルです
 */
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
InAppUrl::init(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="競走馬情報";
$page->ForceNoindex();
$session=new Session();

$page->error_return_url=InAppUrl::to("horse/search");
$page->error_return_link_text="競走馬検索に戻る";
$pdo= getPDO();

$horse_id=(string)filter_input(INPUT_GET,'horse_id');
if($horse_id===''){
    $page->error_msgs[]="競走馬ID未指定";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
$horse=Horse::getByHorseId($pdo,$horse_id);
if($horse===false){
    $page->error_msgs[]="競走馬情報取得失敗";
    $page->error_msgs[]="入力ID：{$horse_id}";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
include (new TemplateImporter('horse/export/common.inc.php'));
