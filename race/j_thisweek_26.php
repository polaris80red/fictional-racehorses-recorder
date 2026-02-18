<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
InAppUrl::init(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="今週の注目レース/出走馬情報 ";
$session=new Session();
// 暫定でログイン＝編集可能
$page->is_editable=Session::isLoggedIn();

$page->error_return_url=$page->to_race_list_path;
$page->error_return_link_text="レース検索に戻る";


$pdo= getPDO();

if(empty($_GET['race_id'])){
    $page->error_msgs[]="レースID未指定";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
$race_id=filter_input(INPUT_GET,'race_id');
# レース情報取得
$race = Race::getByRaceId($pdo, $race_id);
if(!$race){
    $page->error_msgs[]="レース情報取得失敗";
    $page->error_msgs[]="入力ID：{$race_id}";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
$week_data=RaceWeek::getById($pdo,$race->week_id);
$week_month=$week_data->month;
$turn=$week_data->umm_month_turn;

$resultsGetter=new RaceResultsGetter($pdo,$race_id,$race->year);
$resultsGetter->pageIsEditable=$page->is_editable;
$resultsGetter->addOrderParts([
    "`jra_thisweek_horse_sort_number` IS NULL",
    "`jra_thisweek_horse_sort_number` ASC",
    "`horse`.`name_ja` ASC",
    "`horse`.`name_en` ASC",
]);
$table_data=$resultsGetter->getTableData();
$hasThisweek25=$resultsGetter->hasThisweek25;
$hasThisweek26=$resultsGetter->hasThisweek26;
$hasSps=$resultsGetter->hasSps;
if(!$hasThisweek26){
    $page->error_return_url=InAppUrl::to('race/syutsuba.php',['race_id'=>$race_id]);
    $page->error_return_link_text="出馬表に戻る";
    $page->error_msgs[]="出走馬情報が未登録のレースです";
    $page->error_msgs[]="入力ID：{$race_id}";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}

$title="出走馬情報：{$race->race_name} 今週の注目レース";
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?=h($page->renderTitle($title))?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?=$page->renderBaseStylesheetLinks()?>
<style>
p {font-size:90%;}
.tw26_text_box{
    padding-bottom: 0.25em;
}
.tw26_text_box li{
    margin-top: 0.5em;
}
.tw26_title_box{
    margin-left:1em;
}
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php include (new TemplateImporter('race/race_page-content_header.inc.php'));?>
<?php
    // 背景色
    $title_background_color="#666666";
    $text_background_color="#EEEEEE";
    if($race->grade=="G1"){
        $title_background_color="#00619a";
        $text_background_color="#e8f3fb";
    }else if($race->grade=="G2"){
        $title_background_color="#b62a2b";
        $text_background_color="#fef7f7";
    }else if($race->grade=="G3"){
        $title_background_color="#2E7959";
        $text_background_color="#f0faf6";
    }
?>
<?php foreach ($table_data as $data):?>
<?php
    $horse=$data->horseRow;
    $result=$data->resultRow;
    $sex_str=sex2String($result->sex?:$horse->sex);
    $age=$horse->birth_year==null?'':$race->year-$horse->birth_year;
    if(empty($result->jra_thisweek_horse_26_title)&&empty($result->jra_thisweek_horse_26_1)&&$result->jra_thisweek_horse_sort_number==0){ continue; }
?><section style="border: solid 1px #CCC; padding: 0.2em 0.5em; max-width: 940px;margin-top: 8px;">
<div><?php if(false && $page->is_editable): ?>
<a href="<?=$page->to_app_root_path?>race/horse_jra_article/form.php?race_id=<?=h($race_id)?>&horse_id=<?=h($horse->horse_id)?>">■</a>
<?php else: ?>■ <?php endif; ?>
<?php
    $training_country=$training_country=$result->training_country?:$horse->training_country;
    if(($race->is_jra==1 || $race->is_nar==1) && $training_country!='' && $training_country!='JPN'){
        echo "[外] ";
    }
    if($race->is_jra==1 && $result->is_affliationed_nar==1){
        echo "[地] ";
    }
    if($race->is_jra==0 && $race->is_nar==0){
        echo "<span style=\"font-family:monospace;\">[".h($training_country)."]</span> ";
    }
    echo '<span style="font-weight:bold;"><a href="'.InAppUrl::to('horse/',['horse_id'=>$horse->horse_id]).'">';
    print_h($horse->name_ja?:$horse->name_en);
    echo "</a></span>";
    if($result->result_text==='回避'){ echo " 【出走取消】"; }
    print_h("　".$sex_str.$age."歳");
    print $data->trainerName?"　調教師：".$data->trainerName:'';
    print_h("（".($result->tc?:$horse->tc)."）");
?><hr>
<p style="font-size: 0.9em;">父：<?=h($horse->sire_name?:"□□□□□□")?><br>
母：<?=h($horse->mare_name?:"□□□□□□")?><br>
母の父：<?=h($horse->bms_name?:"□□□□□□")?><br></p>
</div>
<div class="tw26_text_box" style="background-color:<?=h($text_background_color)?>;color:#333;max-width:550px;">
<div class="tw26_title_box" style="background-color:<?=h($title_background_color)?>;color:#FFF;max-width:200px;">
    <div style="padding: 5px 20px 5px;"><?=h($result->jra_thisweek_horse_26_title?:"……")?></div>
</div>
<?php
    $text_list=explode("\n",str_replace(["\r","\r\n"],"\n",$result->jra_thisweek_horse_26_1??''));
    $text_list=array_diff($text_list,['']);
    $list=[];
    foreach($text_list as $val){
        $list[]="<li>".h(trim($val)?:'……')."</li>";
    }
?>
<ul>
    <?=implode("\n",$list)?>
</ul>
</div>
</section>
<?php endforeach; ?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>