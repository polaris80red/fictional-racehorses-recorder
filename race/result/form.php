<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース結果情報登録";
$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$race_id=(string)filter_input(INPUT_GET,'race_id');
$is_edit_mode=filter_input(INPUT_GET,'edit_mode')?1:0;
$race= new RaceResults();   
$pdo= getPDO();
if($race_id===''){
    $is_edit_mode=0;
    $race->world_id=$setting->world_id;
} else {
    # 対象取得
    $race->setDataById($pdo,$race_id);
}
if($is_edit_mode==0 && $race_id==''){
    // 引き継ぎ新規の場合
    $race->date=(string)filter_input(INPUT_GET,'date');
    $race->year=(string)filter_input(INPUT_GET,'year');
    $race->month=(string)filter_input(INPUT_GET,'month');
    $race->race_course_name=(string)filter_input(INPUT_GET,'race_course_name');
}
#echo '<pre>'.print_r($race,true).'</pre>'; exit;

$world_list=World::getAll($pdo);
$sex_category_list=RaceCategorySex::getAll($pdo);
$age_category_list=RaceCategoryAge::getAll($pdo);
$race_course_list_results=RaceCourse::getAll($pdo);

$race_course_list=[];
if(count($race_course_list_results)>0){
    foreach($race_course_list_results as $row){
        $race_course_list[]=$row['short_name'];
    }
}
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?>（<?php echo $is_edit_mode?"編集":"新規" ?>）</title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink('js/functions.js'); ?>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php $page->printTitle(); ?>（<?php echo $is_edit_mode?"編集":"新規" ?>）</h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<form action="registration_confirm.php" method="post">
<input type="hidden" name="edit_mode" value="<?php echo $is_edit_mode?1:0; ?>">

<table class="edit-form-table">
<tr>
    <th>レースID</th>
<?php
if($is_edit_mode){
    echo "<td>";
    printHiddenAndText('race_id',$race_id);
    echo "</td><td></td>";
}else{
?><td class="in_input">
    <input type="text" name="race_id" value="<?php echo $race_id;?>" onchange="checkRaceIdExists();" placeholder="未入力で自動割当て">
</td><td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=race_id]');"></td>
<?php
}
?>
</tr>
<tr id="duplicate_id"<?php if($is_edit_mode){ echo 'style="display:none;"'; } ?>>
    <th>ID重複確認</th>
    <td><a id="duplicate_id_link" href="" target="_blank"></a></td>
    <td></td>
</tr>
<tr>
    <th>ワールド</th>
    <td class="in_input"><select name="world_id" class="required" required>
    <option value="">未選択</option>
    <?php
    if(count($world_list)>0){
        foreach($world_list as $row){
            $selected= $row['id']==$race->world_id?" selected":"";
            echo "<option value=\"{$row['id']}\" {$selected}>{$row['id']}: {$row['name']}</option>";
        }
    }
    ?></select></td>
    <td></td>
</tr>
<tr>
    <th>競馬場</th>
    <td class="in_input">
<select name="race_course_name_select" style="width:5em;height:2em;" onchange="clearElmVal('*[name=race_course_name]');">
<?php
    echo '<option value=""></option>'."\n";
    $target_exists=false;
    foreach($race_course_list as $race_course_name){
        echo '<option value="'.$race_course_name,'"'.(($race_course_name==$race->race_course_name)?' selected ':'').'>';
        if($race_course_name==$race->race_course_name){$target_exists=true;}
        echo $race_course_name;
        echo '</option>'."\n";
    }
?></select>／
        <input type="text" name="race_course_name" style="width:4em;" value="<?php echo $target_exists?"":$race->race_course_name; ?>" placeholder="競馬場" onchange="clearElmVal('*[name=race_course_name_select]');">
        <input type="number" name="race_number" style="width:3em;" value="<?php echo ($race->race_number?:""); ?>" placeholder="R">R
    </td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=race_course_name]');clearElmVal('*[name=race_course_name_select]');clearElmVal('*[name=race_number]');"></td>
</tr>
<tr>
    <th>距離</th>
    <td><?php
foreach(['芝','ダ','障'] as $row){
    $checked= $row==$race->course_type?" checked":"";
    echo "<label><input name=\"course_type\" type=\"radio\" value=\"{$row}\" {$checked} required>{$row}</label>\n";
}
    ?>　<input type="number" name="distance" class="required" list="distance_list" style="width:6em;" value="<?php echo $race->distance; ?>" required>m
    </td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=distance]');"></td>
</tr>
<tr>
    <th>レース名</th>
    <td class="in_input"><input type="text" name="race_name" class="required" list="race_name_list" value="<?php echo $race->race_name; ?>" required></td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=race_name]');clearElmVal('*[name=race_short_name]');"></td>
</tr>
<tr>
    <th>出馬表等略名</th>
    <td class="in_input"><input type="text" name="race_short_name" list="race_short_name_list" value="<?php echo $race->race_short_name; ?>"></td>
    <td class=""></td>
</tr>
<tr>
    <th>キャプション</th>
    <td class="in_input"><input type="text" name="caption" value="<?php echo $race->caption; ?>" placeholder="一覧等オンマウス時補足"></td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=caption]');"></td>
</tr>
<tr>
    <th>グレード</th>
    <td class="in_input">
        <select name="grade_select" style="width:5em;height:2em;" onchange="clearElmVal('*[name=grade]');">
<?php
    $grade_list=['G1','G2','G3','重賞','L','OP','3勝','2勝','1勝','未勝','新馬'];
    echo '<option value=""></option>'."\n";
    $target_exists=false;
    foreach($grade_list as $grade){
        echo '<option value="'.$grade,'"'.(($grade==$race->grade)?' selected ':'').'>';
        if($grade==$race->grade){$target_exists=true;}
        echo $grade;
        echo '</option>'."\n";
    }
?></select>／
        <input type="text" name="grade" style="width:4em;" value="<?php echo $target_exists?"":$race->grade; ?>" placeholder="手入力" onchange="clearElmVal('*[name=grade_select]');"></td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=grade_select]');clearElmVal('*[name=grade]');"></td>
</tr>
<tr>
    <th>馬齢条件</th>
    <td class="in_input"><select name="age_category_id">
    <option value="">未登録</option>
    <?php
    if(count($age_category_list)>0){
        foreach($age_category_list as $row){
            $selected= $row['id']==$race->age_category_id?" selected":"";
            echo "<option value=\"{$row['id']}\" {$selected}>{$row['id']}: {$row['name']}</option>";
        }
    }
    ?></select><input type="button" id="agegrade_to_name" value="馬齢・条件から命名"></td>
    <td></td>
</tr>
<tr>
    <th>馬齢（手入力）</th>
    <td class="in_input"><input type="text" name="age" style="width: 18em;" value="<?php echo $race->age; ?>" placeholder="南半球産併記等用(選択式より優先表示)"></td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=age]');"></td>
</tr>
<tr style="display:none;">
    <td colspan="3" title="2歳・3歳新馬・未勝利・1勝クラスおよび3上と4上の1勝・2勝クラス">レース名空欄か略名が新馬・未勝利・1/2勝クラスの場合、<br>該当馬齢とグレード選択で上書き</td>
</tr>
<tr>
    <th>性別条件</th>
    <td class="in_input"><select name="sex_category_id">
    <option value="">未登録</option>
    <?php
    if(count($sex_category_list)>0){
        foreach($sex_category_list as $row){
            $selected= $row['id']==$race->sex_category_id?" selected":"";
            echo "<option value=\"{$row['id']}\" {$selected}>{$row['id']}: {$row['name']}</option>";
        }
    }
    ?></select></td>
    <td></td>
</tr>
<tr>
    <th>馬場</th>
    <td class="in_input">
        <select name="track_condition_select" style="width:5em;height:2em;" onchange="clearElmVal('*[name=track_condition]');">
<?php
    $track_condition_list=['良','稍重','重','不良'];
    echo '<option value=""></option>'."\n";
    $target_exists=false;
    foreach($track_condition_list as $track_condition){
        echo '<option value="'.$track_condition,'"'.(($track_condition==$race->track_condition)?' selected ':'').'>';
        if($track_condition==$race->track_condition){$target_exists=true;}
        echo $track_condition;
        echo '</option>'."\n";
    }
?></select>／
        <input type="text" name="track_condition" style="width:4em;" value="<?php echo $target_exists?"":$race->track_condition; ?>" placeholder="手入力" onchange="clearElmVal('*[name=track_condition_select]');"></td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=track_condition]');clearElmVal('*[name=track_condition_select]');"></td>
</tr>
<tr>
    <th>頭数</th>
    <td class="in_input"><input type="number" name="number_of_starters" value="<?php echo $race->number_of_starters; ?>"></td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=number_of_starters]');"></td>
</tr>
<tr>
    <th>JRA</th>
    <td>
    <label><input type="radio" name="is_jra" value="0" <?php echo ($race->is_jra==0)?"checked":""; ?>>いいえ</label>
    <label><input type="radio" name="is_jra" value="1" <?php echo ($race->is_jra==1)?"checked":""; ?>>はい</label>
    </td>
    <td></td>
</tr>
<tr>
    <th>地方</th>
    <td>
    <label><input type="radio" name="is_nar" value="0" <?php echo ($race->is_nar==0)?"checked":""; ?>>いいえ</label>
    <label><input type="radio" name="is_nar" value="1" <?php echo ($race->is_nar==1)?"checked":""; ?>>はい</label>
    </td>
    <td></td>
</tr>
<tr>
    <th>正規日付</th>
    <td class="in_input"><input type="text" id="race_date_picker" name="date" value="<?php echo $race->date; ?>" onchange="setYM();setWeekSelect();getWeekNum();"></td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=date]');"></td>
</tr>
<tr>
    <th>仮の日付</th>
    <td>
    <label><input type="radio" name="is_tmp_date" value="0" <?php echo ($race->is_tmp_date==0)?"checked":""; ?>>いいえ</label>
    <label><input type="radio" name="is_tmp_date" value="1" <?php echo ($race->is_tmp_date==1)?"checked":""; ?>>はい（一覧等で日を省略）</label>
    </td>
    <td></td>
</tr>
<tr>
    <th>年月</th>
    <td class="in_input">
        <input type="number" name="year" class="required" style="width:5em;" value="<?php echo $race->year; ?>" required>年
        <select name="month" class="required" onchange="setWeekSelect();" required>
    <option value="">未選択</option>
    <?php
    for($i=1;$i<=12;$i++){
        $selected= $i==$race->month?" selected":"";
        echo "<option value=\"{$i}\" {$selected}>{$i}月</option>\n";
    }
    ?></select>月
    </td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=year]');clearElmVal('*[name=month]');"></td>
</tr>
<tr><?php $weeks=RaceWeek::getAll($pdo); ?>
    <th>週</th>
    <td>
    <?php
    echo "<label>";
    HTPrint::Radio('week_id','',$race->week_id);
    echo "未選択</label><br>\n";
    foreach($weeks as $row){
        $class="race_week race_week_m{$row['month']} race_week_id{$row['id']}";
        if($row['month_grouping']%10===0){
            $class.=" race_week_m".((int)$row['month']-1);
        }
        if($row['month_grouping']%10>=5){
            $class.=" race_week_m".((int)$row['month']+1);
        }
        $style='';
        if($race->month!==$row['month'] && $race->week_id!=$row['id']){
            $style="display:none;";
        }
        echo "<label class=\"{$class}\" style=\"{$style}\">";
        HTPrint::Radio('week_id',$row['id'],$race->week_id);
        echo str_pad($row['id'],2,'0',STR_PAD_LEFT)."週（".str_pad($row['month'],2,'0',STR_PAD_LEFT)."月）".h($row['name'])."\n";
        echo "<br></label>\n";
    }
    ?></td>
    <td></td>
</tr>
<tr>
    <th>備考</th>
    <td class="in_input">
        <textarea name="note" style="width:10rm;height:4em;"><?php echo $race->note; ?></textarea>
    </td>
    <td class="in_input"><input type="button" value="クリア" onclick="clearElmVal('*[name=note]');"></td>
</tr>
<!--
<tr>
    <th>表示順補正</th>
    <td class="in_input"><input type="number" name="sort_number" value="<?php echo $race->sort_number; ?>"></td>
    <td></td>
</tr>
-->
<tr style="<?php echo ($is_edit_mode || $race->is_enabled==0)?'':'display:none;'; ?>">
    <th>表示<br>（論理削除）</th>
    <td>
    <label><?php HTPrint::Radio("is_enabled",1,$race->is_enabled); ?>表示する</label><br>
    <label><?php HTPrint::Radio("is_enabled",0,$race->is_enabled); ?>非表示</label>
    </td>
    <td></td>
</tr>
</table>
<?php if(!$is_edit_mode && $race->is_enabled==1){HTPrint::Hidden('is_enabled',$race->is_enabled);} ?>
<?php HTPrint::Hidden('sort_number',$race->sort_number); ?>
<hr>
<input type="submit" value="レース結果データ登録内容確認">
</form>
<?php
HTPrint::DataList('race_name_list',[
    '2歳未勝利','2歳新馬',
    '3歳未勝利','3歳新馬','3歳1勝クラス','3歳以上1勝クラス','4歳以上1勝クラス','3歳以上2勝クラス','4歳以上2勝クラス'
    ]);
HTPrint::DataList('race_short_name_list',[
    '未勝利','新馬','1勝クラス','2勝クラス'
    ]);
HTPrint::DataList('distance_list',[
    '1000','1200','1400','1600','1800','2000','2200','2400','2600','3000','3200','3600','1500','1150'
    ]);
?>
<?php if($is_edit_mode){ ?>
<form action="./delete/" method="post" style="text-align:right;">
<input type="hidden" name="race_id" value="<?php echo $race_id; ?>">
<input type="submit" value="レース結果データ削除確認" style="color:red;">
</form>
<?php } ?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
<script>
$("#race_date_picker").datepicker({
    changeYear:true,
    showButtonPanel:true,
    showOn:'button',
    dateFormat:'yy-mm-dd',
    firstDay:1,
    showOtherMonths:true,
    selectOtherMonths:true
});
$(function() {
  $('input[type="number"]').on('input', function() {
    const fullWidthDigits = /[０-９]/g;

    this.value = this.value.replace(fullWidthDigits, function(char) {
      return String.fromCharCode(char.charCodeAt(0) - 0xFEE0);
    });
  });
});
function setYM(){
    var date_str=$("*[name=date]").val();
    if(date_str!=''){
        var date_obj=new Date(date_str);
        var year = Number(date_obj.getFullYear());
        var month = (Number(date_obj.getMonth()) + 1);
        setElmVal("*[name=year]",year);
        setElmVal("*[name=month]",month);
    }
}
function checkRaceIdExists() {
  const raceId = convertHankaku('input[name="race_id"]');
  if (raceId !== '') {
    $.ajax({
      url: '<?php echo $page->to_app_root_path; ?>api/checkRaceIdExists.php',
      method: 'GET',
      data: { race_id: raceId },
      dataType: 'text',
      success: function(response) {
        if (response.trim() === 'true') {
          const href_pref ='<?php echo $page->to_app_root_path; ?>race/result.php?race_id=';
          $('#duplicate_id_link').attr('href', href_pref+raceId);
          $('#duplicate_id_link').text('ID: '+raceId+' は存在します');
        }else{
          $('#duplicate_id_link').attr('href', '');
          $('#duplicate_id_link').text('');
        }
      },
      error: function(xhr, status, error) {
        console.error('通信エラー:', error);
      }
    });
  }
}
function getWeekNum() {
  const date = $('input[name="date"]').val().trim();
  if (date !== '') {
    $.ajax({
      url: '<?php echo $page->to_app_root_path; ?>api/getWeekByDate.php',
      method: 'GET',
      data: { date: date },
      dataType: 'text',
      success: function(response) {
        $('label.race_week input').prop('checked', false);
        $('label.race_week_id' + response.trim()+' input').prop('checked', true);
      },
      error: function(xhr, status, error) {
        console.error('通信エラー:', error);
      }
    });
  }
}
function setWeekSelect(){
    var month=$("*[name=month]").val();
    if (!month || month === "0") {
        return;
    }
    $('label.race_week').css('display', 'none');
    $('label.race_week_m' + month).css('display', '');
}
//setWeekSelect();
$(document).ready(function() {
    // 判定用配列（キーは文字列）
    const grade = {
        "新馬": "新馬",
        "未勝": "未勝利",
        "1勝": "1勝クラス",
        "2勝": "2勝クラス"
    };

    const age = {
        "20": "2歳",
        "30": "3歳",
        "31": "3歳以上",
        "41": "4歳以上"
    };

    // age → grade の二次元配列
    const age_grade = {
        "20": { "新馬": "2歳新馬", "未勝": "2歳未勝利", "1勝": "2歳1勝クラス" },
        "30": { "新馬": "3歳新馬", "未勝": "3歳未勝利", "1勝": "3歳1勝クラス" },
        "31": { "1勝": "3歳以上1勝クラス", "2勝": "3歳以上2勝クラス" },
        "41": { "1勝": "4歳以上1勝クラス", "2勝": "4歳以上2勝クラス" }
    };

    // ボタンの有効/無効を切り替える関数
    function updateButtonState() {
        const gradeVal = $("[name='grade_select']").val();
        const ageVal   = $("[name='age_category_id']").val();

        const matchExists = age_grade.hasOwnProperty(ageVal) &&
                            age_grade[ageVal].hasOwnProperty(gradeVal);

        $("#agegrade_to_name").prop("disabled", !matchExists);
    }

    // 初期化時にチェック
    updateButtonState();

    // age_category_id または grade_select の変更時にチェック
    $("[name='grade_select'], [name='age_category_id']").on("change", updateButtonState);

    // ボタンクリック時の処理
    $("#agegrade_to_name").on("click", function () {
        const gradeVal       = $("[name='grade_select']").val();
        const ageVal         = $("[name='age_category_id']").val();
        const raceName       = $("[name='race_name']").val().trim();
        const raceShortName  = $("[name='race_short_name']").val().trim();

        const matchExists = age_grade.hasOwnProperty(ageVal) &&
                            age_grade[ageVal].hasOwnProperty(gradeVal);

        if (!matchExists) {
            return; // 念のため安全対策
        }

        const isShortNameInGrade = Object.values(grade).includes(raceShortName);
        const conditionMatch = (raceName === "" || isShortNameInGrade);

        if (conditionMatch) {
            $("[name='race_name']").val(age_grade[ageVal][gradeVal]);
            $("[name='race_short_name']").val(grade[gradeVal]);
        } else {
            if (confirm("レース名・略名を上書きしますか？")) {
                $("[name='race_name']").val(age_grade[ageVal][gradeVal]);
                $("[name='race_short_name']").val(grade[gradeVal]);
            }
        }
        checkRequiredElm("[name='race_name']");
    });
});
</script>
</body>
</html>
