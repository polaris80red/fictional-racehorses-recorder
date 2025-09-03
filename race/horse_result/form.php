<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース結果詳細・登録";
$page->ForceNoindex();
$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$race_result_id=(string)filter_input(INPUT_GET,'race_id');
$horse_id=(string)filter_input(INPUT_GET,'horse_id');
$result_number=filter_input(INPUT_GET,'result_number');

$is_edit_mode=filter_input(INPUT_GET,'edit_mode')?1:0;

$next_race_id=filter_input(INPUT_GET,'next_race_id');
$next_race_data=null;

$form_data= new RaceResults();
$form_data->result_number=$result_number;

# 対象取得
$pdo= getPDO();
$horse=new Horse();
if($horse_id){
    $horse->setDataById($pdo, $horse_id);
}
$race=new Race();
if($race_result_id){
    $race->setDataById($pdo, $race_result_id);
}
do{
    if($horse_id!=='' && !$horse->record_exists){
        // 競走馬ID指定ありでレコード無し
        $page->addErrorMsg("存在しない競走馬ID｜$horse_id");
        break;
    }
    if($race_result_id!=='' && !$race->record_exists){
        // レースID指定ありでレコード無し
        $page->addErrorMsg("存在しないレース結果ID｜$race_result_id");
        break;
    }
    # 該当結果を取得
    if($is_edit_mode){
        if($horse_id===''){
            $page->addErrorMsg("編集モードですが競走馬IDが指定されていません");
            break;
        }
        if($horse_id===''){
            $page->addErrorMsg("編集モードですがレース結果IDが指定されていません");
            break;
        }
    }
    if($horse_id!=='' && $race_result_id!==''){
        // レースと馬が両方指定されている状態のとき
        $result=$form_data->setDataById($pdo,$race_result_id,$horse_id);
        if($result && !$is_edit_mode){
            $page->addErrorMsg("登録予定のレース個別結果が既に存在します｜$race_result_id|$horse_id");
            break;
        }
        if(!$result && $is_edit_mode){
            $page->addErrorMsg("編集対象のレース個別結果が存在しません｜$race_result_id|$horse_id");
            break;
        }
        if($horse->world_id!==$race->world_id){
            $page->addErrorMsg("競走馬とレース情報のワールドが一致していません");
            break;
        }
    }
    if(!$is_edit_mode && $next_race_id!=''){
        $next_race_data = new RaceResults();
        $next_race_data->setDataById($pdo,$next_race_id,$horse_id);
        $next_race = new Race($pdo,$next_race_id);
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}

?><!DOCTYPE html>
<html>
<head>
    <title><?=h($page->title)?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink("js/functions.js"); ?>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?=h($page->title)?><?=h($is_edit_mode?"(編集)":"") ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<form action="./registration_confirm.php" method="post">
<input type="hidden" name="edit_mode" value="<?php echo ($is_edit_mode)?1:0; ?>">
<table class="edit-form-table floatLeft" style="margin-right: 4px;">
<tr>
    <?php if(empty($race_result_id)): ?>
    <th>レースID</th>
    <td class="in_input" colspan="2"><input type="text" name="race_id" value="<?php echo $race_result_id; ?>" class="required" required></td>
    <?php else: ?>
    <th>レース名称</th>
    <td colspan="2">
        <?php HTPrint::Hidden('race_id',$race_result_id); ?>
        <?=h(($race->year?:"")." ".($race->race_name?:""))?>
    </td>
    <?php endif; ?>
</tr>
<tr>
    <?php if(empty($horse_id)): ?>
    <th>競走馬ID</th>
    <td class="in_input" colspan="2"><input type="text" name="horse_id" value="<?=h($horse_id)?>" class="required" required></td>
    <?php else: ?>
    <th>競走馬名</th>
    <td colspan="2">
        <?=h(!empty($horse->name_ja)?$horse->name_ja:($horse->name_en?:""))?>
        <?php HTPrint::Hidden('horse_id',h($horse_id)); ?>
    </td>
    <?php endif; ?>
</tr>
<tr><td colspan="3">
    <input type="button" id="random_imput_tgl_btn" value="▽　ランダム入力を開く">
    <input type="hidden" id="random_imput_tgl_is_enabled" value="0">
</td></tr>
<tr class="random_input_ui" style="display: none;"><td colspan="3">
    <input type="button" value="着順・人気・枠・4角 ランダム" onclick="randomSet();"><br>
    着順ダイス数<input type="number" style="width: 3em;" name="rand_dice_rn" value="1" placeholder="" data-disable-enter><br>
    人気ダイス数<input type="number" style="width: 3em;" name="rand_dice_f" value="1" placeholder="" data-disable-enter>
    <br>
    <input type="input" style="width: 80%;" id="dice_result" value="" placeholder="" data-disable-enter readonly>
</td></tr>
<tr>
    <th>着順</th>
    <td class="in_input">
    <select name="result_number_select" style="width:5em;" onchange="clearElmVal('*[name=result_number]');clearElmVal('*[name=result_order]');">
    <?php
        echo '<option value=""></option>'."\n";
        $selected_option_exists=false;
        for($i=1; $i<=18; $i++){
            if($i==$form_data->result_number){ $selected_option_exists=true; }
            echo '<option value="'.$i,'"'.(($i==$form_data->result_number)?' selected ':'').'>';
            echo $i."着";
            echo '</option>'."\n";
        }
    ?></select>／
    <input type="number" name="result_number" style="width:3em;" onchange="clearElmVal('*[name=result_number_select]');clearElmVal('*[name=result_order]');" value="<?php echo $selected_option_exists?'':$form_data->result_number; ?>">着
    </td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=result_number]');clearElmVal('*[name=result_number_select]');clearElmVal('*[name=result_order]');"></td>
</tr>
<tr>
    <th>表示順補正</th>
    <td class="in_input">
        <input type="number" name="result_order" style="width: 3em;" value="<?=h($form_data->result_order)?>">
        同着順はこの値で昇順
    </td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=result_order]');"></td>
</tr>
<tr>
    <th>特殊結果</th>
    <td class="in_input">
    <input type="text" name="result_text" style="width: 4em;" list="result_text_list" value="<?=h($form_data->result_text)?>" placeholder=""><span title="記載がある場合は着順を無視して代わりに表示します。>除外は戦績にカウントせず中止は数えます。">（着順の代わりに表示）</span>
    </td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=result_text]');"></td>
</tr>
<tr><td colspan="2">取消・非当・非抽・回避は<br>レース結果や出馬表非表示</td></tr>
<tr>
    <th>降着前入線順</th>
    <td class="in_input">
    <input type="number" name="result_before_demotion" style="width: 3em;" value="<?=h($form_data->result_before_demotion?:'')?>" placeholder=""><span title="空でない場合、着順などに降着表記">（※降着馬のみ入力）</span>
    </td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=result_before_demotion]');"></td>
</tr>
<tr>
    <th>枠・馬番</th>
    <td class="in_input">
        <select name="frame_number" style="width:3.5em;"><?php printSelectOptions([1,2,3,4,5,6,7,8],true,ifZero2Empty($form_data->frame_number),'','枠') ?></select>
        <select name="horse_number_select" style="width:4em;" onchange="clearElmVal('*[name=horse_number]');">
        <?php
            echo '<option value=""></option>'."\n";
            $selected_option_exists=false;
            for($i=1; $i<=18; $i++){
                if($i==$form_data->horse_number){ $selected_option_exists=true; }
                echo '<option value="'.$i,'"'.(($i==$form_data->horse_number)?' selected ':'').'>';
                echo $i."番";
                echo '</option>'."\n";
            }
        ?></select>／
        <input type="number" name="horse_number" style="width:3em;" onchange="clearElmVal('*[name=horse_number_select]');" value="<?=h($selected_option_exists?'':ifZero2Empty($form_data->horse_number))?>">番
    </td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=frame_number]');clearElmVal('*[name=horse_number]');clearElmVal('*[name=horse_number_select]');"></td>
</tr>
<tr>
    <th>騎手</th>
    <td class="in_input"><input type="text" name="jockey" style="width:10em;" value="<?=h($form_data->jockey)?>"></td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=jockey]');"></td>
</tr>
<tr>
    <th>斤量</th>
    <td class="in_input"><input type="text" name="handicap" style="width:6em;" list="handicap_list" value="<?=h($form_data->handicap)?>">kg</td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=handicap]');"></td>
</tr>
<tr>
    <th>着差</th>
    <td class="in_input"><input type="text" name="margin" list="margin_list" value="<?=h($form_data->margin)?>"></td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=margin]');"></td>
</tr>
<tr>
    <th rowspan="2">コーナー<br>通過順位</th>
    <td class="in_input">
    <input type="number" name="corner_1" style="width:2.5em;" value="<?php HTPrint::ifZero2Empty($form_data->corner_1); ?>">-
    <input type="number" name="corner_2" style="width:2.5em;" value="<?php HTPrint::ifZero2Empty($form_data->corner_2); ?>">-
    <input type="number" name="corner_3" style="width:2.5em;" value="<?php HTPrint::ifZero2Empty($form_data->corner_3); ?>">-
    <input type="number" name="corner_4" style="width:2.5em;" value="<?php HTPrint::ifZero2Empty($form_data->corner_4);; ?>">
    </td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=corner_1]');clearElmVal('*[name=corner_2]');clearElmVal('*[name=corner_3]');clearElmVal('*[name=corner_4]');"></td>
</tr>
<tr><td colspan="2">3角以下コースは右詰め</td></tr>
<tr>
    <th>単勝人気</th>
    <td class="in_input">
        <select name="favourite_select" style="width:7em;" onchange="clearElmVal('*[name=favourite]');">
        <?php
            echo '<option value=""></option>'."\n";
            $selected_option_exists=false;
            for($i=1; $i<=18; $i++){
                if($i==$form_data->favourite){ $selected_option_exists=true; }
                echo '<option value="'.$i,'"'.(($i==$form_data->favourite)?' selected ':'').'>';
                echo $i."番人気";
                echo '</option>'."\n";
            }
        ?></select>／
        <input type="number" name="favourite" style="width:3em;" onchange="clearElmVal('*[name=favourite_select]');" value="<?=h($selected_option_exists?'':HTPrint::ifZero2Empty($form_data->favourite))?>">
    </td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=favourite]');clearElmVal('*[name=favourite_select]');"></td>
</tr>
<!--<tr>
    <th>収得賞金</th>
    <td class="in_input"><input type="number" name="syuutoku" list="syuutoku_list" value="<?=h($form_data->syuutoku)?>">万円</td>
    <td></td>
</tr>-->
<tr><td colspan="3" style="height: 4px;"></td></tr>
<tr>
    <th>性別上書</th>
    <td>
    <?php
    $radio=new MkTagInputRadio("sex");
    $radio->value(0)->checkedIf($form_data->sex);
    ?>
    <label><?=$radio?>元の値</label>
    <?php if($horse->sex===2){ $radio->disabled(); } ?>
    <label><?=$radio->value(1)->checkedIf($form_data->sex)?>牡</label>
    <label><?=$radio->value(3)->checkedIf($form_data->sex)?>せん</label>
</tr>
<tr>
    <th>所属上書</th>
    <td class="in_input"><input type="text" name="tc" list="tc_list" value="<?=h($form_data->tc)?>" placeholder="このレース時点の所属"></td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=tc]');"></td>
</tr>
<tr>
    <th>調教師上書</th>
    <td class="in_input"><input type="text" name="trainer" value="<?=h($form_data->trainer)?>" placeholder="このレース時点の所属"></td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=trainer]');"></td>
</tr>
<tr>
    <th>調教国上書</th>
    <td class="in_input"><input type="text" name="training_country" value="<?=h($form_data->training_country)?>" placeholder="このレース時点の所属"></td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=training_country]');"></td>
</tr>
<tr>
    <th>地方所属</th>
    <td colspan="2">
    <label><input type="radio" name="is_affliationed_nar" value="0" <?=h(($form_data->is_affliationed_nar==0)?"checked":"")?>>いいえ</label>
    <label><input type="radio" name="is_affliationed_nar" value="1" <?=h(($form_data->is_affliationed_nar==1)?"checked":"")?>>はい（カク地）</label><br>
    <label><input type="radio" name="is_affliationed_nar" value="2" <?=h(($form_data->is_affliationed_nar==2)?"checked":"")?>>中央移籍後（マル地）</label>
    </td>
</tr>
<tr>
    <th>未登録の前走</th>
    <td class="in_input"><input type="number" name="non_registered_prev_race_number" style="width: 3em;" value="<?=h((int)$form_data->non_registered_prev_race_number)?>">走</td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=non_registered_prev_race_number]');"></td>
</tr>
<?php if($next_race_data!=null && $next_race_data->record_exists && $next_race_data->non_registered_prev_race_number>0): ?>
<tr>
    <th rowspan="3">次走から<br>未登録自動減算</th>
    <td><?=h($next_race_data->race_id)?><input type="hidden" name="next_race_id" value="<?=h($next_race_data->race_id)?>"></td>
    <td></td>
</tr>
<tr>
    <td><?=h($next_race->race_name)?>(<?=h($next_race->year)?>)</td>
    <td></td>
</tr>
<tr>
    <td>現在指定：<?=h($next_race_data->non_registered_prev_race_number)?>走</td>
    <td></td>
</tr>
<?php endif; ?>
</table>
<table class="edit-form-table floatLeft">
<tr>
    <th colspan="2">今週の注目レース</th>
</tr>
<tr>
    <th>(火)<br><input type="button" value="クリア" onclick="confirmAndClearElmVal('*[name=jra_thisweek_horse_1]','今週の注目レース（火曜日）');"></th>
    <td class="in_input">
        <textarea name="jra_thisweek_horse_1" style="width: 20em; min-height:5.5em;"><?=h($form_data->jra_thisweek_horse_1)?></textarea>
    </td>
</tr>
<tr>
    <th>(木)<br><input type="button" value="クリア" onclick="confirmAndClearElmVal('*[name=jra_thisweek_horse_2]','今週の注目レース（木曜日）');"></th>
    <td class="in_input">
        <textarea name="jra_thisweek_horse_2" style="width: 20em; min-height:5.5em;"><?=h($form_data->jra_thisweek_horse_2)?></textarea><br>
    </td>
</tr>
<tr>
    <th>並び順</th>
    <td class="in_input">
        <input type="number" name="jra_thisweek_horse_sort_number" style="width:6em;" value="<?=h($form_data->jra_thisweek_horse_sort_number)?>">
        <input type="button" value="クリア" onclick="clearElmVal('*[name=jra_thisweek_horse_sort_number]');">
    </td>
</tr>
<tr>
    <th colspan="3">スペシャル出馬表 紹介</th>
</tr>
<?php if(in_array($race->grade,['G1','Jpn1'])): ?>
<tr>
    <td class="in_input" colspan="2">
        <textarea name="jra_sps_comment" style="width: 95%;min-height:6em;"><?=h($form_data->jra_sps_comment)?></textarea><br>
        <input type="button" value="クリア" onclick="confirmAndClearElmVal('*[name=jra_sps_comment]','スペシャル出馬表紹介');">
    </td>
</tr>
<?php else: ?>
<tr>
    <td class="in_input" colspan="2">
        <textarea name="jra_sps_comment" style="width: 95%;min-height:6em;" disabled></textarea><br>
        <input type="button" value="クリア" onclick="clearElmVal('*[name=jra_sps_comment]');" disabled>
        <?php HTPrint::Hidden('jra_sps_comment',$form_data->jra_sps_comment) ?>
    </td>
</tr>
<?php endif; ?>
</table>
<?php
HTPrint::DataList('result_text_list',['中止','除外','取消','非当','非抽','回避']);
HTPrint::DataList('horse_number_list',[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18]);
HTPrint::DataList('handicap_list',
    ['58','57','56','55','54'],
    ['古馬GⅠ(牡)',
    '別定GⅡ(牡)、3歳戦牡',
    '秋古馬GⅠ(牝・3歳牡)、2歳戦牡',
    '別定GⅡ(牝)、2・3歳戦牝、夏2歳戦',
    '秋古馬GⅠ(3歳牝)、3歳GⅡ牝']);
HTPrint::DataList('syuutoku_list',
    ['400','500','600','900'],
    ['未勝','1勝クラス','2勝クラス','3勝クラス']);
HTPrint::DataList('tc_list',['美浦','栗東','地方','海外']);
HTPrint::DataList('margin_list',['ハナ','クビ','大差','同着']);
?>
<div style="clear: both;"><input type="submit" value="登録・編集　内容確認"></div>
</form>
<hr>
<script>
$(document).ready(function() {
    $('input[type="number"]').on('input', function() {
        const fullWidthDigits = /[０-９]/g;

        this.value = this.value.replace(fullWidthDigits, function(char) {
            return String.fromCharCode(char.charCodeAt(0) - 0xFEE0);
        });
    });
    $('input[data-disable-enter]').on('keydown', function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
        }
    });
    $('#random_imput_tgl_btn').on('click', function() {
        var $toggleBtn = $(this);
        var $isEnabled = $('#random_imput_tgl_is_enabled');
        var $uiRows = $('tr.random_input_ui');

        if ($isEnabled.val() === '0') {
            // 状態を有効に変更
            $isEnabled.val('1');
            $uiRows.show(); // display:none解除
            $toggleBtn.val('△　ランダム入力を閉じる');
        } else {
            // 状態を無効に変更
            $isEnabled.val('0');
            $uiRows.hide(); // display:none付与
            $toggleBtn.val('▽　ランダム入力を開く　');
        }
    });
});
function randomSet(){
    clearElmVal('*[name=result_number_select]');
    clearElmVal('*[name=result_number]');
    clearElmVal('*[name=result_order_select]');
    clearElmVal('*[name=result_order]');
    clearElmVal('*[name=favourite]');
    //$('input[name="result_number"]').val(randomNumber);
    $('select[name="frame_number"]').val(Math.floor(Math.random() * 8) + 1);
    $('input[name="corner_4"]').val(Math.floor(Math.random() * 18) + 1);

    var rand_dice_rn=$('input[name="rand_dice_rn"]').val();
    if(rand_dice_rn<=0){
        rand_dice_rn=1;
    }
    var max=18; var min=1;
    var result=Math.floor(Math.random() * max) + min;
    var dice_str_1= "着順 "+rand_dice_rn+"d"+max+"=["+result;
    if(rand_dice_rn>1){
        var tmp;
        for (let i = 1; i < rand_dice_rn; i++) {
            tmp=Math.floor(Math.random() * max) + min;
            dice_str_1=dice_str_1+","+tmp;
            if(result>tmp){ result=tmp; }
        }
    }
    dice_str_1+="]";
    $('select[name="result_number_select"]').val(result);

    var rand_dice_f=$('input[name="rand_dice_f"]').val();
    if(rand_dice_f<=0){
        rand_dice_f=1;
    }
    var result=Math.floor(Math.random() * max) + min;
    dice_str_1 +="、人気 "+rand_dice_f+"d"+max+"=["+result;
    if(rand_dice_f>1){
        var tmp;
        for (let i = 1; i < rand_dice_f; i++) {
            tmp=Math.floor(Math.random() * max) + min;
            dice_str_1=dice_str_1+","+tmp;
            if(result>tmp){ result=tmp; }
        }
    }
    dice_str_1+="]";
    $('select[name="favourite_select"]').val(result);
    $('input#dice_result').val(dice_str_1);
}
</script>
<?php if($is_edit_mode){ ?>
<form action="./delete/" method="post" style="text-align:right;">
<input type="hidden" name="race_id" value="<?=h($race_result_id)?>">
<input type="hidden" name="horse_id" value="<?=h($horse_id)?>">
<input type="submit" value="レース結果詳細データ削除確認" style="color:red;">
</form>
<?php } ?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>
