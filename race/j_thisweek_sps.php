<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
InAppUrl::init(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="スペシャル出馬表(紹介文)";
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
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php require_once APP_ROOT_DIR."/race/race_content_header.inc.php"; ?>
<hr>
<?php

# レース着順取得
$horse_tbl=Horse::TABLE;
$race_tbl=Race::TABLE;
$race_results_tbl=RaceResults::TABLE;
$sql=<<<END
SELECT
`r_retults`.*
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
,`master_horse`.`color`
,`race`.*
FROM `{$race_tbl}` AS `race`
LEFT JOIN `{$race_results_tbl}` AS `r_retults` ON `race`.`race_id`=`r_retults`.`race_id`
LEFT JOIN {$horse_tbl} AS `master_horse` ON `r_retults`.`horse_id`=`master_horse`.`horse_id`
WHERE `race`.`race_id`=:race_id
ORDER BY
`r_retults`.`frame_number` IS NULL,
`r_retults`.`frame_number` ASC,
`r_retults`.`horse_number` ASC,
`master_horse`.`name_ja` ASC, `master_horse`.`name_en` ASC;
END;

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':race_id', $race_id, PDO::PARAM_STR);
$flag = $stmt->execute();
$table_data=[];
while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data['sex_str']=sexTo1Char($data['sex']);
    $data['age']=empty($data['birth_year'])?'':($race->year-$data['birth_year']);
    $table_data[]=$data;
}

?>
<?php
foreach ($table_data as $data) {
    // 1件目からない場合
    if(empty($data['horse_id'])){
        continue;
    }
?><section>
<p>
<?php if($page->is_editable): ?>
<a href="<?=$page->to_app_root_path?>race/horse_result/form.php?race_id=<?=h($race_id)?>&horse_id=<?=h($data['horse_id'])?>&edit_mode=1">■</a>
<?php else: ?>■
<?php endif; ?>
<?php if(!empty($data['frame_number'])) { ?>
<span style="border:solid 1px #333; padding-left:0.3em; padding-right:0.3em; margin-right:0.3em;" class="<?=h("waku_".$data['frame_number'])?>"> <?=h($data['frame_number']."枠")?></span><?=h( empty($data['horse_number'])?"":(str_pad($data['horse_number'],2,"0",STR_PAD_LEFT)."番 "))?>
<?php } ?>
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
    echo '<a href="'.$page->to_app_root_path.'horse/?horse_id='.h($data['horse_id']).'" style="text-decoration:none;">';
    print_h($data['name_ja']?:$data['name_en']);
    echo "</a><br>";
    echo "調教師：□□□□";
    if(!empty($data['tc'])){
        print_h("（{$data['tc']}）");
    }else{
        print_h("（{$data['horse_tc']}）");
    }
?><br>
父：<?=h($data['sire_name']?:"□□□□□□")?><br>
母：<?=h($data['mare_name']?:"□□□□□□")?><br>
母の父：<?=h($data['bms_name']?:"□□□□□□")?><br>
<?=h($data['sex_str'].$data['age']."歳")?>
<?=h($data['color']?("/".$data['color']):'')?>
<?=h($data['handicap']?(" ".$data['handicap']."kg"):'')?>
</p>
<p>［紹介］<br><?=nl2br(h($data['jra_sps_comment']?:"……"))?></p>
</section><hr><?php
}
?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>