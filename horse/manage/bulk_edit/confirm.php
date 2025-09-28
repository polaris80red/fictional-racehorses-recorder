<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
InAppUrl::init(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="競走馬レース情報一括編集｜確認";
$page->ForceNoindex();
$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }

$pdo= getPDO();
do{
    $errorHeader="HTTP/1.1 404 Not Found";
    $page->setErrorReturnLink("競走馬検索に戻る",InAppUrl::to("horse/search"));
    $horse_id=(string)filter_input(INPUT_POST,'horse_id');
    if($horse_id==''){
        $page->addErrorMsg("競走馬ID未指定");
        break;
    }
    $horse=Horse::getByHorseId($pdo,$horse_id);
    if($horse===false){
        $page->addErrorMsg("競走馬情報取得失敗\n入力ID：{$horse_id}");
        break;
    }
    $errorHeader="HTTP/1.1 403 Forbidden";
    $page->setErrorReturnLink("競走馬情報に戻る",InAppUrl::to("horse/",['horse_id'=>$horse_id]));
    if(!Session::currentUser()->canEditHorse($horse)){
        $page->addErrorMsg("編集権限がありません");
        break;  
    }
}while(false);
if($page->error_exists){
    header($errorHeader);
    $page->printCommonErrorPage();
    exit;
}
$page_urlparam=new UrlParams([
    'horse_id'=>$horse_id,
]);
$race_history=new HorseRaceHistory($pdo,$horse_id);
$race_history->setDateOrder('ASC');
$race_history->getData();

$sex_str=sex2String($horse->sex);

$posted_race_list=isset($_POST['race'])?$_POST['race']:false;
$sex_gelding_override=false;
$nar_override=0;

$has_change=false;
$has_error=false;
$additionalData=[];
foreach ($race_history as $key => $data){
    if(empty($data->race_id)){ continue; }
    $addData=new stdClass;

    $posted_race=isset($posted_race_list[$data->race_id])?(object)$posted_race_list[$data->race_id]:false;
    if($posted_race===false){ continue; }

    $race_result= new RaceResults();
    $result = $race_result->setDataById($pdo,$data->race_id,$horse_id);
    if(!$result){
        continue;
    }
    $row_has_change=false;
    $changed=(object)array_fill_keys([
        'frame_number',
        'horse_number',
        'result_number',
        'result_text',
        'favourite',
        'handicap',
        'h_weight',
        'time',
        'jockey',
        'tc',
        'trainer_name',
        'training_country',
        'sex',
        'is_affliationed_nar',
    ],false);
    if((int)$race_result->frame_number!==(int)$posted_race->frame_number){
        $race_result->frame_number=$posted_race->frame_number?:null;
        $changed->frame_number = $row_has_change = $has_change = true;
    }
    if((int)$race_result->horse_number!==(int)$posted_race->horse_number){
        $race_result->horse_number=$posted_race->horse_number?:null;
        $changed->horse_number = $row_has_change = $has_change = true;
    }
    // 着順欄は1枠で共通処理
    if(is_numeric($posted_race->result)||(string)$posted_race->result===''){
        $posted_race->result_number=$posted_race->result;
        $posted_race->result_text='';
    }else{
        $posted_race->result_number='';
        $posted_race->result_text=$posted_race->result;
    }
    if((int)$race_result->result_number!==(int)$posted_race->result_number){
        $race_result->result_number=$posted_race->result_number?:null;
        $changed->result_number = $row_has_change = $has_change = true;
    }
    if($race_result->result_text!==$posted_race->result_text){
        $race_result->result_text=$posted_race->result_text?:null;
        $changed->result_text = $row_has_change = $has_change = true;
    }
    if((int)$race_result->result_order!==(int)$posted_race->result_order){
        $race_result->result_order=$posted_race->result_order?:null;
        $changed->result_order = $row_has_change = $has_change = true;
    }
    if((int)$race_result->result_before_demotion!==(int)$posted_race->result_before_demotion){
        $race_result->result_before_demotion=(int)$posted_race->result_before_demotion;
        $changed->result_before_demotion = $row_has_change = $has_change = true;
    }
    if((int)$race_result->favourite!==(int)$posted_race->favourite){
        $race_result->favourite=$posted_race->favourite?:null;
        $changed->favourite = $row_has_change = $has_change = true;
    }
    if((string)$race_result->handicap!==(string)$posted_race->handicap){
        $race_result->handicap=$posted_race->handicap?:null;
        $changed->handicap = $row_has_change = $has_change = true;
    }
    if((string)$race_result->h_weight!==(string)$posted_race->h_weight){
        $race_result->h_weight=$posted_race->h_weight?:null;
        $changed->h_weight = $row_has_change = $has_change = true;
    }
    if((string)$race_result->time!==(string)$posted_race->time){
        $race_result->time=$posted_race->time?:null;
        $changed->time = $row_has_change = $has_change = true;
    }
    if((string)$race_result->jockey_name!==(string)$posted_race->jockey){
        $race_result->jockey_name=$posted_race->jockey?:null;
        $changed->jockey = $row_has_change = $has_change = true;
    }
    if((string)$race_result->tc!==(string)$posted_race->tc){
        $race_result->tc=$posted_race->tc?:null;
        $changed->tc = $row_has_change = $has_change = true;
    }
    if((string)$race_result->trainer_name!==(string)$posted_race->trainer_name){
        $race_result->trainer_name=$posted_race->trainer_name?:null;
        $changed->trainer_name = $row_has_change = $has_change = true;
    }
    if((string)$race_result->training_country!==(string)$posted_race->training_country){
        $race_result->training_country=$posted_race->training_country?:null;
        $changed->training_country = $row_has_change = $has_change = true;
    }
    if((int)$race_result->sex!==(int)$posted_race->sex||$sex_gelding_override){
        $race_result->sex=$posted_race->sex;
        if($race_result->sex==3){
            $sex_gelding_override=true;
        }
        if($sex_gelding_override){
            $race_result->sex=3;
        }
        $changed->sex = $row_has_change = $has_change = true;
    }
    if((int)$race_result->is_affliationed_nar!==(int)$posted_race->is_affliationed_nar){
        // 地方区分が変更されている場合の反映処理
        $race_result->is_affliationed_nar=$posted_race->is_affliationed_nar;
        $changed->is_affliationed_nar = $row_has_change = $has_change = true;

        // 継続反映用
        if((int)$posted_race->is_affliationed_nar>0){
            $nar_override=(int)$posted_race->is_affliationed_nar;
        }
    }
    if($nar_override>0 && (int)$race_result->is_affliationed_nar>0){
        // カク[地]・マル(地)が違うレコードが出てきたら、そのあとの区分なしはそれに変更
        $nar_override=(int)$race_result->is_affliationed_nar;
    }
    if((int)$race_result->is_affliationed_nar===0 && $nar_override>0){
        // 最後の変更がカク[地]またはマル(地)への変更の場合、地方区分なしの行にも反映
        $race_result->is_affliationed_nar=$nar_override;
        $changed->is_affliationed_nar = $row_has_change = $has_change = true;
    }
    if($row_has_change && !$race_result->varidate()){
        // 変更点がある場合、エラーチェック
        $has_error=true;
    }
    $addData->race_result=$race_result;
    $addData->row_has_change=$row_has_change;
    $addData->changed=$changed;

    $additionalData[$key]=$addData;
}
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
td.changed{ background-color: yellow; }

.disabled_row{ background-color: #dddddd; }

table.horse_history { margin-top: 8px; }
td.race_course_name { text-align: center; }
td.grade{ text-align:center; }
td.frame_number{ text-align:center; }
td.horse_number{ text-align:center; }
td.favourite{ text-align:right; }
td.result_number{ text-align:right;}
td.result_order{ text-align:right; }
td.result_before_demotion{ text-align:right; }
td.sex{ text-align:center; }
td.is_affliationed_nar{ text-align:center; }
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php $page->printTitle(); ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php include (new TemplateImporter('horse/horse_page-header.inc.php'));?>
<form method="post" action="./execute.php">
<?php (new FormCsrfToken())->printHiddenInputTag(); ?>
<input type="submit" value="一括変更を実行"<?=($has_change && !$has_error)?'':' disabled'?>>
<input type="hidden" name="horse_id" value="<?=h($horse_id)?>">
<table class="horse_history">
<?php $colSpan=19; ?>
<tr>
    <th><?=$setting->horse_record_date==='umm'?'時期':'年月'?></th>
    <th>開催</th>
    <th>レース名</th>
    <th>格付</th>
    <th>枠</th>
    <th>馬</th>
    <th>人気</th>
    <th>着順</th>
    <th>補正</th>
    <th>降</th>
    <th>騎手</th>
    <th>斤量</th>
    <th>タイム</th>
    <th>馬体重</th>
    <th>所属</th>
    <th>厩舎</th>
    <th>調教国</th>
    <th>性別</th>
    <th>地方区分</th>
</tr>
<?php foreach ($race_history as $key => $data):?>
<?php
    $race = $data->race_row;
    $grade = $data->grade_row;
    $jockey=$data->jockey_row;

    $race_result=$additionalData[$key]->race_result??null;
    $changed=$additionalData[$key]->changed??false;
    $row_has_change=$additionalData[$key]->row_has_change??false;

    if($row_has_change===false){
        // 変更箇所がない場合はスキップする
        continue;
    }
    $tr_class=new Imploader(' ');
    if($data->is_registration_only==1){
        $tr_class->add('disabled_row');
    }
    $tr_class->add($grade->css_class);
    if($race->is_enabled===0){ $tr_class->add('disabled_row'); }
?>
<tr class="<?=h($tr_class)?>">
<?php
    $datetime=null;
    if($race->date!=null && $race->is_tmp_date==0){
        $datetime=new DateTime($race->date);
    }
    $month=$race->month;
    // ウマ娘ターン表記の場合は補正済み月を優先
    if($setting->horse_record_date==='umm' && $data->w_month > 0){
        $month=$data->w_month;
    }
    $day=is_null($datetime)?0:(int)$datetime->format('d');
    $date_str=$setting->getRaceListDate([
        'year'=>$race->year,
        'month'=>$month,
        'day'=>$day,
        'turn'=>$data->umm_month_turn,
        'age'=>$race->year - $horse->birth_year]);
    $date_str=(new MkTagA($date_str))->get();
?>
<td><?=$date_str?></td>
<?php
    $a_tag=new MkTagA($data->course_row->short_name??$race->race_course_name);
    if($datetime!==null){
        $a_tag->title($race->race_course_name);
    }
?>
<td class="race_course_name"><?=$a_tag?></td>
<td class="race_name"><?=h($race->race_short_name?:$race->race_name)?></td>
<td class="grade"><?=h(($grade->short_name??'')?:$race->grade)?></td>
<td class="frame_number <?=!$changed->frame_number?'':'changed'?>"><?=h($race_result->frame_number)?>
    <input type="hidden" name="race[<?=h($data->race_id)?>][frame_number]" style="width: 2.5em;" value="<?=h($race_result->frame_number)?>">
</td>
<td class="horse_number <?=!$changed->horse_number?'':'changed'?>"><?=h($race_result->horse_number)?>
    <input type="hidden" name="race[<?=h($data->race_id)?>][horse_number]" style="width: 2.5em;" value="<?=h($race_result->horse_number)?>">
</td>
<td class="favourite <?=!$changed->favourite?'':'changed'?>">
    <?=h($race_result->favourite)?>
    <input type="hidden" name="race[<?=h($data->race_id)?>][favourite]" style="width: 2.5em;" value="<?=h($race_result->favourite)?>">
</td>
<td class="result_number <?=($changed->result_number||$changed->result_text)?'changed':''?>">
    <?=h($race_result->result_text?:$race_result->result_number)?>
    <input type="hidden" name="race[<?=h($data->race_id)?>][result_number]" value="<?=h($race_result->result_number)?>">
    <input type="hidden" name="race[<?=h($data->race_id)?>][result_text]" value="<?=h($race_result->result_text)?>">
</td>
<td class="result_order <?=!$changed->result_order?'':'changed'?>">
    <?=h($race_result->result_order)?>
    <input type="hidden" name="race[<?=h($data->race_id)?>][result_order]" value="<?=h($race_result->result_order)?>">
</td>
<td class="result_before_demotion <?=!$changed->result_before_demotion?'':'changed'?>">
    <?=h($race_result->result_before_demotion?($race_result->result_before_demotion.'位入線'):'')?>
    <input type="hidden" name="race[<?=h($data->race_id)?>][result_before_demotion]" value="<?=h($race_result->result_before_demotion)?>">
</td>
<td class="<?=!$changed->jockey?'':'changed'?>">
    <?=h($race_result->jockey_name)?>
    <input type="hidden" name="race[<?=h($data->race_id)?>][jockey]" value="<?=h($race_result->jockey_name)?>">
</td>
<td class="handicap <?=!$changed->handicap?'':'changed'?>">
    <?=h($race_result->handicap)?>
    <input type="hidden" name="race[<?=h($data->race_id)?>][handicap]" value="<?=h($race_result->handicap)?>">
</td>
<td class="time <?=!$changed->time?'':'changed'?>">
    <?=h($race_result->time)?>
    <input type="hidden" name="race[<?=h($data->race_id)?>][time]" value="<?=h($race_result->time)?>">
</td>
<td class="h_weight <?=!$changed->h_weight?'':'changed'?>">
    <?=h($race_result->h_weight)?>
    <input type="hidden" name="race[<?=h($data->race_id)?>][h_weight]" value="<?=h($race_result->h_weight)?>">
</td>
<td class="tc <?=!$changed->tc?'':'changed'?>">
    <?=h($race_result->tc)?>
    <input type="hidden" name="race[<?=h($data->race_id)?>][tc]" value="<?=h($race_result->tc)?>">
</td>
<td class="trainer_name <?=!$changed->trainer_name?'':'changed'?>">
    <?=h($race_result->trainer_name)?>
    <input type="hidden" name="race[<?=h($data->race_id)?>][trainer_name]" value="<?=h($race_result->trainer_name)?>">
</td>
<td class="training_country <?=!$changed->training_country?'':'changed'?>">
    <?=h($race_result->training_country)?>
    <input type="hidden" name="race[<?=h($data->race_id)?>][training_country]" value="<?=h($race_result->training_country)?>">
</td>
<td class="sex <?=!$changed->sex?'':'changed'?>">
    <?=h($race_result->sex==0?'元':($race_result->sex==1?'牡':'セ'))?>
    <input type="hidden" name="race[<?=h($data->race_id)?>][sex]" value="<?=h($race_result->sex)?>">
</td>
<?php $n_radio=MkTagInput::Radio("race[".$data->race_id."][is_affliationed_nar]"); ?>
<td class="is_affliationed_nar <?=!$changed->is_affliationed_nar?'':'changed'?>">
    <?=h($race_result->is_affliationed_nar==0?'なし':($race_result->is_affliationed_nar==1?'[地]':'(地)'))?>
    <input type="hidden" name="race[<?=h($data->race_id)?>][is_affliationed_nar]" value="<?=h($race_result->is_affliationed_nar)?>">
</td>
</tr>
<?php if($race_result->error_exists):?>
    <tr><td colspan="<?=$colSpan?>" style="color:red;"><?=nl2br(h(implode("\n",$race_result->error_msgs)))?></td></tr>
<?php endif;?>
<?php endforeach; ?>
</table>
</form>
<a id="under_results_table"></a>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>