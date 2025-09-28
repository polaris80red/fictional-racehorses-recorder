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
$page->title="競走馬情報エクスポート";
$page->ForceNoindex();
$session=new Session();

$pdo= getPDO();
$page->setErrorReturnLink("競走馬検索に戻る",InAppUrl::to("horse/search"));
$errorHeader="HTTP/1.1 404 Not Found";
do{
    $horse_id=(string)filter_input(INPUT_GET,'horse_id');
    if($horse_id==''){
        $page->addErrorMsg("競走馬ID未指定");
        break;
    }
    $horse=Horse::getByHorseId($pdo,$horse_id);
    if($horse===false){
        $page->addErrorMsg("競走馬情報取得失敗\n入力ID：{$horse_id}");
        break;
    }
}while(false);
if($page->error_exists){
    header($errorHeader);
    $page->printCommonErrorPage();
    exit;
}
include (new TemplateImporter('horse/export/common.inc.php'));
