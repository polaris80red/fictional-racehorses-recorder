<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
defineAppRootRelPath(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="競走馬登録内容確認";
$page->ForceNoindex();
$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }
$csrf_token=new FormCsrfToken();

$horse_id=(string)filter_input(INPUT_POST,'horse_id');
$is_edit_mode=filter_input(INPUT_POST,'edit_mode')?1:0;
# 対象取得
$pdo= getPDO();
// 既存データ取得
$horse= new Horse();
$horse->setDataById($pdo,$horse_id);
if(!$horse->record_exists){ 
    $is_edit_mode=0;
}else{
    $is_edit_mode=1;
}
if($horse->setDataByPost()==false){
    $page->addErrorMsgArray($horse->error_msgs);
    $page->printCommonErrorPage();
    exit;
}
$error_exists=false;
// 父IDから母情報を取得
if($horse->sire_id){
    $sire= new Horse();
    $sire->setDataById($pdo,$horse->sire_id);
    if($sire->record_exists){
        $horse->sire_name=$sire->name_ja?:$sire->name_en;
        if($sire->sex!==1){
            $page->addErrorMsg('父IDの該当馬が牡馬以外です。');
            $error_exists=true;
        }
    }else{
        $horse->sire_id='';
    }
}
// 母IDから母情報を取得
if($horse->mare_id){
    $mare= new Horse();
    $mare->setDataById($pdo,$horse->mare_id);
    if($mare->record_exists){
        $horse->mare_name=$mare->name_ja?:$mare->name_en;
        $horse->bms_name=$mare->sire_name;
        if($mare->sex!==2){
            $page->addErrorMsg('母IDの該当馬が牝馬以外です。');
            $error_exists=true;
        }
    }else{
        $horse->mare_id='';
    }
}
if($error_exists){
    $page->printCommonErrorPage();
    exit;
}

?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?>（<?php echo $is_edit_mode?"編集":"新規" ?>）</title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
<style>
table{
	border-collapse:collapse;
}

table, tr, th, td{
	border:solid 1px #333;
}

th{
	padding-left:0.3em;
	padding-right:0.3em;
}
</style>
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
<table class="edit-form-table floatLeft" style="margin-right: 4px;">
<tr>
    <th>競走馬ID</th>
    <td><?php HTPrint::HiddenAndText('horse_id',$horse_id) ?></td>
</tr>
<tr>
    <th>ワールド</th>
    <td><?php
    $world=new World($pdo,$horse->world_id);
    print_h($world->name);
    HTPrint::Hidden('world_id',$horse->world_id);
    ?></td>
</tr>
<tr>
    <th>馬名</th>
    <td><?php HTPrint::HiddenAndText('name_ja',$horse->name_ja) ?></td>
</tr>
<tr>
    <th>馬名（欧字）</th>
    <td><?php HTPrint::HiddenAndText('name_en',$horse->name_en) ?></td>
</tr>
<tr>
    <th>生年</th>
    <td><?php HTPrint::HiddenAndText('birth_year',$horse->birth_year) ?></td>
</tr>
<tr>
    <th>性別</th>
    <td><?php HTPrint::Hidden('sex',$horse->sex);
        $sex_str='';
        switch($horse->sex){
            case 1:
                $sex_str='牡';
                break;
            case 2:
                $sex_str='牝';
                break;
            case 3:
                $sex_str='セン';
                break;
            default:
                $sex_str='未選択';
        }
        print $sex_str;
    ?></td>
</tr>
<tr>
    <th>毛色</th>
    <td><?php HTPrint::HiddenAndText('color',$horse->color) ?></td>
</tr>
<tr>
    <th>所属</th>
    <td><?php HTPrint::HiddenAndText('tc',$horse->tc) ?></td>
</tr>
<tr>
    <th>調教師</th>
    <td><?php HTPrint::HiddenAndText('trainer_name',$horse->trainer_name) ?></td>
</tr>
<tr>
    <th>調教国</th>
    <td><?php HTPrint::HiddenAndText('training_country',$horse->training_country) ?></td>
</tr>
<tr>
    <th>馬主</th>
    <td><?php HTPrint::HiddenAndText('owner_name',$horse->owner_name) ?></td>
</tr>
<tr>
    <th>生産者</th>
    <td><?php HTPrint::HiddenAndText('breeder_name',$horse->breeder_name) ?></td>
</tr>
<tr>
    <th>生産国</th>
    <td><?php HTPrint::HiddenAndText('breeding_country',$horse->breeding_country) ?></td>
</tr>
<tr>
    <th>地方所属馬</th>
    <td><?php
        print $horse->is_affliationed_nar?'はい':'いいえ';
        HTPrint::Hidden('is_affliationed_nar',$horse->is_affliationed_nar);  
    ?></td>
</tr>
<tr>
    <th>父ID</th>
    <td><?php HTPrint::HiddenAndText('sire_id',$horse->sire_id) ?></td>
</tr>
<tr>
    <th>父名</th>
    <td><?php HTPrint::HiddenAndText('sire_name',$horse->sire_name) ?></td>
</tr>
<tr>
    <th>母ID</th>
    <td><?php HTPrint::HiddenAndText('mare_id',$horse->mare_id) ?></td>
</tr>
<tr>
    <th>母名</th>
    <td><?php HTPrint::HiddenAndText('mare_name',$horse->mare_name); ?></td>
</tr>
<tr>
    <th>母の父</th>
    <td><?php HTPrint::HiddenAndText('bms_name',$horse->bms_name) ?></td>
</tr>
<tr>
    <th>種牡馬<br>または<br>繫殖馬</th>
    <td><?php
        print $horse->is_sire_or_dam?'はい':'いいえ';
        HTPrint::Hidden('is_sire_or_dam',$horse->is_sire_or_dam);  
    ?></td>
</tr>
<tr>
    <th>馬名意味</th>
    <td><?php HTPrint::HiddenAndText('meaning',$horse->meaning) ?></td>
</tr>
<tr>
    <th>備考</th>
    <td class="in_input">
        <?=nl2br(h($horse->note))?>&nbsp;
        <?php HTPrint::Hidden('note',$horse->note); ?>
    </td>
</tr>
<!--<tr>
    <th>論理削除</th>
    <td><?php
        print $horse->is_enabled?'有効':'削除';
        HTPrint::Hidden('is_enabled',$horse->is_enabled);
    ?></td>
</tr>-->
</table>
<table class="edit-form-table floatLeft">
<tr>
    <th>プロフィール</th>
<tr>
</tr>
    <td class="in_input" style="max-width: 300px;">
        <?=nl2br(h($horse->profile))?>&nbsp;
        <?php HTPrint::Hidden('profile',$horse->profile); ?>
    </td>
</tr>
<?php
$horse_tags=HorseTag::TagsStrToArray(filter_input(INPUT_POST,'horse_tags'));
?>
<tr>
    <th>検索タグ</th>
<tr>
</tr>
    <td>
        <?=nl2br(h(implode("\n",$horse_tags)));?>&nbsp;
        <?php HTPrint::Hidden('horse_tags',implode(' ',$horse_tags)); ?>
    </td>
</tr>
</table>
<?php HTPrint::Hidden("is_enabled",$horse->is_enabled); ?>
<hr style="clear: both;">
<input type="submit" value="競走馬データ登録実行">
<?php $csrf_token->printHiddenInputTag(); ?>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>
