<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="ストーリー設定登録";
$page->ForceNoindex();

$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }
if(!Session::currentUser()->canManageSystemSettings()){
    header("HTTP/1.1 403 Forbidden");
    $page->addErrorMsg('システム設定管理権限がありません');
}

$pdo=getPDO();
$inputId=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
$input_world_id=filter_input(INPUT_GET,'world_id',FILTER_VALIDATE_INT);
$s_setting=new Setting(false);

$editMode=($inputId>0);
$TableClass=WorldStory::class;
$TableRowClass=$TableClass::ROW_CLASS;

if($editMode){
    $page->title.="（編集）";
    $story=($TableClass)::getById($pdo,$inputId);
    if($story===false){
        $page->addErrorMsg("ID '{$inputId}' が指定されていますが該当する設定がありません");
    }
}else{
    $story=new ($TableRowClass)();
}
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
$story_setting=$story->getDecodedConfig();
// 現在値の設定インスタンスにストーリー設定の保存値を反映する
$setting->setByStdClass($story_setting);
$world=World::getById($pdo,$setting->world_id);

// 新規登録かつワールド設定がある（ワールド設定からの遷移）の場合、ワールドを再設定してnameにも反映する。
if(!$editMode && $input_world_id>0){
    $world=World::getById($pdo,$input_world_id);
    $setting->world_id=$input_world_id;
    $story->name=$world->name;
}
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle();  ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink('js/functions.js'); ?>
<style>
    select{
        height: 2em;
    }
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php $page->printTitle(); ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<a href="./list.php">一覧に戻る</a>
<form method="post" action="./registration_confirm.php">
<table class="edit-form-table">
<tr>
    <th>ID</th>
    <td><?php
        print_h($story->id?:"新規登録");
        HTPrint::Hidden('id',$story->id);
    ?></td>
</tr>
<tr>
    <th>名称</th>
    <td class="in_input"><input type="text" name="name" class="required" value="<?=h($story->name)?>" required></td>
</tr>
<tr>
    <th>非ログイン時<br>設定画面</th>
    <td>
        <label><?php HTPrint::Radio('guest_visible',1,$story->guest_visible); ?>選択肢に表示する</label><br>
        <label><?php HTPrint::Radio('guest_visible',0,$story->guest_visible); ?>選択肢に表示しない</label>
    </td>
</tr>
<tr>
    <th>表示順優先度</th>
    <td class="in_input"><input type="number" name="sort_priority" value="<?=h($story->sort_priority)?>"></td>
</tr>
<tr>
    <th>表示順(同優先度内で昇順)</th>
    <td class="in_input"><input type="number" name="sort_number" value="<?=h($story->sort_number)?>" placeholder="同優先度内で昇順"></td>
</tr>
<tr>
    <th>読取専用</th>
    <td><?php $radio=new MkTagInputRadio('is_read_only',0,$story->is_read_only); ?>
        <label><?php print($radio); ?>いいえ</label><br>
        <label><?php
        $radio->value(1)->checkedIf($story->is_read_only)
        ->disabled($story->id>0?false:true)->print();
        ?>はい（上書き候補から隠す）</label>
    </td>
</tr>
<tr>
    <th>選択肢</th>
    <td>
        <label><?php
        $radio=new MkTagInputRadio('is_enabled',1,$story->is_enabled);
        $radio->print();
        ?>表示</label><br>
        <label><?php
        $radio->value(0)->checkedIf($story->is_enabled)
        ->disabled($story->id>0?false:true)->print();
        ?>非表示</label>
    </td>
</tr>
<tr><td colspan="2" style="text-align: right;"><input type="submit" value="登録内容確認"></td></tr>
</table>
<label><input type="checkbox" name="save_to_session" value="true" checked>登録完了時に以下の設定を現在のセッションにも反映する</label><br>
<label><input type="checkbox" name="save_to_defaults" value="true">登録完了時に全体デフォルト設定を以下の設定で上書きする</label><br>
<table class="edit-form-table">
<tr><td colspan="4" style="text-align: right;">※ 右のチェックボックスにONがある場合、ONの項目だけをプリセット設定にする</td></tr>
<tr><td colspan="4" style="text-align: right;">
    上書き設定制御：
    <input type="button" value="オンオフ反転" onclick="save_check_tgl();">
    <input type="button" value="全てオフ（すべて保存対象）に戻す" onclick="save_check_tgl(false);"></td></tr>
<tr>
    <th></th>
    <th>現在値</th>
    <th>設定</th>
    <th>上書対象</th>
</tr>
<tr>
    <th>ワールド</th>
    <td><?=h($setting->world_id>0?$world->name:'')?></td>
    <td class="in_input"><select name="world_id">
    <option value="">未選択</option>
    <?php
    $world_list=World::getAll($pdo);
    if(count($world_list)>0){
        foreach($world_list as $row){
            $selected= $row['id']==$setting->world_id?" selected":"";
            echo "<option value=\"{$row['id']}\" {$selected}>{$row['name']}</option>";
        }
    }
    ?></select></td>
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[world_id]" value="1" <?=isset($story_setting->world_id)?'checked':'';?>></td>
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
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[theme_dir_name]" value="1" <?=isset($story_setting->theme_dir_name)?'checked':'';?>></td>
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
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[race_search_org]" value="1" <?=(isset($story_setting->race_search_org_jra)||isset($story_setting->race_search_org_nar)||isset($story_setting->race_search_org_other))?'checked':'';?>></td>
</tr>
<tr>
    <th>年度プルダウン等起点</th>
    <td><?php echo $setting->select_zero_year; ?></td>
    <td class="in_input"><input type="number" name="select_zero_year" placeholder="空でカウント起点使用" value="<?php echo $setting->select_zero_year; ?>"></td>
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[select_zero_year]" value="1" <?=isset($story_setting->select_zero_year)?'checked':'';?>></td>
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
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[year_select_min_max_diff]" value="1" <?=(isset($story_setting->year_select_min_diff)||isset($story_setting->year_select_max_diff)||isset($story_setting->race_search_org_other))?'checked':'';?>></td>
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
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[year_view_mode]" value="1" <?=isset($story_setting->year_view_mode)?'checked':'';?>></td>
</tr>
<tr>
    <th>相対年数カウント起点の年</th>
    <td><?php echo $setting->zero_year; ?></td>
    <td class="in_input"><input type="number" name="zero_year" value="<?php echo $setting->zero_year; ?>"></td>
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[zero_year]" value="1" <?=isset($story_setting->zero_year)?'checked':'';?>></td>
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
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[birth_year_mode]" value="1" <?=isset($story_setting->birth_year_mode)?'checked':'';?>></td>
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
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[age_view_mode]" value="1" <?=isset($story_setting->age_view_mode)?'checked':'';?>></td>
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
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[horse_record_year]" value="1" <?=isset($story_setting->horse_record_year)?'checked':'';?>></td>
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
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[horse_record_date]" value="1" <?=isset($story_setting->horse_record_date)?'checked':'';?>></td>
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
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[hors_history_sort_is_desc]" value="1" <?=isset($story_setting->hors_history_sort_is_desc)?'checked':'';?>></td>
</tr>
<tr><td colspan="4">&nbsp;</td></tr>
<tr>
    <th>出馬表の年</th>
    <td><?php echo $setting->syutsuba_year?'あり':'なし'; ?></td>
    <td>
        <label><?php HTPrint::Radio('syutsuba_year','1',$setting->syutsuba_year) ?>あり</label> ｜
        <label><?php HTPrint::Radio('syutsuba_year','0',$setting->syutsuba_year) ?>なし</label>
    </td>
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[syutsuba_year]" value="1" <?=isset($story_setting->syutsuba_year)?'checked':'';?>></td>
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
    <td oncontextmenu="return false;"><input type="checkbox" name="save_target[syutsuba_date]" value="1" <?=isset($story_setting->syutsuba_date)?'checked':'';?>></td>
</tr>
</table>
<input type="submit" value="登録内容確認">
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
<script>
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
</body>
</html>