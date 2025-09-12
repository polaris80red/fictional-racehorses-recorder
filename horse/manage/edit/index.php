<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
defineAppRootRelPath(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="競走馬登録";
$page->ForceNoindex();
$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$horse_id=!empty($_GET['horse_id'])?$_GET['horse_id']:"";

$is_edit_mode=filter_input(INPUT_GET,'edit_mode')?1:0;
# 対象取得
$pdo= getPDO();
// 既存データ取得
$horse= new Horse();
$horse->setDataById($pdo,$horse_id);
if(!$horse->record_exists){
    $is_edit_mode=0;
    $horse->world_id=$setting->world_id;
}else{
    $is_edit_mode=1;
}
$world_list=World::getAll($pdo);

?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?>（<?=$is_edit_mode?"編集":"新規"?>）</title>
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
<h1 class="page_title"><?php $page->printTitle(); ?>（<?=$is_edit_mode?"編集":"新規"?>）</h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<form action="./confirm.php" method="post">
<input type="hidden" name="edit_mode" value="<?=$is_edit_mode?1:0;?>">
<table class="edit-form-table floatLeft" style="margin-right: 4px;">
<tr>
    <th>競走馬ID</th>
    <?php if($is_edit_mode): ?>
    <td><?php HTPrint::HiddenAndText('horse_id',$horse_id); ?></td>
    <?php else: ?>
    <td class="in_input"><input type="input" name="horse_id" value="<?=h($horse_id)?>" onchange="checkHorseIdExists()" placeholder="未入力で自動割当て"></td>
    <?php endif; ?>
</tr>
<tr>
    <th>ワールド</th>
    <td class="in_input"><select name="world_id" class="required" required>
    <option value="">未選択</option>
    <?php
    if(count($world_list)>0){
        foreach($world_list as $row){
            $selected= $row['id']==$horse->world_id?" selected":"";
            echo "<option value=\"{$row['id']}\" {$selected}>{$row['id']}: ".h($row['name'])."</option>";
        }
    }
    ?></select></td>
</tr>
<tr>
    <th>馬名</th>
    <td class="in_input"><input type="text" name="name_ja" value="<?=h($horse->name_ja)?>"></td>
</tr>
<tr>
    <th>馬名（欧字）</th>
    <td class="in_input"><input type="text" name="name_en" value="<?=h($horse->name_en)?>" onchange="convertHankaku('input[name=name_en]');"></td>
</tr>
<tr>
    <th><?php
            if($setting->birth_year_mode==1||$setting->birth_year_mode==2){
                if($setting->year_view_mode===3){
                    echo "期";
                }else{
                    echo "世代";
                }
            }else{
                echo "生年";
            }
    ?></th>
    <td class="in_input">
        <select name="birth_year_select" style="width:6em;" onchange="clearElmVal('*[name=birth_year]');">
        <?php
            $year_min=$setting->select_zero_year - $setting->year_select_min_diff - 3;
            $year_max=$setting->select_zero_year + $setting->year_select_max_diff;
            echo '<option value=""></option>'."\n";
            $year_option_exists=false;
            for($i=$year_min; $i<=$year_max; $i++){
                if($i==$horse->birth_year){ $year_option_exists=true; }
                echo '<option value="'.$i,'"'.(($i==$horse->birth_year)?' selected ':'').'>';
                print_h($setting->getBirthYearFormat($i));
                echo '</option>'."\n";
            }
        ?></select>
        ／ <input type="number" name="birth_year" style="width:4em;" value="<?=h($year_option_exists?'':$horse->birth_year)?>" placeholder="生年" onchange="clearElmVal('*[name=birth_year_select]');">
    </td>
</tr>
<tr>
    <th>性別</th>
    <td>
    <label><?php HTPrint::Radio("sex",0,$horse->sex);?>未選択</label><br>
    <label><?php HTPrint::Radio("sex",1,$horse->sex);?>牡</label>
    <label><?php HTPrint::Radio("sex",2,$horse->sex);?>牝</label>
    <label><?php HTPrint::Radio("sex",3,$horse->sex);?>セン</label></td>
</tr>
<tr>
    <th>毛色</th>
    <td class="in_input">
        <select name="color_select" style="width:5em;" onchange="clearElmVal('*[name=color]');">
<?php
    $color_list=['鹿毛','黒鹿毛','青鹿毛','栗毛','芦毛','白毛'];
    echo '<option value=""></option>'."\n";
    $target_exists=false;
    foreach($color_list as $val){
        if($val==$horse->color){
            $target_exists=true;
            $selected_or_empty=' selected ';
        }else{
            $selected_or_empty='';
        }
        echo '<option value="'.$val,'"'.$selected_or_empty.'>';
        echo $val;
        echo '</option>'."\n";
    }
?></select>／
        <input type="text" name="color" style="width: 6em;" value="<?=h($target_exists?'':$horse->color)?>" placeholder="毛色手入力" onchange="clearElmVal('*[name=color_select]');">
    </td>
</tr>
<?php
$affiliation_list=Affiliation::getForSelectbox($pdo);
$affiliation_name_list=[];
if(count($affiliation_list)>0){
    foreach($affiliation_list as $row){
        $affiliation_name_list[]=$row['name'];
    }
}
?>
<tr>
    <th>所属</th>
    <td class="in_input">
        <select name="tc_select" style="width:5em;" onchange="clearElmVal('*[name=tc]');">
<?php
    echo '<option value=""></option>'."\n";
    $target_exists=false;
    foreach($affiliation_name_list as $val){
        echo '<option value="'.$val,'"'.(($val==$horse->tc)?' selected ':'').'>';
        if($val==$horse->tc){$target_exists=true;}
        echo $val;
        echo '</option>'."\n";
    }
?></select>／
        <input type="text" name="tc" style="width: 6em;" value="<?=h($target_exists?'':$horse->tc)?>" placeholder="所属手入力" onchange="clearElmVal('*[name=tc_select]');">
    </td>
</tr>
<tr>
    <th>調教師</th>
    <td class="in_input"><input type="text" name="trainer_unique_name" value="<?=h($horse->trainer_unique_name)?>"></td>
</tr>
<tr>
    <th>調教国</th>
    <td class="in_input"><input type="text" name="training_country" value="<?=h($horse->training_country)?>"></td>
</tr>
<tr>
    <th>馬主</th>
    <td class="in_input"><input type="text" name="owner_name" value="<?=h($horse->owner_name)?>" placeholder="馬主"></td>
</tr>
<tr>
    <th>生産者</th>
    <td class="in_input"><input type="text" name="breeder_name" value="<?=h($horse->breeder_name)?>" placeholder="生産者"></td>
</tr>
<tr>
    <th>生産国</th>
    <td class="in_input"><input type="text" name="breeding_country" value="<?=h($horse->breeding_country)?>"></td>
</tr>
<tr>
    <th>地方所属馬</th>
    <td>
    <label><?php HTPrint::Radio("is_affliationed_nar",0,$horse->is_affliationed_nar);?>いいえ</label>
    <label><?php HTPrint::Radio("is_affliationed_nar",1,$horse->is_affliationed_nar);?>はい</label>
    </td>
</tr>
<tr>
    <th>父ID</th>
    <td class="in_input"><input type="text" name="sire_id" value="<?=h($horse->sire_id)?>" onchange="convertHankaku('input[name=sire_id]');"></td>
</tr>
<tr>
    <th>父名</th>
    <td class="in_input"><input type="text" name="sire_name" placeholder="父ID該当時は上書き" value="<?=h($horse->sire_name)?>"></td>
</tr>
<tr>
    <th>母ID</th>
    <td class="in_input"><input type="text" name="mare_id" value="<?=h($horse->mare_id)?>" onchange="convertHankaku('input[name=mare_id]');"></td>
</tr>
<tr>
    <th>母名</th>
    <td class="in_input"><input type="text" name="mare_name" placeholder="母ID該当時は上書き" value="<?=h($horse->mare_name)?>"></td>
</tr>
<tr>
    <th>母の父</th>
    <td class="in_input"><input type="text" name="bms_name" placeholder="母ID該当時は上書き" value="<?=h($horse->bms_name)?>"></td>
</tr>
<tr>
    <th>種牡馬<br>または<br>繫殖馬</th>
    <td>
    <label><?php HTPrint::Radio("is_sire_or_dam",1,$horse->is_sire_or_dam);?>はい</label><br>
    <label><?php HTPrint::Radio("is_sire_or_dam",0,$horse->is_sire_or_dam);?>いいえ</label>
    </td>
</tr>
<tr>
    <th>馬名意味</th>
    <td class="in_input"><input type="text" name="meaning" value="<?=h($horse->meaning)?>"></td>
</tr>
<tr>
    <th>備考</th>
    <td class="in_input"><textarea name="note" style="width: 95%; height: 5em;"><?=h($horse->note)?></textarea></td>
</tr>
<!--<tr>
    <th>論理削除</th>
    <td>
    <label><?php HTPrint::Radio("is_enabled",1,$horse->is_enabled);?>有効</label>
    <label><?php HTPrint::Radio("is_enabled",0,$horse->is_enabled);?>削除</label>
</tr>-->
</table>
<table class="edit-form-table floatLeft">
<tr>
    <th>プロフィール</th>
<tr>
</tr>
    <td class="in_input"><textarea name="profile" style="width: 20em; height: 10em;"><?=h($horse->profile)?></textarea></td>
</tr>
<?php $horse_tags=(new HorseTag($pdo))->getTagNames($horse->horse_id); ?>
<tr>
    <th >検索タグ<br><span class="small">(改行や空白区切り)</span></th>
<tr>
</tr>
    <td class="in_input">
        <textarea name="horse_tags" style="width: 20em; height: 8em;"><?=h(implode("\n",$horse_tags))?></textarea>
    </td>
</tr>
</table>
<?php HTPrint::Hidden("is_enabled",$horse->is_enabled); ?>
<hr style="clear: both;">
<input type="submit" value="競走馬データ登録内容確認">
</form>
<?php if($is_edit_mode){ ?>
<hr>
<form action="../delete/" method="post" style="text-align:right;">
<input type="hidden" name="horse_id" value="<?=h($horse_id)?>">
<input type="submit" value="競走馬データ削除確認" style="color:red;">
</form>
<?php } ?>
<script>
$(function() {
  $('input[type="number"]').on('input', function() {
    const fullWidthDigits = /[０-９]/g;

    this.value = this.value.replace(fullWidthDigits, function(char) {
      return String.fromCharCode(char.charCodeAt(0) - 0xFEE0);
    });
  });
});
function checkHorseIdExists() {
  const horseId = convertHankaku('input[name="horse_id"]');
  if (horseId !== '') {
    $.ajax({
      url: '<?php echo $page->to_app_root_path; ?>api/checkHorseIdExists.php',
      method: 'GET',
      data: { horse_id: horseId },
      dataType: 'text',
      success: function(response) {
        if (response.trim() === 'true') {
          alert('該当の競走馬IDは既に存在します');
        }
      },
      error: function(xhr, status, error) {
        console.error('通信エラー:', error);
      }
    });
  }
}
</script>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>
