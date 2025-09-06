<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
InAppUrl::init(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="競走馬情報";
$session=new Session();
// 暫定でログイン＝編集可能
$page->is_editable=Session::is_logined();
// ログイン中でも強制的にプレビュー表示にできるパラメータ
$is_preview=filter_input(INPUT_GET,'preview',FILTER_VALIDATE_BOOL);
if($is_preview){
    $page->is_editable=false;
}

$page->error_return_url=InAppUrl::to("horse/search");
$page->error_return_link_text="競走馬検索に戻る";
$pdo= getPDO();

$is_edit_mode = false;
if(filter_input(INPUT_GET,'mode')==='edit'){
    $is_edit_mode = true;
}
$is_edit_mode=true;
if(empty($_GET['horse_id'])){
    $page->error_msgs[]="競走馬ID未指定";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
$get_order=filter_input(INPUT_GET,'horse_history_order');
switch($get_order){
    case 'asc':
        $setting->saveToSession('hors_history_sort_is_desc',false);
        break;
    case 'desc':
        $setting->saveToSession('hors_history_sort_is_desc',true);
        break;
}

$date_order = $setting->hors_history_sort_is_desc ? 'DESC':'ASC';
$horse_id=filter_input(INPUT_GET,'horse_id');
$show_registration_only=(bool)filter_input(INPUT_GET,'show_registration_only');

$page_urlparam=new UrlParams([
    'horse_id'=>$horse_id,
    'horse_history_order'=>$get_order==='desc'?'desc':'asc',
    'show_registration_only'=>$show_registration_only,
]);
# 馬情報取得
$horse=Horse::getByHorseId($pdo,$horse_id);
if($horse===false){
    $page->error_msgs[]="競走馬情報取得失敗";
    $page->error_msgs[]="入力ID：{$horse_id}";
    header("HTTP/1.1 404 Not Found");
    $page->printCommonErrorPage();
    exit;
}
$page->horse = $horse;
if(ENABLE_ACCESS_COUNTER){
    ArticleCounter::countup($pdo,ArticleCounter::TYPE_HORSE,$horse_id);
}

$session->latest_horse=[
    'id'=>$horse_id,
    'name'=>$horse->name_ja?:$horse->name_en
];
$session->login_return_url='horse/?horse_id='.$horse_id;

$is_broodmare=$is_sire=false;
// 「繁殖馬である」
if($horse->is_sire_or_dam===1){
    if($horse->sex===2){
        $is_broodmare=true;
    }else if($horse->sex===1){
        $is_sire=true;
    }
}
$race_history=new HorseRaceHistory();
$race_history->setDateOrder($date_order);
$race_history->getDataByHorseId($pdo,$horse_id);

$sex_str=sex2String($horse->sex);
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink("js/functions.js"); ?>
<style>
.horse_base_data th{ min-width:80px; }
.horse_base_data td{ min-width:160px; }
.disabled_row{ background-color: #dddddd; }

td.track_condition{ text-align:center; }
td.race_course_name { text-align: center; }
td.grade{ text-align:center; }
td.number_of_starters{ text-align:right; }
td.frame_number{ text-align:center; }
td.favourite{ text-align:right; }
td.result_number{ text-align:right; }
table.horse_base_data a {text-decoration: none;}

.edit_menu table { margin-top: 8px;}
.edit_menu table a:link {text-decoration: none;}
.edit_menu table {font-size: 0.9em;}

</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php $page->printTitle(); ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<div style="float:left">
<?php
$name_line=[];
if($horse->name_ja){
    $name_line[]="<span style=\"font-size:1.2em;\">".h($horse->name_ja)."</span> ";
}else if($horse->name_en===''){
    $name_line[]="<span style=\"font-size:1.2em;\">".h(ANNONYMOUS_HORSE_NAME)."</span>";
}
if($horse->name_en){
    $name_line[]="<span style=\"font-size:1.1em;\">{$horse->name_en}</span>";
}
echo implode(' ',$name_line);
if($horse->birth_year>0){
    if($setting->year_view_mode===Setting::YEAR_VIEW_MODE_DEFAULT){
        print_h("（{$horse->birth_year}）");
    }else{
        print_h("（{$setting->getBirthYearFormat($horse->birth_year)}）");
    }
}else{
    print "　";
}
print_h("{$horse->color} {$sex_str}");
?>
</div>
<div style="float:right;">
    <?php if($page->is_editable): ?>
    <a href="#edit_menu" style="text-decoration: none;" title="下部編集メニューへスクロール">▽</a>
    <?php else: ?>
    <a href="#under_results_table" style="text-decoration: none;" title="下部へスクロール">▽</a>
    <?php endif; ?>
</div>
<hr style="clear: both;">
<table class="horse_base_data">
    <tr>
        <th>馬名</th>
        <td style="min-width: 12em;"><?=h($page->horse->name_ja)?></td>
    </tr>
    <tr>
        <th>馬名(欧字)</th>
        <td><?=h($page->horse->name_en)?></td>
    </tr>
    <tr>
        <th>所属</th>
        <td><?=h($page->horse->tc)?></td>
    </tr>
    <?php
        $trainer_name='';
        $trainer_is_anonymous=false;
        do{
            $trainer=Trainer::getByUniqueName($pdo,$page->horse->trainer);
            if($trainer===false || $trainer->is_enabled==0){
                $trainer_name=$page->horse->trainer;
                break;
            }
            if($trainer->is_anonymous==1){
                $trainer_is_anonymous=true;
                if(!$page->is_editable){
                    $trainer_name='□□□□';
                    break;
                }
            }
            $trainer_name=$trainer->name?:($trainer->short_name_10?:$page->horse->trainer);
        }while(false);
    ?>
    <tr>
        <th>調教師</th>
        <td><?=h($trainer_name)?></td>
    </tr>
    <tr>
        <th><?=$setting->birth_year_mode===0?"生年":"世代"?></th>
        <td><?php
        if($page->horse->birth_year>0){
            $birth_year_search=new HorseSearch();
            $birth_year_search->birth_year=$page->horse->birth_year;
            $url =$page->to_horse_search_path.'?'.$birth_year_search->getUrlParam();
            $birth_year_str=$setting->getBirthYearFormat($page->horse->birth_year);
            (new MkTagA(h($birth_year_str),$url))->print();
        }
        ?></td>
    </tr>
    <?php
        $a_tag_sanku=new MkTagA('産駒');
        $a_tag_sanku->setStyles(['display'=>'inline-block','float'=>'right;']);
    ?>
    <tr>
        <th>父</th>
        <td><?php
        $url =$page->to_horse_search_path.'?';
        $sire=Horse::getByHorseId($pdo,$page->horse->sire_id);
        if($sire!==false){
            $sire_search=new HorseSearch();
            $sire_search->sire_id=$sire->horse_id;
            $sire_search->order='birth_year__asc';
            $url .= $sire_search->getUrlParam();
            $sire_name=$sire->name_ja?:$sire->name_en;
            (new MkTagA($sire_name?:ANNONYMOUS_HORSE_NAME,"?horse_id={$sire->horse_id}"))->print();
            $a_tag_sanku->href($url)->print();
        } else if($page->horse->sire_name!=''){
            $sire_search=new HorseSearch();
            $sire_search->sire_name=$page->horse->sire_name;
            $sire_search->order='birth_year__asc';
            $url .= $sire_search->getUrlParam();
            (new MkTagA($page->horse->sire_name,$url))->print();
            $a_tag_sanku->href($url)->print();
        }
        ?></td>
    </tr>
    <tr>
        <th>母</th>
        <td><?php
        $url =$page->to_horse_search_path.'?';
        $mare=Horse::getByHorseId($pdo,$page->horse->mare_id);
        if($mare!==false){
            $mare_search=new HorseSearch();
            $mare_search->mare_id=$mare->horse_id;
            $mare_search->order='birth_year__asc';
            $url .= $mare_search->getUrlParam();
            $mare_name=$mare->name_ja?:$mare->name_en;
            (new MkTagA($mare_name?:ANNONYMOUS_HORSE_NAME,"?horse_id={$mare->horse_id}"))->print();
            $a_tag_sanku->href($url)->print();
        } else if($page->horse->mare_name!=''){
            $mare_search=new HorseSearch();
            $mare_search->mare_name=$page->horse->mare_name;
            $mare_search->order='birth_year__asc';
            $url .= $mare_search->getUrlParam();
            (new MkTagA($page->horse->mare_name,$url))->print();
            $a_tag_sanku->href($url)->print();
        }
        ?></td>
    </tr>
    <tr>
        <th>母の父</th>
        <td><?php
        $bms_name='';
        do{
            if($mare!==false && $mare->is_enabled==1){
                if($mare->sire_id){
                    // 母に父IDがある場合は母の父レコードか母の父名
                    $bms=Horse::getByHorseId($pdo,$mare->sire_id);
                    if($bms!==false && $bms->is_enabled==1){
                        // 母父の馬レコードがある場合はその名前
                        $bms_name=$bms->name_ja?:$bms->name_en;
                        break;
                    }
                }
                // 母に有効な父のレコードがない場合は母の父名
                $bms_name=$mare->sire_name;
                break;
            }
            // 母に父IDがない場合は自身の母父名
            $bms_name = $page->horse->bms_name;
        }while(false);
        if($bms_name!=''){
            $bms_name_search=new HorseSearch();
            $bms_name_search->bms_name=$bms_name;
            $url =$page->to_horse_search_path.'?'.$bms_name_search->getUrlParam();
            (new MkTagA($bms_name,$url))->print();
        }
        ?></td>
    </tr>
</table>
<hr>
<span><?php
print_h($race_history->race_count_1."-");
print_h($race_history->race_count_2."-");
print_h($race_history->race_count_3."-");
?><span style="<?=$race_history->has_unregistered_race_results?'color:#999999;':'';?>"><?php
print_h($race_history->race_count_4+$race_history->race_count_5+$race_history->race_count_n." / ");
print_h($race_history->race_count_all."戦");
?></span></span>
<?php
if($is_broodmare){
    $foal_search=new HorseSearch();
    $foal_search->mare_id=$horse_id;
    $foal_search->order='birth_year__asc';
    $url = $page->to_horse_search_path.'?'.$foal_search->getUrlParam();
    $a_tag=new MkTagA('産駒',$url);
    echo "｜";
    $a_tag->print();
}
if($is_sire){
    $foal_search=new HorseSearch();
    $foal_search->sire_id=$horse_id;
    $foal_search->order='birth_year__asc';
    $url = $page->to_horse_search_path.'?'.$foal_search->getUrlParam();
    $a_tag=new MkTagA('産駒',$url);
    echo "｜";
    $a_tag->print();
}
$mode_umm=false;
switch($setting->age_view_mode){
    case Setting::AGE_VIEW_MODE_UMAMUSUME:
    case Setting::AGE_VIEW_MODE_UMAMUSUME_S:
        $mode_umm = true;
    break;
}
?>
<hr>
<table class="horse_history">
<tr>
    <th><?php
    echo $setting->horse_record_date==='umm'?'時期 ':'年月 ';
    $order=$setting->hors_history_sort_is_desc?"asc":"desc";
    $a_tag=new MkTagA($setting->hors_history_sort_is_desc?"↑":"↓");
    $a_tag->href("?".$page_urlparam->toString(['horse_history_order'=>$order]));
    $a_tag->setStyle('text-decoration','none');
    $a_tag->print();
    ?></th>
    <th>開催</th><th>距離</th><th>馬場</th><th>格付</th><th>レース名</th>
    <th><?=$mode_umm?'人数':'頭数'?></th>
    <th>枠</th>
    <th>人気</th><th>着順</th>
    <?php if(!$mode_umm): ?><th>騎手</th><?php endif; ?>
    <th>斤量</th>
    <th>1着馬<span class="nowrap">(2着馬)</span></th><th>　</th>
    <?php if($page->is_editable): ?><th></th><?php endif; ?>
</tr><?php
$FUNC_print_empty_row=function($non_registered_prev_race_number,$next_race_id='') use($page,$horse_id,$mode_umm){
    $ret_text='';
    if($non_registered_prev_race_number>0){
        $ret_text.="<tr><td style=\"color:#999999;\">（{$non_registered_prev_race_number}戦～）</td>";
        $ret_text.="<td></td>"."<td></td>"."<td></td>"."<td></td>"."<td>……</td>"."<td></td>"."<td></td>"."<td></td>"."<td></td>"."<td></td><td></td><td></td>";
        if(!$mode_umm) { $ret_text.="<td></td>"; }
        if($page->is_editable){
            $params=['horse_id'=>$horse_id];
            if($next_race_id!==''){
                $params['next_race_id']=$next_race_id;
            }
            $url =InAppUrl::to("race/horse_result/form.php",$params);
            $ret_text.="<td><a href=\"".h($url)."\">新</td>";
        }
        $ret_text.="</tr>\n";
    }
    return $ret_text;
};
$registration_only_race_is_exists=false;
$latest_race_is_exists=false; ?>
<?php foreach ($race_history as $data):?>
    <?php
        if(empty($data->race_id)){ continue; }
        $race = $data->race_row;
        $grade = $data->grade_row;
        $jockey=$data->jockey_row;
    ?>
    <?php if($date_order=='ASC'):// 空行の追加 ?>
        <?=$FUNC_print_empty_row($data->non_registered_prev_race_number,$race->race_id)?>
    <?php endif; ?>
    <?php
        if(!empty($session->latest_race['id'])&&
            $session->latest_race['id']===$race->race_id)
            {
                $latest_race_is_exists=true;
            }
        $tr_class=new Imploader(' ');
        // 特別登録のみのデータは表示フラグがなければスキップ
        $race_url_add_param='';
        if($data->is_registration_only==1){
            $registration_only_race_is_exists=true;
            if(!$show_registration_only){
                continue;
            }else{
                $tr_class->add('disabled_row');
                $race_url_add_param='&show_registration_only=true';
            }
        }
        $tr_class->add('race_grade_'.$grade->css_class_suffix);
        if($race->is_enabled===0){ $tr_class->add('disabled_row'); }
    ?>
    <tr class="<?=h($tr_class)?>">
        <?php
            $datetime=null;
            if($race->date!=null && $race->is_tmp_date==0){
                $datetime=new DateTime($race->date);
            }
            $month=$race->month;
            // ウマ娘ターン表記の場合は補正済み月を優先
            if($setting->horse_record_date==='umm' && $data->w_month > 0){
                $month=$data->w_month;
            }
            $day=is_null($datetime)?0:(int)$datetime->format('d');
            $date_str=$setting->getRaceListDate([
                'year'=>$race->year,
                'month'=>$month,
                'day'=>$day,
                'turn'=>$data->umm_month_turn,
                'age'=>$race->year - $horse->birth_year]);
            $url = '';
            if($setting->horse_record_date==='umm'){
                if($data->umm_month_turn > 0){
                    $url = $page->getTurnRaceListUrl($race->year,$month,$data->umm_month_turn);
                }
            }else if($datetime!==null){
                $url=$page->getDateRaceListUrl($datetime);
            }else{
                $url = $page->getTurnRaceListUrl($race->year,$month,null,['week'=>$race->week_id]);
            }
        ?>
        <td><?=(new MkTagA($date_str,$url))?></td>
        <?php
            $race_course_show_name = $data->race_course_mst_short_name??$race->race_course_name;
            $a_tag=new MkTagA($race_course_show_name);
            if($datetime!==null){
                $a_tag->href($page->getDateRaceListUrl(
                    $datetime,
                    ['race_course_name'=>$race->race_course_name]
                ));
                $a_tag->title($race->race_course_name);
            }
        ?>
        <td class="race_course_name"><?=$a_tag?></td>
        <td class="distance"><?=h($race->course_type.$race->distance)?></td>
        <td class="track_condition"><?=h($race->track_condition)?></td>
        <td class="grade"><?=h(($grade->short_name??'')?:$race->grade)?></td>
        <td class="race_name">
            <?=(new MkTagA($race->race_name,$page->getRaceResultUrl($race->race_id).$race_url_add_param))->title($race->race_name.($race->caption?'：'.$race->caption:''))?>
        </td>
        <td class="number_of_starters"><?=h($race->number_of_starters)?></td>
        <td class="frame_number"><?=h($data->frame_number)?></td>
        <td class="favourite favourite_<?=h($data->favourite)?>"><?=h($data->favourite)?></td>
        <?php
            $add_class=getResultClass($data->result_number);
            $h_result_txt='';
            if($data->result_text!=''){
                $h_result_txt=h($data->special_result_short_name_2?:$data->result_text);
            }else if($data->result_number > 0){
                if($data->result_before_demotion > 0){
                    $h_result_txt.="<span title=\"※".h($data->result_before_demotion)."位入線降着\">(降)</span>";
                }
                $h_result_txt.=h($data->result_number."着");
            }
        ?>
        <td class="result_number <?=h($add_class)?>"><?=$h_result_txt?></td>
        <?php if(!$mode_umm): ?>
            <?php
                $jockey_name=$data->jockey_unique_name;
                if($jockey->is_enabled===1){
                    if($jockey->is_anonymous==1){
                        if($page->is_editable){
                            $jockey_name = $jockey->short_name_10?:$data->jockey_unique_name;
                        }else{
                            $jockey_name='□□□□';
                        }
                    }else{
                        $jockey_name = $jockey->short_name_10?:$data->jockey_unique_name;
                    }
                }
            ?>
            <td class="jockey" <?=(!$jockey->is_anonymous?'':'style="color:#999;"')?>><?=h($jockey_name)?></td>
        <?php endif; ?>
        <td class="handicap"><?=h($data->handicap)?></td>
        <?php
            $a_tag=new MkTagA($data->r_name_ja?:$data->r_name_en,InAppUrl::to('horse/',[
                'horse_id'=>$data->r_horse_id
            ]));
        ?>
        <td class="r_horse"><?=$data->result_number==1?"({$a_tag})":$a_tag?></td>
        <td><?=!$data->has_jra_thisweek?'':new MkTagA('記',InAppUrl::to('race/j_thisweek.php',['race_id'=>$race->race_id,'show_registration_only'=>($race_url_add_param?true:null)]))?></td>
        <?php if($page->is_editable): ?>
        <td class="edit_link"><?=(new MkTagA('編',InAppUrl::to("race/horse_result/form.php",[
                    'race_id'=>$race->race_id,
                    'horse_id'=>$horse->horse_id,
                    'edit_mode'=>1,
                ])))?></td>
        <?php endif; ?>
    </tr>
    <?php if($date_order=='DESC'):// 空行の追加 ?>
        <?=$FUNC_print_empty_row($data->non_registered_prev_race_number,$race->race_id)?>
    <?php endif; ?>
<?php endforeach; ?>
</table>
<hr>
<a id="under_results_table"></a>
<table class="horse_base_data">
<tr><th>馬名意味</th><td><?=h($page->horse->meaning)?></td></tr>
<tr><th>備考</th><td><?=nl2br(h($page->horse->note))?></td></tr>
</table>
<?php
if($registration_only_race_is_exists||$show_registration_only){
    print('<hr>');
    $a_tag=new MkTagA("特別登録のみのレースを".($show_registration_only?"非表示(現在:表示)":"表示(現在:非表示)")."");
    $a_tag->href("?".$page_urlparam->toString(['show_registration_only'=>!$show_registration_only]));
    $a_tag->print();
}
?>
<?php $horse_tags=(new HorseTag($pdo))->getTagNames($page->horse->horse_id); ?>
<?php if(count($horse_tags)>0):?>
<hr>
検索タグ：<?php
        $search_text_search=new HorseSearch();
        $links=new Imploader('　');
        foreach($horse_tags as $tag){
            $search_text_search->search_text = $tag;
            $url ="{$page->to_horse_search_path}?".$search_text_search->getUrlParam();
            $links->add("<a href=\"".h($url)."\">#".h($tag)."</a>");
        }
        print($links);
?>
<?php endif; ?>
<?php if($page->is_editable): ?>
<hr><input type="button" id="edit_tgl" value="編集" style="<?=EDIT_MENU_TOGGLE===false?'display:none;':'';?>">
<input type="hidden" id="hiddden_horse_id" value="<?=h($page->horse->horse_id)?>">
<input type="button" value="競走馬IDをクリップボードにコピー" onclick="copyToClipboard('#hiddden_horse_id');">
(horse_id=<?=h($page->horse->horse_id)?>)<a id="edit_menu"></a>
<input type="hidden" id="edit_menu_states" value="0">
<div class="edit_menu" style="<?=EDIT_MENU_TOGGLE?'display:none;':'';?>">
<table>
    <tr>
        <td>
            <?=(new MkTagA('この馬の情報を編集',InAppUrl::to('horse/manage/edit/',['horse_id'=>$page->horse->horse_id])))?>
        </td>
        <td>
<?php
    $url=InAppUrl::to('race/horse_result/form.php',['horse_id'=>$page->horse->horse_id]);
    $a_tag=new MkTagA('この馬の戦績を追加');
        $a_tag->href($url);
    if($page->horse->birth_year==null){
        $a_tag->href('')->setStyle('text-decoration','line-through');
        $a_tag->title("生年仮登録馬のため戦績追加不可");
    }
    print $a_tag;
?>
        </td>
        <td>
            <?=(new MkTagA('競走馬ID修正',"./manage/update_horse_id/?horse_id=".urlencode($page->horse->horse_id)))?>
        </td>
    </tr>
    <tr>
        <td><?=(new MkTagA('レース結果一括編集',$race_history->race_count_all>0?InAppUrl::to('horse/manage/bulk_edit/',['horse_id'=>$page->horse->horse_id]):''))?></td>
        <td></td><td></td>
    </tr>
<?php if($page->horse->birth_year!==null): ?>
    <tr>
        <td colspan="2">
<?php
    $a_tag=new MkTagA('最後に開いたレースにこの馬の戦績を追加');
    if(!empty($session->latest_race['id'])){
        $url=InAppUrl::to('race/horse_result/form.php',['horse_id'=>$page->horse->horse_id,'race_id'=>$session->latest_race['id']]);
        $a_tag->href($url);
        if($latest_race_is_exists===true){
            $a_tag->href('')->setStyle('text-decoration','line-through');
            $a_tag->title("最後に開いたレースには既に登録されています");
        }
    }
    print $a_tag;
?>
        </td>
        <td>
<?php
    if(!empty($session->latest_race['id'])){
        $url=$page->getRaceResultUrl($session->latest_race['id']);
        $text= $session->latest_race['year']." ".($session->latest_race['name']?:$session->latest_race['id']);
        (new MkTagA($text,$url))->print();
    }
?>
        </td>
    </tr>
    <tr>
        <td colspan="3">レースを新規登録してからこの馬の戦績を追加</td>
    </tr>
    <tr>
        <td colspan="3" style="text-align: right;">
<?php
$params=['horse_id'=>$page->horse->horse_id];
$url='race/manage/edit/';
$params['year']=$page->horse->birth_year+2;
echo (new MkTagA('[2歳年]'))->href(InAppUrl::to($url,$params));
echo "　";
$params['year']=$page->horse->birth_year+3;
echo (new MkTagA('[3歳年]'))->href(InAppUrl::to($url,$params));
echo "　";
$params['year']=$page->horse->birth_year+4;
echo (new MkTagA('[4歳年]'))->href(InAppUrl::to($url,$params));
echo "　";
$params['year']=$page->horse->birth_year+5;
echo (new MkTagA('[5歳年]'))->href(InAppUrl::to($url,$params));
?>
        </td>
    </tr>
    <tr><td colspan="3"></tr>
    <tr>
        <td rowspan="2">レース検索（重賞）</td>
        <td colspan="2" style="text-align: right;">
<?php
$url_param=new UrlParams(['session_is_not_update'=>1,'grade_g1'=>1,'grade_g2'=>1,'grade_g3'=>1]);
$url=InAppUrl::to("race/list/?");
echo (new MkTagA('[2歳年]'))->href($url.$url_param->toString(['year'=>$page->horse->birth_year+2,'age[20]'=>1]));
echo "　".(new MkTagA('[3歳年]'))->href($url.$url_param->toString(['year'=>$page->horse->birth_year+3,'age[30]'=>1,'age[31]'=>1]));
echo "　".(new MkTagA('[4歳年]'))->href($url.$url_param->toString(['year'=>$page->horse->birth_year+4,'age[31]'=>1,'age[41]'=>1]));
echo "　".(new MkTagA('[5歳年]'))->href($url.$url_param->toString(['year'=>$page->horse->birth_year+5,'age[31]'=>1,'age[41]'=>1]));
?>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: right;">
<?php
    $url_param=new UrlParams(['session_is_not_update'=>1,'grade_g1'=>1,'grade_g2'=>1,'grade_g3'=>1,'show_organization_jra'=>1]);
    $url=InAppUrl::to("race/list/?");
    echo (new MkTagA('[世代基準・中央重賞]'))->href($url.$url_param->toString(['year'=>$page->horse->birth_year+3,'is_generation_search'=>1]));
?>
        </td>
    </tr>
    <tr>
        <td>レース検索（すべて）</td>
        <td colspan="2" style="text-align: right;">
<?php
if($page->horse->birth_year!==null){
    $url_param=new UrlParams(['session_is_not_update'=>1]);
    $url=InAppUrl::to('race/list/?');
    echo (new MkTagA('[2歳年]'))->href($url.$url_param->toString(['year'=>$page->horse->birth_year+2,'age[20]'=>1]));
    echo "　".(new MkTagA('[3歳年]'))->href($url.$url_param->toString(['year'=>$page->horse->birth_year+3,'age[30]'=>1,'age[31]'=>1]));
    echo "　".(new MkTagA('[4歳年]'))->href($url.$url_param->toString(['year'=>$page->horse->birth_year+4,'age[31]'=>1,'age[41]'=>1]));
    echo "　".(new MkTagA('[5歳年]'))->href($url.$url_param->toString(['year'=>$page->horse->birth_year+5,'age[31]'=>1,'age[41]'=>1]));
}
?>
        </td>
    </tr>
<?php endif; ?>
</table>
</div><!-- /.edit_menu -->
<script>
$(function() {
    // 編集メニュー開閉
    $('#edit_tgl').click(function(){
    if($('#edit_menu_states').val()=='0') {
        $('.edit_menu').css('display','block');
        $('#edit_menu_states').val('1');
    } else {
        $('.edit_menu').css('display','none');
        $('#edit_menu_states').val('0');
    }
    });
});
</script>
<?php endif; ?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>