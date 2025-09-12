<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース結果登録内容確認";
$page->ForceNoindex();
$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }
$csrf_token=new FormCsrfToken();

$race_id=(string)filter_input(INPUT_POST,'race_id');
$is_edit_mode=filter_input(INPUT_POST,'edit_mode')?1:0;
$horse_id=(string)filter_input(INPUT_POST,'horse_id')?:'';// 登録後に馬戦績登録時
# 対象取得
$race= new Race();
$pdo= getPDO();

if($race_id!=''){
    if($race->setRaceId($race_id)===false){
        $page->addErrorMsgArray($race->error_msgs);
        $page->printCommonErrorPage();
        exit;
    }
    $race->setDataById($pdo,$race_id);
    if($is_edit_mode==0 && $race->record_exists){
        $page->addErrorMsg('新規モードで重複IDあり');
        $page->printCommonErrorPage();
        exit;
    }
}
if($race->setDataByPost()==false){
    $page->debug_dump_var[]=$race;
    $page->addErrorMsgArray($race->error_msgs);
    $page->printCommonErrorPage();
    exit;
}
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?>（<?=$is_edit_mode?"編集":"新規"?>）</title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php $page->printTitle(); ?>（<?=$is_edit_mode?"編集":"新規"?>）</h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<form action="./execute.php" method="post">
<input type="hidden" name="edit_mode" value="<?=$is_edit_mode?1:0?>">
<table class="edit-form-table">
<tr>
    <th>レースID</th>
    <td><?php HTPrint::Hidden('race_id',$race_id);print($race_id?:'登録実行時に生成'); ?></td>
</tr>
<tr>
    <th>ワールド</th>
    <?php $world=World::getById($pdo,$race->world_id) ?>
    <td><?=(MkTagInput::Hidden('world_id',$race->world_id))."{$race->world_id}:{$world['name']}"?></td>
</tr>
<tr>
    <th>競馬場</th>
    <td>
        <?=h($race->race_course_name)?><?=h($race->race_number?"{$race->race_number}R":'')?>
        <?php HTPrint::Hidden('race_course_name',$race->race_course_name) ?>
        <?php HTPrint::Hidden('race_number',$race->race_number) ?>
    </td>
</tr>
<tr>
    <th>距離</th>
    <td><?=h($race->course_type)?><?=h($race->distance)?>
    <?php HTPrint::Hidden('course_type',$race->course_type) ?>
    <?php HTPrint::Hidden('distance',$race->distance) ?>
    </td>
</tr>
<tr>
    <th>レース名</th>
    <td><?php HTPrint::HiddenAndText('race_name',$race->race_name) ?></td>
</tr>
<tr>
    <th>出馬表等略名</th>
    <td><?php HTPrint::HiddenAndText('race_short_name',$race->race_short_name) ?></td>
</tr>
<tr>
    <th>キャプション</th>
    <td><?php HTPrint::HiddenAndText('caption',$race->caption) ?></td>
</tr>
<tr>
    <th>グレード</th>
    <td><?php HTPrint::HiddenAndText('grade',$race->grade) ?></td>
</tr>
<tr>
    <th>馬齢条件</th>
    <td><?=h(RaceCategoryAge::getNameById($pdo,$race->age_category_id))?><?php HTPrint::Hidden('age_category_id',$race->age_category_id) ?></td>
</tr>
<tr>
    <th>馬齢(手入力)</th>
    <td><?php HTPrint::HiddenAndText('age',$race->age) ?></td>
</tr>
<tr>
    <th>性別条件</th>
    <td><?=h(RaceCategorySex::getShortNameById($pdo,$race->sex_category_id))?><?php HTPrint::Hidden('sex_category_id',$race->sex_category_id) ?></td>
</tr>
<tr>
    <th>天候</th>
    <td><?php HTPrint::HiddenAndText('weather',$race->weather) ?></td>
</tr>
<tr>
    <th>馬場状態</th>
    <td><?php HTPrint::HiddenAndText('track_condition',$race->track_condition) ?></td>
</tr>
<tr>
    <th>頭数</th>
    <td><?php HTPrint::HiddenAndText('number_of_starters',$race->number_of_starters) ?></td>
</tr>
<tr>
    <th>JRA</th>
    <td><?=$race->is_jra?'はい':'いいえ'?><?php HTPrint::Hidden('is_jra',$race->is_jra) ?></td>
</tr>
<tr>
    <th>地方</th>
    <td><?=$race->is_nar?'はい':'いいえ'?><?php HTPrint::Hidden('is_nar',$race->is_nar) ?></td>
</tr>
<tr>
    <th>正規日付</th>
    <td><?php HTPrint::HiddenAndText('date',$race->date) ?></td>
</tr>
<tr>
    <th>仮の日付</th>
    <td><?=$race->is_tmp_date?'はい':'いいえ'?><?php HTPrint::Hidden('is_tmp_date',$race->is_tmp_date) ?></td>
</tr>
<tr>
    <th>年月</th>
    <td>
        <?=h($setting->getConvertedDate(['year'=>$race->year,'month'=>$race->month]))?>
        <?php HTPrint::Hidden('year',$race->year) ?>
        <?php HTPrint::Hidden('month',$race->month) ?>
    </td>
</tr>
<tr>
    <th>週</th>
    <td>
        <?php
            $week_str='';
            if($race->week_id>0){
                $week=RaceWeek::getById($pdo,$race->week_id);
                $week_str="第{$week->id}週（{$week->month}月）{$week->name}";
            }
        ?>
        <?=h($week_str)?><?php HTPrint::Hidden('week_id',$race->week_id) ?>
    </td>
</tr>
<tr>
    <th>備考</th>
    <td>
        <?=nl2br(h($race->note))?>
        <?php HTPrint::Hidden('note',$race->note); ?>
    </td>
</tr>
<tr>
    <th>論理削除状態</th>
    <td>
        <?=$race->is_enabled?'表示する':'非表示'?>
        <?php HTPrint::Hidden('is_enabled',$race->is_enabled) ?>
    </td>
</tr>
<?php if($horse_id && !$is_edit_mode): ?>
<tr>
    <th>登録後に<br>個別結果登録</th>
    <td><?=(MkTagInput::Hidden('horse_id',$horse_id));?><?=h($horse_id)?></td>
</tr>
<?php endif; ?>
</table>
<hr>
<input type="submit" value="レース結果データ登録実行">
<?php $csrf_token->printHiddenInputTag(); ?>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>
