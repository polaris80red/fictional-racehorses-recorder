<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
defineAppRootRelPath(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="出馬表";
$session=new Session();
// 暫定でログイン＝編集可能
$page->is_editable=Session::is_logined();
// ログイン中でも強制的にプレビュー表示にできるパラメータ
$is_preview=filter_input(INPUT_GET,'preview',FILTER_VALIDATE_BOOL);
if($is_preview){
    $page->is_editable=false;
}

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
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
$race_id=filter_input(INPUT_GET,'race_id');
# レース情報取得
$race = new Race($pdo, $race_id);
if(!$race->record_exists){
    $page->error_msgs[]="レース情報取得失敗";
    $page->error_msgs[]="入力ID：{$race_id}";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
$session->latest_race=[
    'id'=>$race_id,
    'year'=>$race->year,
    'name'=>$race->race_short_name?:$race->race_name
];
$session->login_return_url='race/syutsuba_simple.php?race_id='.$race_id;
$race_access_history=(new RaceAccessHistory())->set($race_id)->saveToSession();

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
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink("js/functions.js"); ?>
<style>
.race_results td:nth-child(1){ text-align:center; }
.race_results td:nth-child(2){ text-align:center; }
.race_results td:nth-child(4){ text-align:center; }
.race_results td:nth-child(<?php
    print($setting->age_view_mode!==Setting::AGE_VIEW_MODE_UMAMUSUME?9:7);
?>){ text-align:center; }

.edit_menu table { margin-top: 8px;}
.edit_menu table a:link {text-decoration: none;}
.edit_menu table {font-size: 0.9em;}
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php echo $page->title; ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php require_once APP_ROOT_DIR."/race/race_content_header.inc.php"; ?>
<hr>
<?php
# レース着順取得
$sql=(function(){
    $horse_tbl=Horse::TABLE;
    $race_tbl=Race::TABLE;
    $r_results_tbl=RaceResults::TABLE;
    $race_special_results_tbl=RaceSpecialResults::TABLE;

    $horse_s_columns=new SqlMakeSelectColumns(Horse::TABLE);
    $horse_s_columns->addColumnsByArray([
        'name_ja','name_en','sex','birth_year'
    ]);
    $horse_s_columns->addColumnAs('tc','horse_tc');
    $horse_s_columns->addColumnAs('training_country','horse_training_country');
    $horse_s_columns->addColumnAs('is_affliationed_nar','horse_is_affliationed_nar');
    $sql_part_select_columns=implode(",\n",[
        "`r_results`.*",
        $horse_s_columns->get(true),
        "`race`.*",
        "`spr`.`is_registration_only`",
    ]);
     
    $sql=<<<END
    SELECT
    {$sql_part_select_columns}
    FROM `{$race_tbl}` AS `race`
    LEFT JOIN `{$r_results_tbl}` AS `r_results`
        ON `race`.`race_id`=`r_results`.`race_id`
    LEFT JOIN `{$horse_tbl}`
        ON `r_results`.`horse_id`=`{$horse_tbl}`.`horse_id`
    LEFT JOIN `{$race_special_results_tbl}` as spr
        ON `r_results`.result_text LIKE spr.unique_name AND spr.is_enabled=1
    WHERE `race`.`race_id`=:race_id
    ORDER BY
        `r_results`.`frame_number` IS NULL,
        `r_results`.`frame_number` ASC,
        `r_results`.`horse_number` IS NULL,
        `r_results`.`horse_number` ASC,
        `{$horse_tbl}`.`name_en` ASC;
    END;
    return $sql;
})();

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':race_id', $race_id, PDO::PARAM_STR);
$flag = $stmt->execute();
$table_data=[];
while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if(empty($data['horse_id'])){ continue; }
    $data['sex_str']=sex2String((int)$data['sex']);
    $data['age']=empty($data['birth_year'])?'':($race->year-$data['birth_year']);
    $table_data[]=$data;
}

$empty_row_2="<td>&nbsp;</td><td></td><td class=\"horse_name\"></td><td></td><td></td><td></td><td></td>";
?><table class="race_results">
<tr>
<th>枠</th><th>馬番</th>
<th style="min-width:12em;">馬名</th>
<th><?=h($setting->age_view_mode===Setting::AGE_VIEW_MODE_UMAMUSUME?"級":"性齢")?></th>
<th>負担<br>重量</th>
<?php if($setting->age_view_mode!==Setting::AGE_VIEW_MODE_UMAMUSUME): ?><th>騎手</th><?php endif; ?>
<th>所属</th>
<?php if($setting->age_view_mode!==Setting::AGE_VIEW_MODE_UMAMUSUME): ?><th>馬体重</th><?php endif; ?>
<th>人気</th>
<?php if($page->is_editable): ?><th>編</th><?php endif; ?>
</tr><?php
$i=0;
$latest_horse_exists=false;
foreach ($table_data as $data) {
    $i++;
    // 1件目からない場合
    if(empty($data['horse_id'])){
        echo "<tr><td></td>".$empty_row_2."</tr>\n";
        continue;
    }
    if($data['horse_id']==($session->latest_horse['id']??'')){
        $latest_horse_exists=true;
    }
    // 特別登録のみのデータはスキップ
    if($data['is_registration_only']){
        continue;
    }
?><tr class="">
<td class="waku_<?php echo $data['frame_number']; ?>"><?php echo $data['frame_number']; ?></td>
<td><?=h($data['horse_number'])?></td>
<td class="horse_name">
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
    $is_affliationed_nar=0;
    if($data['is_affliationed_nar']===null){
        $is_affliationed_nar=$data['horse_is_affliationed_nar'];
    }else{
        $is_affliationed_nar=$data['is_affliationed_nar'];
    }
    if($data['is_jra']==1&& $is_affliationed_nar==1){
        echo "[地] ";
    }
    echo '<a href="'.$page->to_app_root_path.'horse/?horse_id='.h($data['horse_id']).'">';
    print_h($data['name_ja']?:$data['name_en']);
    if($data['is_jra']==0 && $data['is_nar']==0){
        echo " <span>(".h($training_country).")</span> ";
    }
    echo "</a>";
?></td>
<?php
    $age_sex_str='';
    if($setting->age_view_mode===Setting::AGE_VIEW_MODE_DEFAULT){
        // 通常表記の場合
        $age_sex_str.=$data['sex_str'];
    }
    $age_sex_str.=$setting->getAgeSexSpecialFormat($data['age'],$data['sex']);
?>
<td class="sex_<?=h($data['sex'])?>"><?=h($age_sex_str)?></td>
<td><?=h($data['handicap'])?></td>
<?php if($setting->age_view_mode!==1): ?><td><?php /* 騎手 */ ?></td><?php endif; ?>
<td><?=h(!empty($data['tc'])?$data['tc']:$data['horse_tc'])?></td>
<?php if($setting->age_view_mode!==1): ?><td><?php /* 馬体重 */ ?></td><?php endif; ?>
<td class="favourite_<?=h($data['favourite'])?>"><?=h($data['favourite'])?></td>
<?php
    if(!empty($data['horse_id'])){
        $url=$page->to_app_root_path."race/horse_result/form.php?race_id={$race->race_id}&horse_id={$data['horse_id']}&edit_mode=1";
    }
?>
<?php if($page->is_editable): ?>
<td><a href="<?=h($url)?>" title="編集">編</a></td>
<?php endif; ?>
</tr>
<?php } ?></table>
<hr>
<a href="<?=h($page->getRaceNameSearchUrl($race->race_name))?>" style="">他年度の<?=h($race->race_name)?>を検索</a>
<?php if($page->is_editable): ?>
<hr><input type="button" id="edit_tgl" value="編集" style="<?=!EDIT_MENU_TOGGLE?'display:none;':''?>">
<input type="hidden" id="hiddden_race_id" value="<?=h($race->race_id)?>">
<input type="button" value="レースIDをクリップボードにコピー" onclick="copyToClipboard('#hiddden_race_id');">
(race_id=<?=h($race->race_id)?>)<a id="edit_menu"></a>
<div class="edit_menu" style="<?=EDIT_MENU_TOGGLE?'display:none;':''?>"> 
<input type="hidden" id="edit_menu_states" value="0">
<table>
    <tr>
<?php $url=APP_ROOT_REL_PATH."race/result/form.php?race_id={$race->race_id}&edit_mode=1"; ?>
        <td><a href="<?=$url?>">このレースの情報を編集</a></td>
<?php $url=APP_ROOT_REL_PATH."race/horse_result/form.php?race_id={$race->race_id}"; ?>
        <td><a href="<?=h($url)?>">このレースに戦績を追加</a></td>
        <td><a href="<?=APP_ROOT_REL_PATH?>race/update_race_result_id/form.php?race_id=<?=h($race->race_id)?>&edit_mode=1">レースID修正</a></td>
    </tr>
    <tr>
<?php
$a_tag=new MkTagA('最後に開いた馬をこのレースに追加');
$latest_horse=new Horse();
if(!empty($session->latest_horse['id'])){
    $latest_horse->setDataById($pdo,$session->latest_horse['id']);
}
if($latest_horse->record_exists){
    if($latest_horse_exists){
        $a_tag->title("最後に開いた競走馬は既に登録されています")->setStyle('text-decoration','line-through');
    }else if($latest_horse->birth_year==null){
        $a_tag->title("生年仮登録馬のため戦績追加不可")->setStyle('text-decoration','line-through');
    }else{
        $url=APP_ROOT_REL_PATH."race/horse_result/form.php?horse_id={$session->latest_horse['id']}&race_id={$race->race_id}";
        $a_tag->href($url);
    }
}
?>
        <td colspan="2"><?=$a_tag?></td>
        <td>
<?php if(!empty($session->latest_horse['id'])): ?>
<?php $url=APP_ROOT_REL_PATH."horse/?horse_id={$session->latest_horse['id']}"; ?>
<a href="<?=h($url)?>"><?=h($session->latest_horse['name']?:$session->latest_horse['id'])?></a>
<?php endif; ?>
        </td>
    </tr>
    <tr>
        <td>
<a href="<?=APP_ROOT_REL_PATH?>race/result/form.php?race_id=<?=h($race->race_id);?>&edit_mode=0">コピーして新規登録</a>
        </td>
<?php
    $a_tag=new MkTagA('同日同場で新規登録');
    if($race->date!=''){
        $a_tag->setLinkText('同日同場で新規登録');
        $urlparam=new UrlParams([
            'date'=>$race->date,
            'race_course_name'=>$race->race_course_name]);
        $a_tag->href(APP_ROOT_REL_PATH."race/result/form.php?".$urlparam);
    }else{
        $a_tag->setLinkText('同週同場で新規登録');
        $urlparam=new UrlParams([
            'year'=>$race->year,
            'week_id'=>$race->week_id,
            'race_course_name'=>$race->race_course_name]);
        $a_tag->href(APP_ROOT_REL_PATH."race/result/form.php?".$urlparam);
    }
    ?>
        <td><?=$a_tag?></td>
        <td></td>
    </tr>
</table>
</div>
<script>
$(function() {
    $('#edit_tgl').click(function(){
    if($('#edit_menu_states').val()=='0') {
        $('.edit_menu').css('display','block');
        $('#edit_menu_states').val('1');
    } else {
        $('.edit_menu').css('display','none');
        $('#edit_menu_states').val('0');
    }
    });
});
</script>
<?php endif; ?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>