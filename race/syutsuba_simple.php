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
if(ENABLE_ACCESS_COUNTER){
    ArticleCounter::countup($pdo,ArticleCounter::TYPE_RACE_SYUTSUBA_SIMPLE,$race_id);
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
.race_results td.col_favourite{ text-align:center; }

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
$resultsGetter=new RaceResultsGetter($pdo,$race_id,$race->year);
$resultsGetter->pageIsEditable=$page->is_editable;
$resultsGetter->addOrderParts([
    "`r_results`.`frame_number` IS NULL",
    "`r_results`.`frame_number` ASC",
    "`r_results`.`horse_number` IS NULL",
    "`r_results`.`horse_number` ASC",
    "`".Horse::TABLE."`.`name_en` ASC",
]);
$table_data=$resultsGetter->getTableData();
$mode_umm=false;
switch($setting->age_view_mode){
    case Setting::AGE_VIEW_MODE_UMAMUSUME:
    case Setting::AGE_VIEW_MODE_UMAMUSUME_S:
        $mode_umm=true;
}
$empty_row_2="<td>&nbsp;</td><td></td><td class=\"horse_name\"></td><td></td><td></td><td></td><td></td>";
?><table class="race_results">
<tr>
<th>枠</th><th>馬番</th>
<th style="min-width:12em;">馬名</th>
<th><?=h($mode_umm?"級":"性齢")?></th>
<th>負担<br>重量</th>
<?php if(!$mode_umm): ?><th>騎手</th><?php endif; ?>
<th>所属</th>
<?php if(!$mode_umm): ?><th>調教師</th><?php endif; ?>
<?php if(!$mode_umm): ?><th>馬体重</th><?php endif; ?>
<th>人気</th>
<?php if($page->is_editable): ?><th>編</th><?php endif; ?>
</tr><?php
$i=0;
$latest_horse_exists=false;
foreach ($table_data as $data) {
    $i++;
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
<?php
    $is_affliationed_nar=0;
    if($data['is_affliationed_nar']===null){
        $is_affliationed_nar=$data['horse_is_affliationed_nar'];
    }else{
        $is_affliationed_nar=$data['is_affliationed_nar'];
    }
    $marks=new Imploader('');
    if(($race->is_jra==1 || $race->is_nar==1)){
        // 中央競馬または地方競馬の場合、調教国・生産国でカク外・マル外マークをつける
        if($data['training_country']!='' && $data['training_country']!='JPN'){
            // 外国調教馬にカク外表記
            $marks->add("[外]");
        }else{
            // 中央競馬の場合のみ地方所属馬と元地方所属馬のカク地・マル地マーク
            if($race->is_jra){
                if($is_affliationed_nar==1){
                    $marks->add("[地]");
                }else if($is_affliationed_nar==2){
                    $marks->add("(地)");
                }
            }
            // 外国産馬のマル外表記
            if($data['breeding_country']!='' && $data['breeding_country']!='JPN'){
                $marks->add("(外)");
            }
        }
    }
    $a_tag=new MkTagA($data['name_ja']?:$data['name_en']);
    $a_tag->href($page->to_app_root_path.'horse/?horse_id='.$data['horse_id']);
    $country=($race->is_jra==0 && $race->is_nar==0)?" <span>(".h($data['training_country']).")</span> ":'';
?>
<td class="horse_name"><?=implode(' ',[$marks,$a_tag,$country])?></td>
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
<?php if(!$mode_umm): ?>
    <td style="<?=$data['jockey_row']->is_anonymous?'color:#999;':''?>"><?=h($data['jockey_name']??'')?></td>
<?php endif; ?>
<td><?=h($data['tc'])?></td>
<?php if(!$mode_umm): ?>
    <td style="<?=$data['trainer_row']->is_anonymous?'color:#999;':''?>">
        <?=h($data['trainer_name']??'')?>
    </td>
<?php endif; ?>
<?php if(!$mode_umm): ?><td><?php /* 馬体重 */ ?></td><?php endif; ?>
<td class="col_favourite favourite_<?=h($data['favourite'])?>"><?=h($data['favourite'])?></td>
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
<?php $url=APP_ROOT_REL_PATH."race/manage/edit/?race_id={$race->race_id}&edit_mode=1"; ?>
        <td><a href="<?=$url?>">このレースの情報を編集</a></td>
<?php $url=APP_ROOT_REL_PATH."race/horse_result/form.php?race_id={$race->race_id}"; ?>
        <td><a href="<?=h($url)?>">このレースに戦績を追加</a></td>
        <td><a href="<?=APP_ROOT_REL_PATH?>race/manage/update_race_result_id/?race_id=<?=h($race->race_id)?>">レースID修正</a></td>
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
<a href="<?=APP_ROOT_REL_PATH?>race/manage/edit/?race_id=<?=h($race->race_id);?>&edit_mode=0">コピーして新規登録</a>
        </td>
<?php
    $a_tag=new MkTagA('同日同場で新規登録');
    if($race->date!=''){
        $a_tag->setLinkText('同日同場で新規登録');
        $urlparam=new UrlParams([
            'date'=>$race->date,
            'race_course_name'=>$race->race_course_name]);
        $a_tag->href(APP_ROOT_REL_PATH."race/manage/edit/?".$urlparam);
    }else{
        $a_tag->setLinkText('同週同場で新規登録');
        $urlparam=new UrlParams([
            'year'=>$race->year,
            'week_id'=>$race->week_id,
            'race_course_name'=>$race->race_course_name]);
        $a_tag->href(APP_ROOT_REL_PATH."race/manage/edit/?".$urlparam);
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