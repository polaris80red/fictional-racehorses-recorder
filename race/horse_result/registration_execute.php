<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース結果詳細・登録実行";
$page->ForceNoindex();
$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$is_error=0;
$error_msgs=[];

$input = new RaceResultDetail();
$is_edit_mode = 0;
if(filter_input(INPUT_POST,'edit_mode',FILTER_VALIDATE_BOOLEAN)){
    $is_edit_mode = 1;
}
$input->race_results_id=filter_input(INPUT_POST,'race_id');
$input->horse_id=filter_input(INPUT_POST,'horse_id');

$next_race_id=filter_input(INPUT_POST,'next_race_id');
$next_race=null;

$input->setDataByForm(INPUT_POST);
do{
    if(!(new FormCsrfToken())->isValid()){
        ELog::error($page->title.": CSRFトークンエラー");
        $is_error=1;
        $error_msgs[]="登録編集フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）";
        break;
    }
    if($input->race_results_id==""){
        $is_error=1;
        $error_msgs[]="レースIDなし。";
        break;
    }
    if($input->horse_id==""){
        $is_error=1;
        $error_msgs[]="競走馬IDなし。";
        break;
    }
    if($input->result_number==0){
        //$is_error=1;
        //$error_msgs[]="着順未指定。";
        //break;
    }
    
    $pdo= getPDO();
    $old_horse_result= new RaceResultDetail();
    $old_horse_result->setDataById(
        $pdo,
        $input->race_results_id,
        $input->horse_id);
    $horse=new Horse();
    $horse->setDataById($pdo, $input->horse_id);
    $race=new Race($pdo, $input->race_results_id);
    if(!$race->record_exists){
        $is_error=1;
        $error_msgs[]="存在しないレースID";
        break;
    }
    if(!$horse->record_exists){
        $is_error=1;
        $error_msgs[]="存在しない競走馬ID";
        break;
    }
    if($horse->world_id!==$race->world_id){
        $is_error=1;
        $error_msgs[]="競走馬とレース情報のワールドが一致していません";
        break;
    }
    if($is_edit_mode==1){
        if(!$old_horse_result->record_exists){
            $is_error=1;
            $error_msgs[]="対象のレース結果が存在しません。";
            break;
        }
        $input->UpdateExec($pdo);
    }else{
        if($old_horse_result->record_exists){
            $is_error=1;
            $error_msgs[]="結果が既に存在します";
            break;
        }
        $pdo->beginTransaction();
        try{
            $result = $input->InsertExec($pdo);
            // 空き区間への追加なら未登録数を減算
            if($next_race_id!=''){
                $next_race=new RaceResultDetail();
                $next_race->setDataById($pdo,$next_race_id,$input->horse_id);
                if($next_race->record_exists){
                    $next_race->SubtractionNonRegisteredPrevRaceNumber($pdo);
                }
            }
            $pdo->commit();
        }catch(Exception $e){
            $pdo->rollBack();
            $page->debug_dump_var[]=$e;
            $page->printCommonErrorPage();
        }
    }
    // 最後に開いた馬を結果登録した馬に更新
    $session->latest_horse=[
        'id'=>$input->horse_id,
        'name'=>$horse->name_ja?:$horse->name_en,
    ];
}while(false);

?><!DOCTYPE html>
<html lang="ja">
<head>
    <title>結果登録</title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
<style>
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php echo $page->title; ?><?php echo ($is_edit_mode?"(編集)":"") ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php
if($is_error!==0){
    echo '<div style="border:solid 1px red;padding:8px;margin-bottom:8px;">';
    echo "<h2>処理中止</h2>";
    echo nl2br(h(implode("\n",$error_msgs)));
    echo "</div>";
}
?>
<?php if($is_error==0): ?>
<h2>登録完了</h2>
<hr>
<div><?php
$url_suffix='';
$sp_result=RaceSpecialResults::getByUniqueName($pdo,$input->result_text);
if($sp_result && $sp_result->is_registration_only){
    $url_suffix = '&show_registration_only=true';
}
?>
<a href="<?=h($page->getRaceResultUrl($input->race_results_id).$url_suffix)?>" style="font-weight: bold;">レース結果</a>
｜<a href="<?=APP_ROOT_REL_PATH?>race/j_thisweek.php?race_id=<?=h($input->race_results_id.$url_suffix)?>">出走馬情報</a>
｜<a href="<?=APP_ROOT_REL_PATH?>race/j_thisweek_sps.php?race_id=<?=h($input->race_results_id)?>">SP出馬表紹介文</a><br>
<a href="<?=APP_ROOT_REL_PATH?>horse/?horse_id=<?=h($input->horse_id.$url_suffix)?>" style="font-weight: bold;">競走馬情報</a>
</div>
<hr>
<div>
<?php
$race_result=new Race($pdo,$input->race_results_id);
?>
<?php if($race_result->date!=''): ?>
<?=(new MkTagA('同日のレース一覧',APP_ROOT_REL_PATH.'race/list/in_date.php?date='.urlencode($race_result->date)))?><br>
<?php endif; ?>
<?php
$url_param=new UrlParams(['year'=>$race_result->year]);
$url=APP_ROOT_REL_PATH.'race/list/in_week.php?';
$week=new RaceYearWeek($race_result->year,$race_result->week_id);

echo (new MkTagA('同週のレース一覧',$url.$url_param->toString(['year'=>$week->year,'week'=>$week->week])));
$getNextWeekTag=function($link_text,&$week)use($url_param){
    $url=APP_ROOT_REL_PATH.'race/list/in_week.php?';
    $week->nextWeek();
    return (new MkTagA($link_text,$url.$url_param->toString(['year'=>$week->year,'week'=>$week->week])))->__toString();
};
echo "｜".$getNextWeekTag('連闘',$week);
for ($i=1; $i <=5; $i++) {
    echo "｜".$getNextWeekTag("中{$i}週",$week);
}
?><br>
<?php
$week_row=RaceWeek::getById($pdo,$race_result->week_id);
$ym_dt=new DateTime($race_result->year."-".str_pad(($week_row->month),2,'0',STR_PAD_LEFT)."-01");
?>
<?=(new MkTagA('同月のレース一覧',$url.$url_param->toString(['month'=>$race_result->month])))?>
｜<?=(new MkTagA('翌月',$url.$url_param->toString(['year'=>$ym_dt->modify('next month')->format('Y'),'month'=>$ym_dt->format('n')])))?>
｜<?=(new MkTagA('翌々月',$url.$url_param->toString(['year'=>$ym_dt->modify('next month')->format('Y'),'month'=>$ym_dt->format('n')])))?>
</div>
<hr>
<form action="" method="post">
<input type="hidden" name="is_edit_mode" value="<?=$is_edit_mode?1:0?>">
<table class="edit-form-table floatLeft" style="margin-right: 4px;">
<tr>
    <th>レースID</th><td><?=h($input->race_results_id)?></td>
</tr>
<tr>
    <th>競走馬ID</th><td><?=h($input->horse_id)?></td>
</tr>
<tr>
    <th>着順</th><td><?=h($input->result_number)?>着</td>
</tr>
<tr>
    <th>表示順補正</th><td><?=h($input->result_order)?></td>
</tr>
<tr>
    <th>特殊結果</th><td><?=h($input->result_text)?></td>
</tr>
<tr>
    <th>降着前入線順</th><td><?=h($input->result_before_demotion?:'')?></td>
</tr>
<tr>
    <th>枠番</th><td><?=h(ifZero2Empty($input->frame_number))?>枠</td>
</tr>
<tr>
    <th>馬番</th><td><?=h(ifZero2Empty($input->horse_number))?>番</td>
</tr>
<tr>
    <th>斤量</th><td><?=h($input->handicap)?>kg</td>
</tr>
<tr>
    <th>着差</th><td><?=h($input->margin)?></td>
</tr>
<tr>
    <th>コーナー<br>通過順位</th>
    <td><?php
        print_h($input->corner_1?$input->corner_1.'-':'');
        print_h($input->corner_2?$input->corner_2.'-':'');
        print_h($input->corner_3?$input->corner_3.'-':'');
        print_h($input->corner_4);
    ?></td>
</tr>
<tr>
    <th>単勝人気</th><td><?=h(ifZero2Empty($input->favourite))?></td>
</tr>
<tr>
    <th>性別</th>
    <td><?php
switch($input->sex){
    case 0:
        echo "元の値";
        break;
    case 1:
        echo "牡";
        break;    
    case 3:
        echo "せん";
        break;    
}
?></td>
</tr>
<tr>
    <th>所属上書</th><td><?=h($input->tc)?></td>
</tr>
<tr>
    <th>調教国上書</th><td><?=h($input->training_country)?></td>
</tr>
<tr>
    <th>地方所属</th>
    <td><?php
switch($input->is_affliationed_nar){
    case 1:
        echo "[地]";
        break;
    case 2:
        echo "(地)";
        break;    
}
?></td>
</tr>
</table>
<table class="edit-form-table floatLeft">
<tr>
    <th colspan="2">今週の注目レース</th>
</tr>
<tr>
    <th>(火)</th>
    <td><textarea readonly><?=h($input->jra_thisweek_horse_1)?></textarea></td>
</tr>
<tr>
    <th>(木)</th>
    <td><textarea readonly><?=h($input->jra_thisweek_horse_2)?></textarea></td>
</tr>
<tr>
    <th>並び順</th>
    <td><?=h($input->jra_thisweek_horse_sort_number)?></td>
</tr>
<tr><th colspan="2">スペシャル出馬表紹介</th></tr>
<tr>
    <td colspan="2"><textarea readonly><?=h($input->jra_sps_comment)?></textarea></td>
</tr>
</table>
</form>
<div style="clear: both;"></div>
<?php endif; ?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>
