<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
InAppUrl::init(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="競走馬検索";
$session=new Session();
// 暫定でログイン＝編集可能
$page->is_editable=Session::isLoggedIn();

$pdo=getPDO();
$search = new HorseSearch();
$search->setByUrl();
$search->world_id=$setting->world_id;
$search_results=[];
if($search->SelectExec($pdo)){
    $search_results=$search->stmt->fetchAll();
}
// 検索結果が1件の場合のみ転送
if(count($search_results)===1 && $search->executed_by_form){
    $id=$search_results[0]['horse_id'];
    redirect_exit(InAppUrl::to('horse/',['horse_id'=>$id]));
}
$horse_id_is_visibled = filter_var($search->horse_id_is_visibled,FILTER_VALIDATE_BOOLEAN);

?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink('js/functions.js'); ?>
<style>
.horse_search_results th a {
    text-decoration: none;
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
<?php $search->current_page_results_count=count($search_results); ?>
<?php if($search->current_page_results_count==0){
    $search->printForm($page, $setting);
} ?>
<?php if($search->current_page_results_count>0): ?>
<?php
print("<a href=\"#foot\" title=\"最下部検索フォームに移動\" style=\"text-decoration:none;\">▽検索結果</a>｜".$search->getSearchParamStr());
print "<hr>\n";
if($search->limit>0){
    $search->printPagination();
}
?>
<table class="horse_search_results">
<tr>
    <?php if($horse_id_is_visibled): ?>
    <th><?php
    $a_tag=new MkTagA("競走馬ID");
    if($search->order!=HorseSearch::ORDER_ID__ASC){
        $a_tag->href("./?".$search->getUrlParam(['order'])."&order=".HorseSearch::ORDER_ID__ASC);
    }else{
        // ID順を選択中
        $a_tag->addClass('selected_order');
    }
    $a_tag->print();
    ?></th>
    <?php endif; /* /$horse_id_is_visibled */ ?>
    <th><?php
    $a_tag=new MkTagA("馬名");
    if($search->order!==''){
        $a_tag->href("./?".$search->getUrlParam(['order'])."&order=");
    }else{
        // 馬名順を選択中
        $a_tag->addClass('selected_order');
    }
    $a_tag->print();
    ?></th>
    <th><?php
    $a_tag=new MkTagA($setting->birth_year_mode===0?"生年":"世代");
    if($search->order!=HorseSearch::ORDER_BIRTH‗YEAR__ASC){
        // 生年検索している場合は、その年のレコードしかないため並び替えリンクにしない
        if(strval($search->birth_year)===''){
            $a_tag->href("./?".$search->getUrlParam(['order'])."&order=".HorseSearch::ORDER_BIRTH‗YEAR__ASC);
        }
    } else {
        // 生年順を表示中
        $a_tag->addClass('selected_order');
    }
    $a_tag->print();
    ?></th>
    <th>毛色</th>
    <th>性</th>
    <th>所属</th>
    <th>厩舎</th>
    <th>父</th>
    <th>母</th>
    <th>母父</th> 
    <th>　</th> 
</tr>
<?php $search_reset_array=[
    'keyword','birth_year',
    'sire_name','mare_name','bms_name','trainer',
    'sire_id','mare_id','search_text',
    'order','null_birth_year'
]; ?>
<?php foreach($search_results as $row): ?>
<?php
    $trainer=(new TrainerRow())->setFromArray($row,Trainer::TABLE.'__');
    $trainer_name=$row['trainer_name'];
    if($trainer->is_enabled && $trainer->is_anonymous && !$page->is_editable){
        $trainer_name='□□□□';
    }else{
        $trainer_name=$trainer->short_name_10?:($trainer->name?:$row['trainer_name']);
    }
?>
<tr>
    <?php if($horse_id_is_visibled): ?><td><?=h($row['horse_id'])?></td><?php endif; ?>
    <?php
    $name=$row['name_ja']?:$row['name_en'];
    $a_tag = new MkTagA($name?:ANNONYMOUS_HORSE_NAME,InAppUrl::to('horse',['horse_id'=>$row['horse_id']]));
    if(!$name){
        $a_tag->title("競走馬ID：{$row['horse_id']}");
    }
    ?><td><?=$a_tag?></td>
    <?php
        if($row['birth_year']>0){
            $birth_year_str=$setting->getBirthYearFormat($row['birth_year'],false);
            $a_tag=new MkTagA($birth_year_str);
            if($row['birth_year']!=='' && $search->birth_year===''){
                $a_tag->href("./?".$search->getUrlParam($search_reset_array)."&birth_year=".$row['birth_year']);
            }
        }
    ?><td><?=$a_tag?></td>
    <td><a><?=h($row['color'])?></a></td>
    <td><a><?=h(sex2String($row['sex']))?></a></td>
    <td><a><?=h($row['tc'])?></a></td>
    <td><?php
        if($row['trainer_name']!=''){
            $url='';
            if($row['trainer_name']!==$search->trainer){
                $url ="./?".$search->getUrlParam($search_reset_array);
                $url.="&".(new UrlParams(['trainer'=>$row['trainer_name'],'order'=>'']));
            }
            $a_tag=new MkTagA(($trainer_name)?:'□□□□',$url);
            $a_tag->print();
        }
    ?></td>
    <td><?php
        if($row['sire_id']!=''){
            $url ="./?".$search->getUrlParam($search_reset_array);
            $url.="&".(new UrlParams(['sire_id'=>$row['sire_id'],'order'=>'birth_year__asc']));
            $a_tag=new MkTagA(($row['sire_name_ja']?:$row['sire_name_en'])?:ANNONYMOUS_HORSE_NAME,$url);
            $a_tag->title("父ID: {$row['sire_id']}")->print();
        }else if($row['sire_name']!=''){
            $url ="./?".$search->getUrlParam($search_reset_array);
            $url.="&".(new UrlParams(['sire_name'=>$row['sire_name'],'order'=>'birth_year__asc']));
            (new MkTagA($row['sire_name'],$url))->print();
        }
    ?></td>
    <td><?php
        if($row['mare_id']!=''){
            $url ="./?".$search->getUrlParam($search_reset_array);
            $url.="&".(new UrlParams(['mare_id'=>$row['mare_id'],'order'=>'birth_year__asc']));
            $a_tag=new MkTagA(($row['mare_name_ja']?:$row['mare_name_en'])?:ANNONYMOUS_HORSE_NAME,$url);
            $a_tag->title("母ID: {$row['mare_id']}")->print();
        }else if($row['mare_name']!==''){
            $url ="./?".$search->getUrlParam($search_reset_array);
            $url.="&".(new UrlParams(['mare_name'=>$row['mare_name'],'order'=>'birth_year__asc']));
            (new MkTagA($row['mare_name'],$url))->print();
        }
    ?></td>
    <td><?php
        if($row['mare_id']!='' && ($row['mare_sire_id']!=''||$row['mare_sire_name']!='')){
            $bms_name=(($row['bms_name_ja']?:$row['bms_name_en'])?:$row['mare_sire_name'])?:'';
            $url ="./?".$search->getUrlParam($search_reset_array);
            $url.="&".(new UrlParams(['bms_name'=>$bms_name,'order'=>'birth_year__asc']));
            (new MkTagA($bms_name,$url))->print();
        }else if($row['bms_name']!==''){
            $url ="./?".$search->getUrlParam($search_reset_array);
            $url.="&".(new UrlParams(['bms_name'=>$row['bms_name'],'order'=>'birth_year__asc']));
            (new MkTagA($row['bms_name'],$url))->print();
        }
    ?></td>
    <td><?php
        if($row['is_sire_or_dam']){
            $tgt=$row['sex']===1?'sire_id':'mare_id';
            $url ="./?".$search->getUrlParam($search_reset_array);
            $url.="&".(new UrlParams([$tgt=>$row['horse_id'],'order'=>'birth_year__asc']));
            (new MkTagA('[繁]',$url))->print();
        }
    ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php
if($search->limit>0){
    $search->printPagination();
}
?>
<?php if($search->birth_year!==''): ?>
<hr>
<a href="?birth_year=<?=h($search->birth_year-1)?>&<?=h($search->getUrlParam(['birth_year']))?>">[前年へ]</a>
 <?=$search->birth_year?> 
<a href="?birth_year=<?=h($search->birth_year+1)?>&<?=h($search->getUrlParam(['birth_year']))?>">[翌年へ]</a>
<?php endif; ?>
<hr><a id="foot"></a>
<?php $search->printForm($page, $setting); ?>
<?php endif; ?>
<?php if($page->is_editable): ?>
<hr>
<a href="<?=h(InAppUrl::to('horse/manage/edit/'))?>">競走馬新規登録</a><br>
<?php endif; /* is_editable */ ?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>