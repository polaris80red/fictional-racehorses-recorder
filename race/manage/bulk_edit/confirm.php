<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
InAppUrl::init(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース個別結果一括編集｜内容確認";
if(!Session::isLoggedIn()){ $page->exitToHome(); }
$csrf_token=new FormCsrfToken();

$page->error_return_url=$page->to_race_list_path;
$page->error_return_link_text="レース検索に戻る";

$pdo= getPDO();

if(empty($_POST['race_id'])){
    $page->error_msgs[]="レースID未指定";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
$race_id=filter_input(INPUT_POST,'race_id');
# レース情報取得
$race = Race::getByRaceId($pdo, $race_id);
if(!$race){
    $page->error_msgs[]="レース情報取得失敗";
    $page->error_msgs[]="入力ID：{$race_id}";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
if(!Session::currentUser()->canEditOtherHorse()){
    // ほかのユーザーの競走馬の記録を編集できる権限がない場合は一括編集不可
    header("HTTP/1.1 403 Forbidden");
    $page->addErrorMsg("編集権限がありません");
    $page->printCommonErrorPage();
    exit;
}
$week_data=RaceWeek::getById($pdo,$race->week_id);
$week_month=$week_data->month;
$turn=$week_data->umm_month_turn;

$resultsGetter=new RaceResultsGetter($pdo,$race_id,$race->year);
$resultsGetter->pageIsEditable=$page->is_editable;
$resultsGetter->addOrderParts([
    "`r_results`.`frame_number` IS NULL",
    "`r_results`.`frame_number` ASC",
    "`r_results`.`horse_number` IS NULL",
    "`r_results`.`horse_number` ASC",
    "`horse`.`name_en` ASC",
]);
$table_data=$resultsGetter->getTableData();
$hasThisweek=$resultsGetter->hasThisweek;
$hasSps=$resultsGetter->hasSps;
$mode_umm=false;
switch($setting->age_view_mode){
    case Setting::AGE_VIEW_MODE_UMAMUSUME:
    case Setting::AGE_VIEW_MODE_UMAMUSUME_S:
        $mode_umm=true;
}
$has_error=false;
$has_change=false;
$additionalData=[];
foreach($table_data as $key => $data){
    $addData=new stdClass;

    $horse=$data->horseRow;
    $raceResult=$data->resultRow;
    $newResult= RaceResults::getRowByIds($pdo,$race_id,$horse->horse_id);
    if(!$newResult){
        continue;
    }
    if(!isset($_POST['race'][$horse->horse_id])){
        // その馬のデータがなければスキップ
        continue;
    }else{
        $inputHorseResultRow=$_POST['race'][$horse->horse_id];
    }
    $input='';
    $changed=[];
    if(isset($inputHorseResultRow['frame_number'])){
        $input = max((int)mb_convert_kana(trim($inputHorseResultRow['frame_number']),'n'),0);
        if((int)$newResult->frame_number != $input){
            $newResult->frame_number = $input?:null;
            $changed['frame_number'] = $has_change = true;
        }
    }
    if(isset($inputHorseResultRow['horse_number'])){
        $input = max((int)mb_convert_kana(trim($inputHorseResultRow['horse_number']),'n'),0);
        if((int)$newResult->horse_number != $input){
            $newResult->horse_number = $input?:null;
            $changed['horse_number'] = $has_change = true;
        }
    }
    if(isset($inputHorseResultRow['result'])){
        $resultValue = mb_convert_kana(trim($inputHorseResultRow['result']),'n');
        if(is_numeric($resultValue)||(string)$resultValue===''){
            $inputResultNumber=max($resultValue,0)?:null;
            $inputResultText=null;
        }else{
            $inputResultNumber=null;
            $inputResultText=$resultValue;
        }
        if((int)$newResult->result_number !== (int)$inputResultNumber){
            $newResult->result_number=$inputResultNumber;
            $changed['result'] = $has_change = true;
        }
        if((string)$newResult->result_text != $inputResultText){
            $newResult->result_number=null;
            $newResult->result_text=$inputResultText;
            $changed['result'] = $has_change = true;
        }
    }
    if(isset($inputHorseResultRow['handicap'])){
        $input = mb_convert_kana(trim($inputHorseResultRow['handicap']),'n');
        if((string)$newResult->handicap != $input){
            $newResult->handicap = $input;
            $changed['handicap'] = $has_change = true;
        }
    }
    if(isset($inputHorseResultRow['jockey_name'])
        && (string)$newResult->jockey_name != (string)$inputHorseResultRow['jockey_name']){
        $newResult->jockey_name = $inputHorseResultRow['jockey_name'];
        $changed['jockey_name'] = $has_change = true;
    }
    if(isset($inputHorseResultRow['time'])){
        $input = mb_convert_kana(trim($inputHorseResultRow['time']),'n');
        if((string)$newResult->time != $input){
            $newResult->time = $input;
            $changed['time'] = $has_change = true;
        }
    }
    if(isset($inputHorseResultRow['margin'])
        && (string)$newResult->margin != (string)$inputHorseResultRow['margin']){
        $newResult->margin = $inputHorseResultRow['margin'];
        $changed['margin'] = $has_change = true;
    }
    if(isset($inputHorseResultRow['corner_1'])){
        $input = max((int)mb_convert_kana(trim($inputHorseResultRow['corner_1']),'n'),0);
        if((int)$newResult->corner_1 != $input){
            $newResult->corner_1 = $input?:null;
            $changed['corner_1'] = $has_change = true;
        }
    }
    if(isset($inputHorseResultRow['corner_2'])){
        $input = max((int)mb_convert_kana(trim($inputHorseResultRow['corner_2']),'n'),0);
        if((int)$newResult->corner_2 != $input){
            $newResult->corner_2 = $input?:null;
            $changed['corner_2'] = $has_change = true;
        }
    }
    if(isset($inputHorseResultRow['corner_3'])){
        $input = max((int)mb_convert_kana(trim($inputHorseResultRow['corner_3']),'n'),0);
        if((int)$newResult->corner_3 != $input){
            $newResult->corner_3 = $input?:null;
            $changed['corner_3'] = $has_change = true;
        }
    }
    if(isset($inputHorseResultRow['corner_4'])){
        $input = max((int)mb_convert_kana(trim($inputHorseResultRow['corner_4']),'n'),0);
        if((int)$newResult->corner_4 != $input){
            $newResult->corner_4 = $input?:null;
            $changed['corner_4'] = $has_change = true;
        }
    }
    if(isset($inputHorseResultRow['f_time'])){
        $input = mb_convert_kana(trim($inputHorseResultRow['f_time']),'n');
        if((string)$newResult->f_time != $input){
            $newResult->f_time = $input;
            $changed['f_time'] = $has_change = true;
        }
    }
    if(isset($inputHorseResultRow['tc'])
        && (string)$newResult->tc != (string)$inputHorseResultRow['tc']){
        $newResult->tc = $inputHorseResultRow['tc'];
        $changed['tc'] = $has_change = true;
    }
    if(isset($inputHorseResultRow['trainer_name'])
        && (string)$newResult->trainer_name != (string)$inputHorseResultRow['trainer_name']){
        $newResult->trainer_name = $inputHorseResultRow['trainer_name'];
        $changed['trainer_name'] = $has_change = true;
    }
    if(isset($inputHorseResultRow['h_weight'])){
        $input = max((int)mb_convert_kana(trim($inputHorseResultRow['h_weight']),'n'),0);
        if((int)$newResult->h_weight != $input){
            $newResult->h_weight = $input?:null;
            $changed['h_weight'] = $has_change = true;
        }
    }
    if(isset($inputHorseResultRow['odds'])){
        $input = mb_convert_kana(trim($inputHorseResultRow['odds']),'n');
        if((string)$newResult->odds != $input){
            $newResult->odds = $input;
            $changed['odds'] = $has_change = true;
        }
    }
    if(isset($inputHorseResultRow['favourite'])){
        $input = max((int)mb_convert_kana(trim($inputHorseResultRow['favourite']),'n'),0);
        if((int)$newResult->favourite != $input){
            $newResult->favourite = $input?:null;
            $changed['favourite'] = $has_change = true;
        }
    }
    if(isset($inputHorseResultRow['earnings'])){
        $input = max((int)mb_convert_kana(trim($inputHorseResultRow['earnings']),'n'),0);
        if((int)$newResult->earnings != (int)$input){
            $newResult->earnings = $input;
            $changed['earnings'] = $has_change = true;
        }
    }
    if(isset($inputHorseResultRow['syuutoku'])){
        $input = max((int)mb_convert_kana(trim($inputHorseResultRow['syuutoku']),'n'),0);
        if((int)$newResult->syuutoku != (int)$input){
            $newResult->syuutoku = $input;
            $changed['syuutoku'] = $has_change = true;
        }
    }
    if(!$newResult->validate()){
        $has_error=true;
    }
    $addData->newResult=$newResult;
    $addData->changed=$changed;
    $additionalData[$key]=$addData;
}
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?=h($page->renderTitle())?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?=$page->renderBaseStylesheetLinks()?>
    <?=$page->renderJqueryResource()?>
    <?=$page->renderScriptLink("js/functions.js")?>
    <style>
        td.changed{ background-color: yellow; }
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
<form action="execute.php" method="post">
<?php $colSpan=21; ?>
<table class="race_results">
<tr>
    <th>枠</th>
    <th>馬<br>番</th>
    <th>着<br>順</th>
    <th style="min-width:12em;">馬名</th>
    <th><?=h($mode_umm?"級":"性齢")?></th>
    <th>負担<br>重量</th>
    <th>騎手</th>
    <th>タイム</th>
    <th>着差</th>
    <th colspan="4">通過順位</th>
    <th>上り</th>
    <th>所属<br>上書</th>
    <th>調教師<br>上書</th>
    <th>馬体重</th>
    <th>単勝</th>
    <th>人気</th>
    <th>賞金</th>
    <th>収得</th>
</tr>
<?php foreach ($table_data as $key => $data):?>
    <?php
        $horse=$data->horseRow;
        $raceResult=$data->resultRow;
        if(!isset($_POST['race'][$horse->horse_id])){
            // 送信データにその行の馬のデータが存在しない場合はスキップ
            continue;
        }
        $addData=$additionalData[$key];
        $changed=$addData->changed;
        $newResult=$addData->newResult;
    ?>
    <tr class="">
        <td class="<?=($changed['frame_number']??false)?'changed':''?>">
            <?=h($newResult->frame_number)?>
            <input type="hidden" name="race[<?=h($horse->horse_id)?>][frame_number]" value="<?=h($newResult->frame_number)?>">
        </td>
        <td class="<?=($changed['horse_number']??false)?'changed':''?>">
            <?=h($newResult->horse_number)?>
            <input type="hidden" name="race[<?=h($horse->horse_id)?>][horse_number]" value="<?=h($newResult->horse_number)?>">
        </td>
        <td class="<?=($changed['result']??false)?'changed':''?>">
            <?=h($newResult->result_text?:$newResult->result_number)?>
            <input type="hidden" name="race[<?=h($horse->horse_id)?>][result]" value="<?=h($newResult->result_text?:$newResult->result_number)?>">
        </td>
        <td class="horse_name"><?=$horse->name_ja?:$horse->name_en?></td>
        <?php
            $age_sex_str='';
            if($setting->age_view_mode===Setting::AGE_VIEW_MODE_DEFAULT){
                // 通常表記の場合
                $age_sex_str.=$data->sexStr;
            }
            $age_sex_str.=$setting->getAgeSexSpecialFormat($data->age,$data->sex);
        ?>
        <td class="age sex_<?=h($data->sex)?>"><?=h($age_sex_str)?></td>
        <td class="<?=($changed['handicap']??false)?'changed':''?>">
            <?=h($newResult->handicap)?>
            <input type="hidden" name="race[<?=h($horse->horse_id)?>][handicap]" value="<?=h($newResult->handicap)?>">
        </td>
        <td class="<?=($changed['jockey_name']??false)?'changed':''?>">
            <?=h($newResult->jockey_name)?>
            <input type="hidden" name="race[<?=h($horse->horse_id)?>][jockey_name]" value="<?=h($newResult->jockey_name)?>">
        </td>
        <td class="<?=($changed['time']??false)?'changed':''?>">
            <?=h($newResult->time)?>
            <input type="hidden" name="race[<?=h($horse->horse_id)?>][time]" value="<?=h($newResult->time)?>">
        </td>
        <td class="<?=($changed['margin']??false)?'changed':''?>">
            <?=h($newResult->margin)?>
            <input type="hidden" name="race[<?=h($horse->horse_id)?>][margin]" value="<?=h($newResult->margin)?>">
        </td>
        <td class="<?=($changed['corner_1']??false)?'changed':''?>">
            <?=h($newResult->corner_1?:'')?>
            <input type="hidden" name="race[<?=h($horse->horse_id)?>][corner_1]" value="<?=h($newResult->corner_1?:'')?>">
        </td>
        <td class="<?=($changed['corner_2']??false)?'changed':''?>">
            <?=h($newResult->corner_2?:'')?>
            <input type="hidden" name="race[<?=h($horse->horse_id)?>][corner_2]" value="<?=h($newResult->corner_2?:'')?>">
        </td>
        <td class="<?=($changed['corner_3']??false)?'changed':''?>">
            <?=h($newResult->corner_3?:'')?>
            <input type="hidden" name="race[<?=h($horse->horse_id)?>][corner_3]" value="<?=h($newResult->corner_3?:'')?>">
        </td>
        <td class="<?=($changed['corner_4']??false)?'changed':''?>">
            <?=h($newResult->corner_4?:'')?>
            <input type="hidden" name="race[<?=h($horse->horse_id)?>][corner_4]" value="<?=h($newResult->corner_4?:'')?>">
        </td>
        <td class="<?=($changed['f_time']??false)?'changed':''?>">
            <?=h($newResult->f_time)?>
            <input type="hidden" name="race[<?=h($horse->horse_id)?>][f_time]" value="<?=h($newResult->f_time)?>">
        </td>
        <td class="<?=($changed['tc']??false)?'changed':''?>">
            <?=h($newResult->tc)?>
            <input type="hidden" name="race[<?=h($horse->horse_id)?>][tc]" value="<?=h($newResult->tc)?>">
        </td>
        <td class="<?=($changed['trainer_name']??false)?'changed':''?>">
            <?=h($newResult->trainer_name)?>
            <input type="hidden" name="race[<?=h($horse->horse_id)?>][trainer_name]" value="<?=h($newResult->trainer_name)?>">
        </td>
        <td class="<?=($changed['h_weight']??false)?'changed':''?>">
            <?=h($newResult->h_weight)?>
            <input type="hidden" name="race[<?=h($horse->horse_id)?>][h_weight]" value="<?=h($newResult->h_weight)?>">
        </td>
        <td class="<?=($changed['odds']??false)?'changed':''?>">
            <?=h($newResult->odds)?>
            <input type="hidden" name="race[<?=h($horse->horse_id)?>][odds]" value="<?=h($newResult->odds)?>">
        </td>
        <td class="<?=($changed['favourite']??false)?'changed':''?>">
            <?=h($newResult->favourite)?>
            <input type="hidden" name="race[<?=h($horse->horse_id)?>][favourite]" value="<?=h($newResult->favourite)?>">
        </td>
        <td class="<?=($changed['earnings']??false)?'changed':''?>">
            <?=h($newResult->earnings)?>
            <input type="hidden" name="race[<?=h($horse->horse_id)?>][earnings]" value="<?=h($newResult->earnings)?>">
        </td>
        <td class="<?=($changed['syuutoku']??false)?'changed':''?>">
            <?=h($newResult->syuutoku?:'')?>
            <input type="hidden" name="race[<?=h($horse->horse_id)?>][syuutoku]" value="<?=h($newResult->syuutoku)?>">
        </td>
    </tr>
    <?php if($newResult->hasErrors):?>
        <tr><td colspan="<?=$colSpan?>" style="color:red;"><?=nl2br(h(implode("\n",$newResult->errorMessages)))?></td></tr>
    <?php endif;?>
<?php endforeach;?>
</table>
<input type="hidden" name="race_id" value="<?=$race_id?>">
<?php $csrf_token->printHiddenInputTag(); ?>
<input type="submit" value="登録処理実行"<?=!$has_change||$has_error?' disabled':''?>>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>