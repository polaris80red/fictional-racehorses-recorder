<?php
/**
 * スプレッドシートやExcelへのペーストを想定したタブ区切りテキスト形式で戦績を出力します
 */
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
InAppUrl::init(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="競走馬戦績エクスポート";
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
$page->renderErrorsAndExitIfAny($errorHeader);
$get_order=strtoupper((string)filter_input(INPUT_GET,'horse_history_order'));
$date_order = in_array($get_order,['ASC','DESC'])?$get_order:'ASC';
$show_registration_only=(bool)filter_input(INPUT_GET,'show_registration_only');

include (new TemplateImporter('horse/export/history_tsv.inc.php'));
