<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
defineAppRootRelPath(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース結果詳細・登録内容確認";
$page->ForceNoindex();
$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }
$csrf_token=new FormCsrfToken();

$is_error=0;
$error_msgs=[];

$race_results = new RaceResults();
$is_edit_mode = 0;
if(filter_input(INPUT_POST,'edit_mode',FILTER_VALIDATE_BOOLEAN)){
    $is_edit_mode = 1;
}
$race_results->race_id=filter_input(INPUT_POST,'race_id');
$race_results->horse_id=filter_input(INPUT_POST,'horse_id');

$next_race_id=filter_input(INPUT_POST,'next_race_id');
$next_race_results=null;

$pdo= getPDO();
do{
    if($race_results->race_id==""){
        $page->addErrorMsg("レースID未指定。");
    }
    if($race_results->horse_id==""){
        $page->addErrorMsg("競走馬ID未指定。");
    }
    if($page->error_exists){ break; }

    $race_results->setDataByForm(INPUT_POST);
    if($race_results->result_number==0){
        //$page->addErrorMsg("着順未指定。");
        //break;
    }
    if( // 着順と降着前着順が設定されていて、降着前のほうが着順が大きい（）
        intval($race_results->result_before_demotion)>0 &&
        intval($race_results->result_number)>0 &&
        $race_results->result_number<=$race_results->result_before_demotion
        ){
            $page->addErrorMsg("降着前着順が入力されていますが、降着で同値または着順が高くなっています\n（{$race_results->result_before_demotion}→{$race_results->result_number}）");
    }
    $old_horse_result= new RaceResults();
    $old_horse_result->setDataById(
        $pdo,
        $race_results->race_id,
        $race_results->horse_id);

    $horse=new Horse();
    $horse->setDataById($pdo, $race_results->horse_id);
    $race=new Race($pdo, $race_results->race_id);
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
            $next_race_results=new RaceResults();
            $next_race_results->setDataById($pdo,$next_race_id,$race_results->horse_id);
            $next_race = new Race($pdo,$next_race_id);
        }
    }
}while(false);
$race_results->varidate();
if($race_results->error_exists){
    $page->addErrorMsgArray($race_results->error_msgs);
    $page->printCommonErrorPage();
    exit;
}
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
<form action="./execute.php" method="post">
<input type="hidden" name="edit_mode" value="<?=$is_edit_mode?1:0?>">
<table class="edit-form-table floatLeft" style="margin-right: 4px;">
<tr>
    <th>レースID</th>
    <td><?php HTPrint::HiddenAndText('race_id',$race_results->race_id); ?></td>
</tr>
<tr>
    <th>レース名</th>
    <td><?=h($race->year." ".$race->race_name)?></td>
</tr>
<tr>
    <th>競走馬ID</th>
    <td><?php HTPrint::HiddenAndText('horse_id',$race_results->horse_id); ?></td>
</tr>
<tr>
    <th>競走馬名</th>
     <td><?=h($horse->name_ja?:($horse->name_en?:""))?></td>
</tr>
<tr>
    <th>着順</th>
    <td><?php HTPrint::HiddenAndText('result_number',$race_results->result_number?:''); ?>着</td>
</tr>
<tr>
    <th>表示順補正</th>
    <td><?php HTPrint::HiddenAndText('result_order',$race_results->result_order?:''); ?></td>
</tr>
<tr>
    <th>特殊結果</th>
    <td><?php HTPrint::HiddenAndText('result_text',$race_results->result_text); ?></td>
</tr>
<tr>
    <th>降着前入線順</th>
    <td><?php
        HTPrint::HiddenAndText('result_before_demotion',$race_results->result_before_demotion?:'');
        print_h($race_results->result_before_demotion?"着から降着":'');
    ?></td>
</tr>
<tr>
    <th>枠・馬番</th>
    <td>
        <?php HTPrint::HiddenAndText('frame_number',$race_results->frame_number?:''); ?>枠
        <?php HTPrint::HiddenAndText('horse_number',$race_results->horse_number?:''); ?>番
    </td>
</tr>
<tr>
    <th>騎手</th>
    <td><?php HTPrint::HiddenAndText('jockey',$race_results->jockey_name?:''); ?></td>
</tr>
<?php if($race_results->jockey_name): ?>
<?php $mst_jockey=Jockey::getByUniqueName($pdo,$race_results->jockey_name);?>
<tr>
    <th>騎手マスタ</th>
    <td><?=$mst_jockey!==false?($mst_jockey->short_name_10):'登録外'?></td>
</tr>
<?php endif; ?>
<tr>
    <th>斤量</th>
    <td>
        <?php HTPrint::HiddenAndText('handicap',$race_results->handicap?:'');?>kg
        ｜馬体重 <?php HTPrint::HiddenAndText('h_weight',$race_results->h_weight?:'');?>kg
    </td>
</tr>
<tr>
    <th>タイム</th>
    <td>
        <?php HTPrint::HiddenAndText('time',$race_results->time); ?>
        （上り：<?php HTPrint::HiddenAndText('f_time',$race_results->f_time); ?>）
    </td>
</tr>
<tr>
    <th>着差</th>
    <td><?php HTPrint::HiddenAndText('margin',$race_results->margin); ?></td>
</tr>
<tr>
    <th>コーナー<br>通過順位</th>
    <td>
    <?php
        print_h($race_results->corner_1?$race_results->corner_1.'-':'');
        print_h($race_results->corner_2?$race_results->corner_2.'-':'');
        print_h($race_results->corner_3?$race_results->corner_3.'-':'');
        print_h($race_results->corner_4?:'');
    ?>
    <?php HTPrint::Hidden('corner_1',$race_results->corner_1?:''); ?>
    <?php HTPrint::Hidden('corner_2',$race_results->corner_2?:''); ?>
    <?php HTPrint::Hidden('corner_3',$race_results->corner_3?:''); ?>
    <?php HTPrint::Hidden('corner_4',$race_results->corner_4?:''); ?>
    </td>
</tr>
<tr>
    <th>単勝人気</th>
    <td><?php HTPrint::HiddenAndText('favourite',ifZero2Empty($race_results->favourite)); ?>番人気</td>
</tr>
<tr>
    <th>単勝オッズ</th>
    <td><?php HTPrint::HiddenAndText('odds',$race_results->odds); ?></td>
</tr>
<tr>
    <th>本賞金</th>
    <td><?php HTPrint::HiddenAndText('earnings',$race_results->earnings?:''); ?>万円</td>
</tr>
<tr>
    <th>収得賞金</th>
    <td><?php HTPrint::HiddenAndText('syuutoku',$race_results->syuutoku?:''); ?>万円</td>
</tr>
</table>
<table class="edit-form-table floatLeft">
<tr>
    <th>性別</th>
    <td><?php
switch($race_results->sex){
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
?><?php HTPrint::Hidden('sex',$race_results->sex); ?></td>
</tr>
<tr>
    <th>所属上書</th>
    <td><?php HTPrint::HiddenAndText('tc',$race_results->tc); ?></td>
</tr>
<tr>
    <th>調教師上書</th>
    <td><?php HTPrint::HiddenAndText('trainer_name',$race_results->trainer_name); ?></td>
</tr>
<tr>
    <th>調教国上書</th>
    <td><?php HTPrint::HiddenAndText('training_country',$race_results->training_country); ?></td>
</tr>
<tr>
    <th>地方所属</th>
    <td><?php
switch($race_results->is_affliationed_nar){
    case 1:
        echo "[地]";
        break;
    case 2:
        echo "(地)";
        break;    
}
?><?php HTPrint::Hidden('is_affliationed_nar',$race_results->is_affliationed_nar); ?></td>
</tr>
<tr>
    <th>馬主上書</th>
    <td><?php HTPrint::HiddenAndText('owner_name',$race_results->owner_name); ?></td>
</tr>
<tr>
    <th>未登録の前走</th>
    <td><?php HTPrint::HiddenAndText('non_registered_prev_race_number',$race_results->non_registered_prev_race_number); ?></td>
</tr>
<?php if($next_race_results!=null && $next_race_results->record_exists && $next_race_results->non_registered_prev_race_number>0): ?>
<tr>
    <th rowspan="3">次走から<br>未登録自動減算</th>
    <td><?php HTPrint::HiddenAndText('next_race_id',$next_race_results->race_id); ?></td>
</tr>
<tr>
    <td><?=h($next_race->race_name)?>(<?=h($next_race->year)?>)</td>
</tr>
<tr>
    <td>現在指定：<?=h($next_race_results->non_registered_prev_race_number)?>走</td>
</tr>
<?php endif; ?>
<tr><td colspan="2"></td></tr>
<tr>
    <th>前メモ</th>
    <td style="max-width: 250px;">
        <?=nl2br(h($race_results->race_previous_note))?>&nbsp;
        <?php HTPrint::Hidden('race_previous_note',$race_results->race_previous_note); ?>
    </td>
</tr>
<tr>
    <th>後メモ</th>
    <td style="max-width: 250px;">
        <?=nl2br(h($race_results->race_after_note))?>&nbsp;
        <?php HTPrint::Hidden('race_after_note',$race_results->race_after_note); ?>
    </td>
</tr>
<tr>
    <th colspan="2">今週の注目レース</th>
</tr>
<tr>
    <th>(火)</th>
    <td style="max-width: 250px;">
        <?=nl2br(h($race_results->jra_thisweek_horse_1))?>&nbsp;
        <?php HTPrint::Hidden('jra_thisweek_horse_1',$race_results->jra_thisweek_horse_1); ?>
    </td>
</tr>
<tr>
    <th>(木)</th>
    <td style="max-width: 250px;">
        <?=nl2br(h($race_results->jra_thisweek_horse_2))?>&nbsp;
        <?php HTPrint::Hidden('jra_thisweek_horse_2',$race_results->jra_thisweek_horse_2); ?>
    </td>
</tr>
<tr>
    <th>並び順</th>
    <td><?php HTPrint::HiddenAndText('jra_thisweek_horse_sort_number',$race_results->jra_thisweek_horse_sort_number); ?></td>
</tr>
<tr><th colspan="2">スペシャル出馬表紹介</th></tr>
<tr>
    <td colspan="2" style="max-width: 300px;">
        <?=nl2br(h($race_results->jra_sps_comment))?>&nbsp;
        <?php HTPrint::Hidden('jra_sps_comment',$race_results->jra_sps_comment); ?>
    </td>
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
