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
$page->is_editable=Session::is_logined();

$page->error_return_url=$page->to_race_list_path;
$page->error_return_link_text="レース検索に戻る";


$pdo= getPDO();

$is_edit_mode = false;
if(filter_input(INPUT_GET,'mode')==='edit'){
    $is_edit_mode = true;
}
$is_edit_mode=true;
if(empty($_GET['race_id'])){
    $page->error_msgs[]="レースID未指定";
    $page->printCommonErrorPage();
    exit;
}
$race_id=filter_input(INPUT_GET,'race_id');
# レース情報取得
$race = new Race($pdo, $race_id);
if(!$race->record_exists){
    $page->error_msgs[]="レース情報取得失敗";
    $page->error_msgs[]="入力ID：{$race_id}";
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
$hasThisweek=$resultsGetter->hasThisweek;
$hasSps=$resultsGetter->hasSps;
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
<style>
p {font-size:90%;}
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php $page->printTitle(); ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php include (new TemplateImporter('race/race_page-content_header.inc.php'));?>
<hr>
<?php foreach ($table_data as $data):?>
<?php
    $horse=$data->horseRow;
    $result=$data->resultRow;
    $sex_str=sex2String($result->sex?:$horse->sex);
    $age=$horse->birth_year==null?'':$race->year-$horse->birth_year;
    if(empty($result->jra_thisweek_horse_1)&&empty($result->jra_thisweek_horse_2)&&$result->jra_thisweek_horse_sort_number==0){ continue; }
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
<div style="background-color:#FEC;border:solid 1px #CCC;max-width:550px;">
    <span style="font-weight:bold;">［ここに注目！］</span>
    <div style="padding: 5px 20px 5px;font-size:0.85em;"><?=h($result->jra_thisweek_horse_1?:"……")?></div>
</div>
<p><span style="font-weight:bold;">［出走馬情報］</span><br><?=h($result->jra_thisweek_horse_2?:"……")?></p>
</section>
<?php endforeach; ?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>