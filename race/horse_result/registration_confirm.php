<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース結果詳細・登録内容確認";
$page->ForceNoindex();
$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }
$csrf_token=new FormCsrfToken();

$is_error=0;
$error_msgs=[];

$race_detail = new RaceResultDetail();
$is_edit_mode = 0;
if(filter_input(INPUT_POST,'edit_mode',FILTER_VALIDATE_BOOLEAN)){
    $is_edit_mode = 1;
}
$race_detail->race_results_id=filter_input(INPUT_POST,'race_id');
$race_detail->horse_id=filter_input(INPUT_POST,'horse_id');

$next_race_id=filter_input(INPUT_POST,'next_race_id');
$next_race_detail=null;

$pdo= getPDO();
do{
    if($race_detail->race_results_id==""){
        $page->addErrorMsg("レースID未指定。");
    }
    if($race_detail->horse_id==""){
        $page->addErrorMsg("競走馬ID未指定。");
    }
    if($page->error_exists){ break; }

    $race_detail->setDataByForm(INPUT_POST);
    if($race_detail->result_number==0){
        //$page->addErrorMsg("着順未指定。");
        //break;
    }
    if( // 着順と降着前着順が設定されていて、降着前のほうが着順が大きい（）
        intval($race_detail->result_before_demotion)>0 &&
        intval($race_detail->result_number)>0 &&
        $race_detail->result_number<=$race_detail->result_before_demotion
        ){
            $page->addErrorMsg("降着前着順が入力されていますが、降着で同値または着順が高くなっています\n（{$race_detail->result_before_demotion}→{$race_detail->result_number}）");
    }
    $old_horse_result= new RaceResultDetail();
    $old_horse_result->setDataById(
        $pdo,
        $race_detail->race_results_id,
        $race_detail->horse_id);

    $horse=new Horse();
    $horse->setDataById($pdo, $race_detail->horse_id);
    $race=new Race($pdo, $race_detail->race_results_id);
    if($is_edit_mode==1){
        if(!$old_horse_result->record_exists){
            $page->addErrorMsg("編集対象のレース結果が存在しません。");
            break;
        }
    }else{
        if($old_horse_result->record_exists){
            $page->addErrorMsg("結果が既に存在します");
            break;
        }
        if(!$race->record_exists){
            $page->addErrorMsg("存在しないレースID");
        }
        if(!$horse->record_exists){
            $page->addErrorMsg("存在しない競走馬ID");
        }
        if($horse->world_id!==$race->world_id){
            $page->addErrorMsg("競走馬とレース情報のワールドが一致していません");
            break;
        }
        if($horse->birth_year===null){
            $page->addErrorMsg("対象馬は生年未登録です");
        }
    }
    if(!$is_edit_mode){
        if($next_race_id!=''){
            $next_race_detail=new RaceResultDetail();
            $next_race_detail->setDataById($pdo,$next_race_id,$race_detail->horse_id);
            $next_race = new Race($pdo,$next_race_id);
        }
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
?><!DOCTYPE html>
<html>
<head>
    <title>結果登録</title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?=h($page->title)?><?=$is_edit_mode?"(編集)":""?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php
if($is_error!==0){
    echo '<div style="border:solid 1px red;">';
    echo nl2br(h(implode("\n",$error_msgs)));
    echo "</div>";
}
?>
<form action="./registration_execute.php" method="post">
<input type="hidden" name="edit_mode" value="<?=$is_edit_mode?1:0?>">
<table class="edit-form-table floatLeft" style="margin-right: 4px;">
<tr>
    <th>レースID</th>
    <td><?php HTPrint::HiddenAndText('race_id',$race_detail->race_results_id); ?></td>
</tr>
<tr>
    <th>レース名</th>
    <td><?=h($race->year." ".$race->race_name)?></td>
</tr>
<tr>
    <th>競走馬ID</th>
    <td><?php HTPrint::HiddenAndText('horse_id',$race_detail->horse_id); ?></td>
</tr>
<tr>
    <th>競走馬名</th>
     <td><?=h($horse->name_ja?:($horse->name_en?:""))?></td>
</tr>
<tr>
    <th>着順</th>
    <td><?php HTPrint::HiddenAndText('result_number',$race_detail->result_number?:''); ?>着</td>
</tr>
<tr>
    <th>表示順補正</th>
    <td><?php HTPrint::HiddenAndText('result_order',$race_detail->result_order?:''); ?></td>
</tr>
<tr>
    <th>特殊結果</th>
    <td><?php HTPrint::HiddenAndText('result_text',$race_detail->result_text); ?></td>
</tr>
<tr>
    <th>降着前入線順</th>
    <td><?php
        HTPrint::HiddenAndText('result_before_demotion',$race_detail->result_before_demotion?:'');
        print_h($race_detail->result_before_demotion?"着から降着":'');
    ?></td>
</tr>
<tr>
    <th>枠・馬番</th>
    <td>
        <?php HTPrint::HiddenAndText('frame_number',$race_detail->frame_number?:''); ?>枠
        <?php HTPrint::HiddenAndText('horse_number',$race_detail->horse_number?:''); ?>番
    </td>
</tr>
<tr>
    <th>斤量</th>
    <td><?php HTPrint::HiddenAndText('handicap',$race_detail->handicap?:''); ?>kg</td>
</tr>
<tr>
    <th>着差</th>
    <td><?php HTPrint::HiddenAndText('margin',$race_detail->margin); ?></td>
</tr>
<tr>
    <th>コーナー<br>通過順位</th>
    <td>
    <?php
        print_h($race_detail->corner_1?$race_detail->corner_1.'-':'');
        print_h($race_detail->corner_2?$race_detail->corner_2.'-':'');
        print_h($race_detail->corner_3?$race_detail->corner_3.'-':'');
        print_h($race_detail->corner_4?:'');
    ?>
    <?php HTPrint::Hidden('corner_1',$race_detail->corner_1?:''); ?>
    <?php HTPrint::Hidden('corner_2',$race_detail->corner_2?:''); ?>
    <?php HTPrint::Hidden('corner_3',$race_detail->corner_3?:''); ?>
    <?php HTPrint::Hidden('corner_4',$race_detail->corner_4?:''); ?>
    </td>
</tr>
<tr>
    <th>単勝人気</th>
    <td><?php HTPrint::HiddenAndText('favourite',ifZero2Empty($race_detail->favourite)); ?>番人気</td>
</tr>
<!--<tr>
    <th>収得賞金</th>
    <td><?php HTPrint::HiddenAndText('syuutoku',ifZero2Empty($race_detail->syuutoku)); ?>万円</td>
</tr>-->
<tr>
    <th>性別</th>
    <td><?php
switch($race_detail->sex){
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
?><?php HTPrint::Hidden('sex',$race_detail->sex); ?></td>
</tr>
<tr>
    <th>所属上書</th>
    <td><?php HTPrint::HiddenAndText('tc',$race_detail->tc); ?></td>
</tr>
<tr>
    <th>調教国上書</th>
    <td><?php HTPrint::HiddenAndText('training_country',$race_detail->training_country); ?></td>
</tr>
<tr>
    <th>地方所属</th>
    <td><?php
switch($race_detail->is_affliationed_nar){
    case 1:
        echo "[地]";
        break;
    case 2:
        echo "(地)";
        break;    
}
?><?php HTPrint::Hidden('is_affliationed_nar',$race_detail->is_affliationed_nar); ?></td>
</tr>
<tr>
    <th>未登録の前走</th>
    <td><?php HTPrint::HiddenAndText('non_registered_prev_race_number',$race_detail->non_registered_prev_race_number); ?></td>
</tr>
<?php if($next_race_detail!=null && $next_race_detail->record_exists && $next_race_detail->non_registered_prev_race_number>0): ?>
<tr>
    <th rowspan="3">次走から<br>未登録自動減算</th>
    <td><?php HTPrint::HiddenAndText('next_race_id',$next_race_detail->race_results_id); ?></td>
</tr>
<tr>
    <td><?=h($next_race->race_name)?>(<?=h($next_race->year)?>)</td>
</tr>
<tr>
    <td>現在指定：<?=h($next_race_detail->non_registered_prev_race_number)?>走</td>
</tr>
<?php endif; ?>
</table>
<table class="edit-form-table floatLeft">
<tr>
    <th colspan="2">今週の注目レース</th>
</tr>
<tr>
    <th>(火)</th>
    <td class="in_input"><textarea name="jra_thisweek_horse_1" readonly><?=h(rtrim($race_detail->jra_thisweek_horse_1))?></textarea></td>
</tr>
<tr>
    <th>(木)</th>
    <td class="in_input"><textarea name="jra_thisweek_horse_2" readonly><?=h(rtrim($race_detail->jra_thisweek_horse_2))?></textarea></td>
</tr>
<tr>
    <th>並び順</th>
    <td><?php HTPrint::HiddenAndText('jra_thisweek_horse_sort_number',$race_detail->jra_thisweek_horse_sort_number); ?></td>
</tr>
<tr><th colspan="2">スペシャル出馬表紹介</th></tr>
<tr>
    <td class="in_input" colspan="2"><textarea name="jra_sps_comment" readonly><?=h(rtrim($race_detail->jra_sps_comment))?></textarea></td>
</tr>
</table>
<div style="clear: both;">
<input type="submit" value="登録実行" <?=h($is_error==0?"":'disabled')?>>
</div>
<?php $csrf_token->printHiddenInputTag(); ?>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>
