<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
defineAppRootRelPath(1);
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
<?php require_once APP_ROOT_DIR."/race/race_content_header.inc.php"; ?>
<hr>
<?php

# レース着順取得
$horse_tbl=Horse::TABLE;
$race_results_tbl=Race::TABLE;
$race_results_horse_tbl=RaceResults::TABLE;
$sql=<<<END
SELECT
`r_result`.*
,`master_horse`.`name_ja`
,`master_horse`.`name_en`
,`master_horse`.`tc` AS 'horse_tc'
,`master_horse`.`training_country` AS 'horse_training_country'
,`master_horse`.`is_affliationed_nar` AS 'horse_is_affliationed_nar'
,`master_horse`.`sex`
,`master_horse`.`birth_year`
,`master_horse`.`sire_name`
,`master_horse`.`mare_name`
,`master_horse`.`bms_name`
,`race`.*
FROM `{$race_results_tbl}` AS `race`
LEFT JOIN `{$race_results_horse_tbl}` AS `r_result` ON `race`.`race_id`=`r_result`.`race_results_id`
LEFT JOIN {$horse_tbl} AS `master_horse` ON `r_result`.`horse_id`=`master_horse`.`horse_id`
WHERE `race`.`race_id`=:race_id
ORDER BY
`jra_thisweek_horse_sort_number` IS NULL,
`jra_thisweek_horse_sort_number` ASC,
`master_horse`.`name_ja` ASC, `master_horse`.`name_en` ASC;
END;

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':race_id', $race_id, PDO::PARAM_STR);
$stmt->execute();
$table_data=[];
while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data['sex_str']=sex2String($data['sex']);
    $data['age']=empty($data['birth_year'])?'':($race->year-$data['birth_year']);
    $table_data[]=$data;
}

?>
<?php foreach ($table_data as $data):?>
<?php
    // 1件目からない場合
    if(empty($data['horse_id'])){
        continue;
    }
    if(empty($data['jra_thisweek_horse_1'])&&empty($data['jra_thisweek_horse_2'])&&$data['jra_thisweek_horse_sort_number']==0){ continue; }
?><section style="border: solid 1px #CCC; padding: 0.2em 0.5em; max-width: 940px;margin-top: 8px;">
<div><?php if(false && $page->is_editable): ?>
<a href="<?=$page->to_app_root_path?>race/horse_jra_article/form.php?race_id=<?=h($race_id)?>&horse_id=<?=h($data['horse_id'])?>">■</a>
<?php else: ?>■ <?php endif; ?>
<?php
    $training_country='';
    if(!empty($data['training_country'])){
        $training_country=$data['training_country'];
    }else{
        $training_country=$data['horse_training_country'];
    }
    if(($data['is_jra']==1 || $data['is_nar']==1)&& $training_country!='JPN'){
        echo "[外] ";
    }
    if($data['is_jra']==1&& $data['is_affliationed_nar']==1){
        echo "[地] ";
    }
    if($data['is_jra']==0 && $data['is_nar']==0){
        echo "<span style=\"font-family:monospace;\">[".h($data['training_country'])."]</span> ";
    }
    echo '<span style="font-weight:bold;"><a href="'.APP_ROOT_REL_PATH.'horse/?horse_id='.h($data['horse_id']).'">';
    print_h($data['name_ja']?:$data['name_en']);
    echo "</a></span>";
    if($data['result_text']==='回避'){ echo " 【出走取消】"; }
    print_h("　".$data['sex_str'].$data['age']."歳");
    if(!empty($data['tc'])){
        print_h("（{$data['tc']}）");
    }else{
        print_h("（{$data['horse_tc']}）");
    }
?><hr>
<p style="font-size: 0.9em;">父：<?=h($data['sire_name']?:"□□□□□□")?><br>
母：<?=h($data['mare_name']?:"□□□□□□")?><br>
母の父：<?=h($data['bms_name']?:"□□□□□□")?><br></p>
</div>
<div style="background-color:#FEC;border:solid 1px #CCC;max-width:550px;">
    <span style="font-weight:bold;">［ここに注目！］</span>
    <div style="padding: 5px 20px 5px;font-size:0.85em;"><?=h($data['jra_thisweek_horse_1']?:"……")?></div>
</div>
<p><span style="font-weight:bold;">［出走馬情報］</span><br><?=h($data['jra_thisweek_horse_2']?:"……")?></p>
</section>
<?php endforeach; ?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>