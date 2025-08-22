<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="システム設定";
$session=new Session();
// 暫定でログイン＝編集可能
$page->is_editable=SESSION::is_logined();

$pdo=getPDO();

$world_list=World::getAll($pdo);
$story_list=WorldStory::getAll($pdo);
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink('js/functions.js'); ?>
<style>
    th { background-color: #EEE;}
    select{
        height: 2em;
    }
    table.setting{ font-size: 0.9em; }
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php $page->printTitle(); ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php if($page->is_editable): ?>
<a href="<?php print $page->to_app_root_path ?>admin/world/list.php">[ワールド管理]</a>
<a href="<?php print $page->to_app_root_path ?>admin/world_story/list.php">[ストーリー設定管理]</a>
<a href="<?php print $page->to_app_root_path ?>admin/themes/list.php">[テーマ設定管理]</a>
<hr>
<?php endif; ?>
<form action="./execute.php" method="post" id="setting">
<table class="setting">
<tr>
    <th>選択した設定から読み込み</th>
    <td class="in_input" colspan="2"><select name="story_id" onchange="clearElmVal('*[name=save_story_id]');highlightIfNotEmpty('[name=save_story_id]');" style="width: 100%;">
    <option value="" selected>未選択</option>
    <?php
    if(count($story_list)>0){
        foreach($story_list as $row){
            echo "<option value=\"{$row['id']}\">{$row['id']}: {$row['name']}</option>";
        }
    }
    ?></select></td>
    <td class="in_input"><input type="button" value="設定変更" onclick="confirmAndSubmit();"></td>
</tr>
<tr><td colspan="4" style="text-align: right;">※ 読み込む場合、 設定に存在する項目は以下での変更より優先</td></tr>
<tr>
    <th>初期設定ファイルに書出し</th>
    <td>
        <label><input type="radio" name="save_to_file" value="0" checked>しない</label>
        <label><input type="radio" name="save_to_file" value="1"<?php echo Session::is_logined()?'':' disabled'; ?>>する</label>
    </td>
    <td colspan="2">初期設定を変更しログアウト後も適用</td>
</tr>
<tr>
    <th>選択した設定にも上書き</th>
    <td>
        <label><input type="radio" name="save_story_is_enabled" value="0" checked onclick="clearElmVal('*[name=save_story_id]');highlightIfNotEmpty('[name=save_story_id]');">しない</label>
        <label><input type="radio" name="save_story_is_enabled" value="1"<?php echo Session::is_logined()?'':' disabled'; ?>>する</label>
    </td>
    <td class="in_input"><select name="save_story_id" onchange="clearElmVal('*[name=story_id]');"<?php echo Session::is_logined()?'':' disabled'; ?>>
    <option value="" selected>未選択</option>
    <?php
    if(count($story_list)>0){
        foreach($story_list as $row){
            if($row['is_read_only']){continue;}
            echo "<option value=\"{$row['id']}\">{$row['id']}: {$row['name']}</option>";
        }
    }
    ?></select></td>
    <td></td>
</tr>
<tr><td colspan="4" style="text-align: right;">※ 右のチェックボックスにONがある場合、ONの項目だけをプリセット設定にする</td></tr>
<tr><td colspan="4" style="text-align: right;">
    上書き設定制御：
    <input type="button" value="オンオフ反転" onclick="save_check_tgl();"<?php echo Session::is_logined()?'':' disabled'; ?>>
    <input type="button" value="全てオフ（すべて保存対象）に戻す" onclick="save_check_tgl(false);"<?php echo Session::is_logined()?'':' disabled'; ?>></td></tr>
<tr>
    <th></th>
    <th>現在値</th>
    <th>設定</th>
    <th>上書対象</th>
</tr>
<tr>
    <th>ワールド</th>
    <td><?php
        if($setting->world_id>0){
            $world=new World();
            $world->getDataById($pdo,$setting->world_id);
            echo $world->name;
        }
    ?></td>
    <td class="in_input"><select name="world_id">
    <option value="">未選択</option>
    <?php
    if(count($world_list)>0){
        foreach($world_list as $row){
            $selected= $row['id']==$setting->world_id?" selected":"";
            echo "<option value=\"{$row['id']}\" {$selected}>{$row['id']}: {$row['name']}</option>";
        }
    }
    ?></select></td>
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[world_id]" value="1"></td>
</tr>
<tr><td colspan="4">&nbsp;</td></tr>
<?php $skin_list=Themes::getAll($pdo); ?>
<tr>
    <th>配色</th>
    <td><?php echo $setting->theme_dir_name; ?></td>
    <td class="in_input">
        <select name="theme_dir_name">
            <?php
foreach($skin_list as $row){
    $selected=($setting->theme_dir_name==$row['dir_name'])?' selected':'';
    echo "<option value=\"{$row['dir_name']}\"{$selected}>{$row['name']}</option>\n";
}
            ?>
        </select>
    </td>
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[theme_dir_name]" value="1"></td>
</tr>
<tr>
    <th>レース検索：主催初期値</th>
    <td colspan="2" style="text-align: right;" oncontextmenu="return false;">
        <?php
        $tag_h=new MkTagInput('hidden','','OFF');
        $tag_c=new MkTagInput('checkbox','',1);
        ?>
        <?=$tag_h->name('race_search_org_jra');?>
        <?=$tag_h->name('race_search_org_nar');?>
        <?=$tag_h->name('race_search_org_other');?>
        <label oncontextmenu="reset_and_checked('race_search_org_','jra');"><?=$tag_c->name('race_search_org_jra')->checked($setting->race_search_org_jra);?>中央　</label>
        <label oncontextmenu="reset_and_checked('race_search_org_','nar');"><?=$tag_c->name('race_search_org_nar')->checked($setting->race_search_org_nar);?>地方　</label>
        <label oncontextmenu="reset_and_checked('race_search_org_','other');"><?=$tag_c->name('race_search_org_other')->checked($setting->race_search_org_other);?>その他（海外）</label>
    </td>
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[race_search_org]" value="1"></td>
</tr>
<tr>
    <th>年度プルダウン等起点</th>
    <td><?php echo $setting->select_zero_year; ?></td>
    <td class="in_input"><input type="number" name="select_zero_year" placeholder="空でカウント起点使用" value="<?php echo $setting->select_zero_year; ?>"></td>
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[select_zero_year]" value="1"></td>
</tr>
<tr>
    <th>年度プルダウン</th>
    <td><?php
    echo ($setting->year_select_min_diff)."年前～";
    echo ($setting->year_select_max_diff)."年後";
    ?></td>
    <td class="in_input">
        －<input type="number" style="width: 3em;" name="year_select_min_diff" value="<?php echo $setting->year_select_min_diff; ?>">
        ～
        ＋<input type="number" style="width: 3em;" name="year_select_max_diff" value="<?php echo $setting->year_select_max_diff; ?>"></td>
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[year_select_min_max_diff]" value="1"></td>
</tr>
<tr><td colspan="3">&nbsp;</td></tr>
<tr>
    <th>年度表示モード</th>
    <td><?php echo Setting::YEAR_VIEW_MODE__LIST[$setting->year_view_mode]; ?></td>
    <td class="in_input"><select name="year_view_mode">
    <?php
    foreach(Setting::YEAR_VIEW_MODE__LIST as $key=>$val){
            $selected= $key==$setting->year_view_mode?" selected":"";
            echo "<option value=\"{$key}\" {$selected}>{$key}: {$val}</option>";
    }
    ?></select></td>
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[year_view_mode]" value="1"></td>
</tr>
<tr>
    <th>相対年数カウント起点の年</th>
    <td><?php echo $setting->zero_year; ?></td>
    <td class="in_input"><input type="number" name="zero_year" value="<?php echo $setting->zero_year; ?>"></td>
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[zero_year]" value="1"></td>
</tr>
<tr>
    <th>生年モード</th>
    <td><?php echo Setting::BIRTH_YEAR_MODE[$setting->birth_year_mode]; ?></td>
    <td class="in_input"><select name="birth_year_mode">
    <?php
    foreach(Setting::BIRTH_YEAR_MODE as $key=>$val){
            $selected= $key==$setting->birth_year_mode?" selected":"";
            echo "<option value=\"{$key}\" {$selected}>{$key}: {$val}</option>";
    }
    ?></select></td>
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[birth_year_mode]" value="1"></td>
</tr>
<tr>
    <th>馬齢モード</th>
    <td><?php echo Setting::AGE_VIEW_MODE__LIST[$setting->age_view_mode]; ?></td>
    <td class="in_input"><select name="age_view_mode">
    <?php
    foreach(Setting::AGE_VIEW_MODE__LIST as $key=>$val){
            $selected= $key==$setting->age_view_mode?" selected":"";
            echo "<option value=\"{$key}\" {$selected}>{$key}: {$val}</option>";
    }
    ?></select></td>
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[age_view_mode]" value="1"></td>
</tr>
<tr><td colspan="4">&nbsp;</td></tr>
<tr>
    <th rowspan="2">年の馬齢変換</th>
    <td><?php echo Setting::HORSE_RECORD_YEAR[$setting->horse_record_year]; ?></td>
    <td><?php
    foreach(Setting::HORSE_RECORD_YEAR as $key=>$val){
            print '<label>';
            HTPrint::Radio('horse_record_year',$key,$setting->horse_record_year);
            print "{$val}</label><br>";
    }
    ?></td>
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[horse_record_year]" value="1"></td>
</tr>
<tr><td colspan="3">馬齢表示は生年が一意に決まるページで適用<br>（競走馬個別記事、世代指定のレース検索）</td></tr>
<tr>
    <th>戦績の日付形式</th>
    <td><?php echo Setting::HORSE_RECORD_DATE[$setting->horse_record_date]; ?></td>
    <td><?php
    foreach(Setting::HORSE_RECORD_DATE as $key=>$val){
            print '<label>';
            HTPrint::Radio('horse_record_date',$key,$setting->horse_record_date);
            print "{$val}</label><br>";
    }
    ?></td>
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[horse_record_date]" value="1"></td>
</tr>
<!--<tr style="display: none;">
    <th>戦績の日付形式(日)</th>
    <td><?php echo Setting::HORSE_RECORD_DAY[$setting->horse_record_day]; ?></td>
    <td><?php
    foreach(Setting::HORSE_RECORD_DAY as $key=>$val){
            print '<label>';
            HTPrint::Radio('horse_record_day',$key,$setting->horse_record_day);
            print "{$val}</label><br>";
    }
    ?></td>
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[horse_record_day]" value="1"></td>
</tr>-->
<tr>
    <th>個別戦績のデフォルト並び順</th>
    <td><?php echo $setting->hors_history_sort_is_desc?"降順":"昇順"; ?></td>
    <td>
        <label><?php HTPrint::Radio('hors_history_sort_is_desc','0',$setting->hors_history_sort_is_desc) ?>昇順</label> ｜
        <label><?php HTPrint::Radio('hors_history_sort_is_desc','1',$setting->hors_history_sort_is_desc) ?>降順</label>
    </td>
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[hors_history_sort_is_desc]" value="1"></td>
</tr>
<tr><td colspan="4">&nbsp;</td></tr>
<tr>
    <th>出馬表の年</th>
    <td><?php echo $setting->syutsuba_year?'あり':'なし'; ?></td>
    <td>
        <label><?php HTPrint::Radio('syutsuba_year','1',$setting->syutsuba_year) ?>あり</label> ｜
        <label><?php HTPrint::Radio('syutsuba_year','0',$setting->syutsuba_year) ?>なし</label>
    </td>
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[syutsuba_year]" value="1"></td>
</tr>
<tr>
    <th>出馬表の日付形式</th>
    <td><?php echo Setting::SYUTSUBA_DATE[$setting->syutsuba_date]; ?></td>
    <td class="in_input"><select name="syutsuba_date">
    <?php
    foreach(Setting::SYUTSUBA_DATE as $key=>$val){
            $selected= $key==$setting->syutsuba_date?" selected":"";
            echo "<option value=\"{$key}\" {$selected}>{$key}: {$val}</option>";
    }
    ?></select></td>
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[syutsuba_date]" value="1"></td>
</tr>
<tr>
    <td colspan="4" class="in_input" style="text-align: right;"><input type="button" value="設定変更" onclick="confirmAndSubmit();"></td>
</tr>
</table>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
<script>
$(function() {
    $('[name="save_story_id"]').change(function(){
        highlightIfNotEmpty('[name="save_story_id"]');
    });
});
function highlightIfNotEmpty(selector){
    var tgt= $(selector);
    if(tgt.val()!=0){
        tgt.css('color','red');
        tgt.css('background-color','#FFFCCC');
    }else{
        tgt.css('color','');
        tgt.css('background-color','');
    }
}
function confirmAndSubmit() {
    var id = $('select[name="save_story_id"]').val();
    var radio = $('input[name="save_story_is_enabled"]:checked').val();
    if(id!=0){
        if(radio==0){
            alert("保存先の設定が選択されていますが、\n選択した設定に上書き「しない」モードになっています。");
            return;
        }else{
            var result = confirm("設定を変更し、選択した設定を上書きしますか？");
            if(!result){ return; }
        }
    }
    document.getElementById("setting").submit();
}
function save_check_tgl(mode=''){
  var tgt=$('input[type="checkbox"][name^="save_target["]');
  if(mode===true){
      tgt.each(function () { $(this).prop('checked', true); });
  }else if(mode===false){
      tgt.each(function () { $(this).prop('checked', false); });
  }else{
      tgt.each(function () { $(this).prop('checked', !$(this).prop('checked')); });
  }
}
</script>
<?php $page->printScriptLink('js/race_search_form.js'); ?>
</body>
</html>
