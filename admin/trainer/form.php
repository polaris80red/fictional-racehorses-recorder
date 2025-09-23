<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="調教師マスタ";
$page->title="{$base_title}｜登録";
$page->ForceNoindex();

$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }

$pdo=getPDO();
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
$input_name=filter_input(INPUT_GET,'unique_name');

$editMode=($id>0);
$TableClass=Trainer::class;
$TableRowClass=$TableClass::ROW_CLASS;

if($editMode){
    $page->title.="（編集）";
    $form_item=($TableClass)::getById($pdo,$id);
    if($form_item===false){
        $page->addErrorMsg("ID '{$id}' が指定されていますが該当するレコードがありません");
    }
}else{
    $form_item=new ($TableRowClass)();
    if($input_name){
        $form_item->unique_name=$input_name;
    }
}
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?><?=" - ".SITE_NAME; ?></title>
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
        print_h($form_item->id?:"新規登録");
        HTPrint::Hidden('id',$form_item->id);
    ?></td>
</tr>
<tr>
<?php if($form_item->id||$form_item->unique_name): ?>
<th>キー名称</th>
    <td><?=(MkTagInput::Hidden('unique_name',$form_item->unique_name)).h($form_item->unique_name)?></td>
</tr>
<?php else: ?>
<th rowspan="2">キー名称</th>
<td class="in_input"><input type="text" name="unique_name" class="required" required value="<?=h($form_item->unique_name)?>"></td>
<tr>
    <td>競走馬の調教師名がキー名称と一致すると<br>略名などを適用します。</td>
</tr>
<?php endif; ?>
<tr>
    <th>氏名</th>
    <td class="in_input"><input type="text" name="name" value="<?=h($form_item->name)?>"></td>
</tr>
<tr>
    <th>10字以内略</th>
    <td class="in_input"><input type="text" name="short_name_10" value="<?=h($form_item->short_name_10)?>" placeholder="空ならキー名を使用"></td>
</tr>
<?php
    $affiliation_list=Affiliation::getForSelectbox($pdo);
    $affiliation_name_list=[];
    if(count($affiliation_list)>0){
        foreach($affiliation_list as $row){
            $affiliation_name_list[]=$row['unique_name'];
        }
    }
?>
<tr>
    <th>所属</th>
    <td class="in_input">
        <select name="affiliation_name_select" style="width:5em;" onchange="clearElmVal('*[name=affiliation_name]');">
        <?php
            echo '<option value=""></option>'."\n";
            $target_exists=false;
            foreach($affiliation_name_list as $val){
                echo '<option value="'.$val,'"'.(($val==$form_item->affiliation_name)?' selected ':'').'>';
                if($val==$form_item->affiliation_name){$target_exists=true;}
                echo $val;
                echo '</option>'."\n";
            }
        ?></select>／
        <input type="text" name="affiliation_name" style="width: 5.5em;" value="<?=h($target_exists?'':$form_item->affiliation_name)?>" placeholder="所属手入力" onchange="clearElmVal('*[name=affiliation_name_select]');">
    </td>
</tr>
<tr>
    <th>匿名</th>
    <td>
        <label><?php
        $radio=new MkTagInputRadio('is_anonymous',0,$form_item->is_anonymous);
        $radio->print();
        ?>通常のレコード</label><br>
        <label><?php
        $radio->value(1)->checkedIf($form_item->is_anonymous)->print();
        ?>管理用</label>
    </td>
</tr>
<tr>
    <th>論理削除状態</th>
    <td>
        <label><?php
        $radio=new MkTagInputRadio('is_enabled',1,$form_item->is_enabled);
        $radio->print();
        ?>有効</label><br>
        <label><?php
        $radio->value(0)->checkedIf($form_item->is_enabled)
        ->disabled($form_item->id>0?false:true)->print();
        ?>無効化中</label>
    </td>
</tr>
<tr><td colspan="2" style="text-align: right;"><input type="submit" value="登録内容確認"></td></tr>
</table>
</form>
<?php if($form_item->id): ?>
<hr>
<div style="text-align: right;">
※ キー名称はレース結果も一括更新するため専用画面で変更してください<br>
[ <a href="./update_unique_name/form.php?<?=h(new UrlParams(['u_name'=>$form_item->unique_name]));?>">キー名称一括変換</a> ]
</div>
<?php endif; ?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>