<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="レース格付マスタ";
$page->title="{$base_title}｜設定登録";

$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$pdo=getPDO();
$input_id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
$input_name=filter_input(INPUT_GET,'name');

$s_setting=new Setting(false);
if($input_id>0){
    $race_grade=RaceGrade::getById($pdo,$input_id);
    if($race_grade!==false){
        $page->title.="（編集）";
    }else{
        $input_id=0;
    }
}
if($input_id==0){
    $race_grade=new RaceGradeRow();
    if($input_name){
        $race_grade->unique_name=$input_name;
    }
}

?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle();  ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
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
        print $race_grade->id?:"新規登録";
        HTPrint::Hidden('race_grade_id',$race_grade->id);
    ?></td>
</tr>
<tr>
    <th rowspan="1">キー名称</th>
    <td class="in_input"><input type="text" name="unique_name" class="required" value="<?php print $race_grade->unique_name; ?>"<?=(($race_grade->id||$input_name)?' readonly':'')?> required></td>
</tr>
<tr>
    <th>短縮名</th>
    <td class="in_input"><input type="text" name="short_name" value="<?php print $race_grade->short_name; ?>" placeholder="空ならキー名称を使用"></td>
</tr>
<tr>
    <th>検索判定</th>
    <td class="in_input">
        <select name="search_grade" style="width:5em;">
<?php
    $search_grade_list=['G1','G2','G3','L','OP','重賞','3勝','2勝','1勝','未勝','新馬'];
    echo '<option value=""></option>'."\n";
    $target_exists=false;
    foreach($search_grade_list as $val){
        if($val==$race_grade->search_grade){
            $selected_or_empty=' selected ';
        }else{
            $selected_or_empty='';
        }
        echo '<option value="'.$val,'"'.$selected_or_empty.'>';
        echo $val;
        echo '</option>'."\n";
    }
?></select>
    </td>
</tr>
<tr>
    <th>カテゴリ</th>
    <td class="in_input"><input type="text" name="category" value="<?php print $race_grade->category; ?>"></td>
</tr>
<tr>
    <th>CSSクラス接尾語</th>
    <td class="in_input"><input type="text" name="css_class_suffix" value="<?php print $race_grade->css_class_suffix; ?>"></td>
</tr>
<tr>
    <th>表示順補正</th>
    <td class="in_input"><input type="number" name="sort_number" value="<?php print $race_grade->sort_number; ?>" placeholder="昇順"></td>
</tr>
<tr>
    <th>プルダウンに表示</th>
    <td>
        <label><?php
        $radio=new MkTagInputRadio('show_in_select_box',1,$race_grade->show_in_select_box);
        $radio->print();
        ?>表示</label><br>
        <label><?php
        $radio->value(0)->checkedIf($race_grade->show_in_select_box)->print();
        ?>非表示</label>
    </td>
</tr>
<tr>
    <th>論理削除状態</th>
    <td>
        <label><?php
        $radio=new MkTagInputRadio('is_enabled',1,$race_grade->is_enabled);
        $radio->print();
        ?>有効</label><br>
        <label><?php
        $radio->value(0)->checkedIf($race_grade->is_enabled)
        ->disabled($race_grade->id>0?false:true)->print();
        ?>無効化中</label>
    </td>
</tr>
<tr><td colspan="2" style="text-align: right;"><input type="submit" value="登録内容確認"></td></tr>
</table>
</form>
<?php if($race_grade->id): ?>
<hr>
<div style="text-align: right;">
※ キー名称はレース側のグレードも一括更新するため専用画面で変更してください<br>
[ <a href="./update_unique_name/form.php?<?=(new UrlParams(['u_name'=>$race_grade->unique_name]));?>">キー名称一括変換</a> ]
</div>
<?php endif; ?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>