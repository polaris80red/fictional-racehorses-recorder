<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
InAppUrl::init(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース後メモ";
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
if(ENABLE_ACCESS_COUNTER){
    ArticleCounter::countup($pdo,'race_after_note',$race_id);
}
$week_data=RaceWeek::getById($pdo,$race->week_id);
$week_month=$week_data->month;
$turn=$week_data->umm_month_turn;
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
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php include (new TemplateImporter('race/race_page-content_header.inc.php'));?>
<hr>
<?php
$horse_tbl=Horse::TABLE;
$race_tbl=Race::TABLE;
$race_results_tbl=RaceResults::TABLE;
$sql=<<<END
SELECT
`r_retults`.*
,`horse`.`name_ja`
,`horse`.`name_en`
,`horse`.`tc` AS 'horse_tc'
,`horse`.`training_country` AS 'horse_training_country'
,`horse`.`is_affliationed_nar` AS 'horse_is_affliationed_nar'
,`horse`.`sex`
,`horse`.`birth_year`
,`horse`.`sire_name`
,`horse`.`mare_name`
,`horse`.`bms_name`
,`horse`.`color`
,`race`.*
FROM `{$race_tbl}` AS `race`
LEFT JOIN `{$race_results_tbl}` AS `r_retults` ON `race`.`race_id`=`r_retults`.`race_id`
LEFT JOIN {$horse_tbl} AS `horse` ON `r_retults`.`horse_id`=`horse`.`horse_id`
WHERE `race`.`race_id`=:race_id
ORDER BY
`r_retults`.`frame_number` IS NULL,
`r_retults`.`frame_number` ASC,
`r_retults`.`horse_number` ASC,
`horse`.`name_ja` ASC, `horse`.`name_en` ASC;
END;

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':race_id', $race_id, PDO::PARAM_STR);
$flag = $stmt->execute();
$table_data=[];
while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data['sex_str']=sexTo1Char($data['sex']);
    $data['age']=empty($data['birth_year'])?'':($race->year-$data['birth_year']);
    $data['result_obj']=(new RaceResultsRow())->setFromArray($data);
    $table_data[]=$data;
}
?>
<?php foreach ($table_data as $data): ?>
    <?php if($data['result_obj']->race_after_note==''){ continue; }?>
    <section>
        <?php if($page->is_editable): ?>
        <a href="<?=InAppUrl::to(Routes::HORSE_RACE_RESULT_EDIT,['race_id'=>$race_id,'horse_id'=>$data['horse_id'],'edit_mode'=>1])?>">■</a>
        <?php else: ?>■<?php endif; ?>
        <a href="<?=h(InAppUrl::to('horse/',['horse_id'=>$data['horse_id']]))?>" style="text-decoration:none;"><?=$data['name_ja']?:$data['name_en']?></a>
        <br>
        <?=nl2br(h($data['result_obj']->race_after_note?:"……"))?>
    </section>
    <hr>
<?php endforeach; ?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>