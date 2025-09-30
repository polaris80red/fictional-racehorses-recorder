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
if(!Session::isLoggedIn()){ $page->exitToHome(); }
$csrf_token=new FormCsrfToken();

$horse_id=(string)filter_input(INPUT_POST,'horse_id');
$is_edit_mode=filter_input(INPUT_POST,'edit_mode')?1:0;
# 対象取得
$pdo= getPDO();
// 既存データ取得
$horse= Horse::getByHorseId($pdo,$horse_id);
if(!$horse){ 
    $is_edit_mode=0;
    $horse=new HorseRow();
}else{
    $is_edit_mode=1;
    if($horse && !Session::currentUser()->canEditHorse($horse)){
        header("HTTP/1.1 403 Forbidden");
        $page->addErrorMsg("編集権限がありません");
        $page->printCommonErrorPage();
        exit;
    }
}
$horse->setFromPost();
$horse->validate();
if($horse->hasErrors){
    $page->addErrorMsgArray($horse->errorMessages);
    $page->printCommonErrorPage();
    exit;
}
// 父IDから母情報を取得
if($horse->sire_id){
    $sire=Horse::getByHorseId($pdo,$horse->sire_id);
    if($sire!==false){
        $horse->sire_name=$sire->name_ja?:$sire->name_en;
        if($sire->sex!==1){
            $page->addErrorMsg('父IDの該当馬が牡馬以外です。');
        }
    }else{
        $horse->sire_id='';
    }
}
// 母IDから母情報を取得
if($horse->mare_id){
    $mare=Horse::getByHorseId($pdo,$horse->mare_id);
    if($mare!==false){
        $horse->mare_name=$mare->name_ja?:$mare->name_en;
        $horse->bms_name=$mare->sire_name;
        if($mare->sex!==2){
            $page->addErrorMsg('母IDの該当馬が牝馬以外です。');
        }
    }else{
        $horse->mare_id='';
    }
}
$horse_tags=HorseTag::TagsStrToArray(filter_input(INPUT_POST,'horse_tags'));
foreach($horse_tags as $tag){
    if(mb_strlen($tag)>100){
        $page->addErrorMsg('1つのタグは100文字以内で設定してください。');
        break;
    }
}
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
$page->title.="（".($is_edit_mode?"編集":"新規")."）";
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?=h($page->renderTitle())?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?=$page->renderBaseStylesheetLinks()?>
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
<h1 class="page_title"><?=h($page->title)?></h1>
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
    $world=World::getById($pdo,$horse->world_id);
    HTPrint::Hidden('world_id',$horse->world_id);
    ?><?=h($world->name??'')?></td>
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
    <th>生年月日</th>
    <td>
        <?php HTPrint::HiddenAndText('birth_year',$horse->birth_year) ?>
        <?php HTPrint::Hidden('birth_month',$horse->birth_month) ?>
        <?php HTPrint::Hidden('birth_day_of_month',$horse->birth_day_of_month) ?>
        <?=h(($horse->birth_month?"{$horse->birth_month}月":'').($horse->birth_day_of_month?"{$horse->birth_day_of_month}日":''))?>
    </td>
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
    <th>調教師・国</th>
    <td>
        <?php HTPrint::Hidden('trainer_name',$horse->trainer_name) ?>
        <?php HTPrint::Hidden('training_country',$horse->training_country) ?>
        <?=h($horse->trainer_name)?><?=h($horse->training_country?"({$horse->training_country})":'')?>
    </td>
</tr>
<tr>
    <th>馬主</th>
    <td><?php HTPrint::HiddenAndText('owner_name',$horse->owner_name) ?></td>
</tr>
<tr>
    <th>生産者・国</th>
    <td>
        <?php HTPrint::Hidden('breeder_name',$horse->breeder_name) ?>
        <?php HTPrint::Hidden('breeding_country',$horse->breeding_country) ?>
        <?=h($horse->breeder_name)?><?=h($horse->breeding_country?"({$horse->breeding_country})":'')?>
    </td>
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
    <th>繫殖(種牡)馬</th>
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
