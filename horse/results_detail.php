<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
InAppUrl::init(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="競走馬情報 | 詳細戦績";
$session=new Session();

$pdo= getPDO();
$page->setErrorReturnLink("競走馬検索に戻る",InAppUrl::to("horse/search"));
$errorHeader="HTTP/1.1 404 Not Found";
do{
    $horse_id=(string)filter_input(INPUT_GET,'horse_id');
    if($horse_id==''){
        $page->addErrorMsg("競走馬ID未指定");
        break;
    }
    $horse=Horse::getByHorseId($pdo,$horse_id);
    if($horse===false){
        $page->addErrorMsg("競走馬情報取得失敗\n入力ID：{$horse_id}");
        break;
    }
}while(false);
$page->renderErrorsAndExitIfAny($errorHeader);
$page->horse = $horse;
if(ENABLE_ACCESS_COUNTER){
    ArticleCounter::countup($pdo,ArticleCounter::TYPE_HORSE_RESULTS_DETAIL,$horse_id);
}
// 編集可否チェック
$page->is_editable=Session::isLoggedIn() && Session::currentUser()->canEditHorse($horse);

// ログイン中でも強制的にプレビュー表示にできるパラメータ
$is_preview=filter_input(INPUT_GET,'preview',FILTER_VALIDATE_BOOL);
if($is_preview){
    $page->is_editable=false;
}
$page->has_edit_menu=true;

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
$show_registration_only=(bool)filter_input(INPUT_GET,'show_registration_only');
$show_race_note=filter_input(INPUT_GET,'show_race_note',FILTER_VALIDATE_BOOL);
$show_horse_note=filter_input(INPUT_GET,'show_horse_note',FILTER_VALIDATE_BOOL);

$page_urlparam=new UrlParams(array_diff([
    'horse_id'=>$horse_id,
    'horse_history_order'=>$get_order==='desc'?'desc':'asc',
    'show_registration_only'=>$show_registration_only,
    'show_race_note'=>$show_race_note,
    'show_horse_note'=>$show_horse_note,
    'preview'=>$is_preview,
],[0,'',false]));

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
$race_history=new HorseRaceHistory($pdo,$horse_id);
$race_history->setDateOrder($date_order);
$race_history->getData();

$sex_str=sex2String($horse->sex);

$title=(function($pageTitle)use($horse){
    $t = $horse->name_ja?:'';
    if($horse->name_en){
        $t.=$t?" ({$horse->name_en})":$horse->name_en;
    }
    return $pageTitle.($t?" | {$t}":'');
})($page->title);
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?=h($page->renderTitle($title))?></title>
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

td.race_course_name { text-align: center; }
td.weather{ text-align:center; }
td.track_condition{ text-align:center; }
td.grade{ text-align:center; }
td.number_of_starters{ text-align:right; }
td.frame_number{ text-align:center; }
td.horse_number{ text-align:right; }
td.odds{ text-align:right; }
td.favourite{ text-align:right; }
td.result_number{ text-align:right; }
td.corner{ text-align:center; }
td.h_weight{ text-align:center; }
td.earnings{ text-align:right; }
td.syuutoku{ text-align:right; }
table.horse_base_data a {text-decoration: none;}
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php include (new TemplateImporter('horse/horse_page-header.inc.php'));?>
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
$colSpan=22+($mode_umm?0:2);//+($page->is_editable?1:0);
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
    <th>開催</th>
    <th>天<br>候</th>
    <th>レース名</th>
    <th>格付</th>
    <th><?=$mode_umm?'人<br>数':'頭<br>数'?></th>
    <th>枠</th>
    <th>馬<br>番</th>
    <th>オッズ</th>
    <th>人<br>気</th>
    <th>着順</th>
    <?php if(!$mode_umm): ?>
        <th>騎手</th>
    <?php endif; ?>
    <th>斤量</th>
    <th>距離</th>
    <th>馬場</th>
    <th>タイム</th>
    <th>着差</th>
    <th>通過</th>
    <th>上り</th>
    <?php if(!$mode_umm): ?>
        <th>馬体重</th>
    <?php endif; ?>
    <th>1着馬<span class="nowrap">(2着馬)</span></th>
    <th>本賞金<br>(万円)</th>
    <th>収得<br>賞金</th>
    <th>記</th>
    <?php if($page->is_editable): ?><th></th><?php endif; ?>
</tr><?php
$FUNC_print_empty_row=function($non_registered_prev_race_number,$next_race_id='') use($page,$horse_id,$mode_umm){
    $ret_text='';
    if($non_registered_prev_race_number>0){
        $ret_text.="<tr><td style=\"color:#999999;\">（{$non_registered_prev_race_number}戦～）</td>";
        $ret_text.=str_repeat("<td></td>",2)."<td>……</td>";
        $ret_text.=str_repeat("<td></td>",18);
        if(!$mode_umm) { $ret_text.=str_repeat("<td></td>",2); }
        if($page->is_editable){
            $params=['horse_id'=>$horse_id];
            if($next_race_id!==''){
                $params['next_race_id']=$next_race_id;
            }
            $url =InAppUrl::to(Routes::HORSE_RACE_RESULT_EDIT,$params);
            $ret_text.="<td><a href=\"".h($url)."\">新</td>";
        }
        $ret_text.="</tr>\n";
    }
    return $ret_text;
};
$FUNC_print_note_row=function(string $title,$tr_class,$race_id,$note,$horse_id='')use($page,$colSpan){
    if($note==''){ return; }
    ?>
        <tr class="<?=h($tr_class)?>">
            <td></td>
            <td style="text-align: center;"><?=h($title)?></td>
            <td colspan="<?=$colSpan-2?>">
                <?=nl2br(h($note))?>
            </td>
            <?php if($page->is_editable):?>
                <td><?=new MkTagA('記',InAppUrl::to('race/manage/note_edit/',['race_id'=>$race_id],$horse_id?"horse_{$horse_id}":''))?></td>
            <?php endif;?>
        </tr>
    <?php
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
        $tr_class->add($grade->css_class);
        if($race->is_enabled===0){ $tr_class->add('disabled_row'); }
        // レースメモの描画
        if($date_order==='ASC'){
            $FUNC_print_note_row('前',$tr_class,$race->race_id,$show_race_note?$race->previous_note:'');
            $FUNC_print_note_row('前',$tr_class,$race->race_id,$show_horse_note?$data->race_previous_note:'',$horse_id);
        }else if($date_order==='DESC'){
            $FUNC_print_note_row('後',$tr_class,$race->race_id,$show_race_note?$race->after_note:'');
            $FUNC_print_note_row('後',$tr_class,$race->race_id,$show_horse_note?$data->race_after_note:'',$horse_id);
        }
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
            $race_course_show_name = $data->course_row->short_name??$race->race_course_name;
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
        <td class="weather"><?=h($race->weather)?></td>
        <td class="race_name">
            <?=(new MkTagA($race->race_name,$page->getRaceResultUrl($race->race_id).$race_url_add_param))->title($race->race_name.($race->caption?'：'.$race->caption:''))?>
        </td>
        <td class="grade"><?=h(($grade->short_name??'')?:$race->grade)?></td>
        <td class="number_of_starters"><?=h($race->number_of_starters)?></td>
        <td class="frame_number"><?=h($data->frame_number)?></td>
        <td class="horse_number"><?=h($data->horse_number)?></td>
        <td class="odds"><?=h($data->odds)?></td>
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
            <td class="jockey" <?=(!$jockey->is_anonymous?'':'style="color:#999;"')?>><?=h($data->getJockeyName($page->is_editable))?></td>
        <?php endif; ?>
        <td class="handicap"><?=h($data->handicap)?></td>
        <td class="distance"><?=h($race->course_type.$race->distance)?></td>
        <td class="track_condition"><?=h($race->track_condition)?></td>
        <td class="time"><?=h($data->time)?></td>
        <td class="margin"><?=h($data->margin)?></td>
        <?php
            $corner_list=[];
            if($data->corner_1>0){ $corner_list[]=$data->corner_1; }
            if($data->corner_2>0){ $corner_list[]=$data->corner_2; }
            if($data->corner_3>0){ $corner_list[]=$data->corner_3; }
            if($data->corner_4>0){ $corner_list[]=$data->corner_4; }
        ?>
        <td class="corner"><?=h(implode(' - ',$corner_list))?></td>
        <td class="f_time"><?=h($data->f_time)?></td>
        <?php if(!$mode_umm): ?>
            <td class="h_weight"><?=h($data->h_weight)?></td>
        <?php endif; ?>
        <?php
            $a_tag=new MkTagA($data->r_name_ja?:$data->r_name_en,InAppUrl::to('horse/',[
                'horse_id'=>$data->r_horse_id
            ]));
        ?>
        <td class="r_horse"><?=$data->result_number==1?"({$a_tag})":$a_tag?></td>
        <td class="earnings"><?=h($data->earnings?:'')?></td>
        <td class="syuutoku"><?=h($data->syuutoku?:'')?></td>
        <td>
            <?php
                $list=[
                    (!$data->race_previous_note && !$race->previous_note)?'':(new MkTagA('前',InAppUrl::to('race/race_previous_note.php',['race_id'=>$race->race_id])))->title("レース前メモ"),
                    (!$data->race_after_note && !$race->after_note)?'':(new MkTagA('後',InAppUrl::to('race/race_after_note.php',['race_id'=>$race->race_id])))->title("レース後メモ"),
                    !$data->has_jra_thisweek?'':(new MkTagA('記',InAppUrl::to('race/j_thisweek.php',['race_id'=>$race->race_id,'show_registration_only'=>($race_url_add_param?true:null)])))->title("今週の注目レース"),
                ]
            ?>
            <?=implode('｜',array_diff($list,['']))?>
        </td>
        <?php if($page->is_editable): ?>
        <td class="edit_link"><?=(new MkTagA('編',InAppUrl::to(Routes::HORSE_RACE_RESULT_EDIT,[
                    'race_id'=>$race->race_id,
                    'horse_id'=>$horse->horse_id,
                    'edit_mode'=>1,
                ])))?></td>
        <?php endif; ?>
    </tr>
    <?php
        // レースメモの描画
        if($date_order==='DESC'){
            $FUNC_print_note_row('前',$tr_class,$race->race_id,$show_race_note?$race->previous_note:'');
            $FUNC_print_note_row('前',$tr_class,$race->race_id,$show_horse_note?$data->race_previous_note:'',$horse_id);
        }else if($date_order==='ASC'){
            $FUNC_print_note_row('後',$tr_class,$race->race_id,$show_race_note?$race->after_note:'');
            $FUNC_print_note_row('後',$tr_class,$race->race_id,$show_horse_note?$data->race_after_note:'',$horse_id);
        }
    ?>
    <?php if($date_order=='DESC'):// 空行の追加 ?>
        <?=$FUNC_print_empty_row($data->non_registered_prev_race_number,$race->race_id)?>
    <?php endif; ?>
<?php endforeach; ?>
</table>
<form id="show_mode_switch" method="get" action="#" style="margin-top: 4px;padding-left:0.5em; border:solid 1px #999; font-size:90%" oncontextmenu="return false;">
    表示切替：
    <input type="button" class="toggle" value="全選択・全解除" onclick="toggleCheckboxes('#show_mode_switch input[type=checkbox]');">
    <label oncontextmenu="uncheckAndCheck('#show_mode_switch input[type=checkbox]','input[name=show_horse_note]');">
        <input type="checkbox" name="show_horse_note" value="1"<?=!$show_horse_note?'':' checked'?>>競走馬メモ
    </label>
    ｜<label oncontextmenu="uncheckAndCheck('#show_mode_switch input[type=checkbox]','input[name=show_race_note]');">
        <input type="checkbox" name="show_race_note" value="1"<?=!$show_race_note?'':' checked'?>>レースメモ
    </label>
    <?php if($registration_only_race_is_exists||$show_registration_only):?>
        ｜<label oncontextmenu="uncheckAndCheck('#show_mode_switch input[type=checkbox]','input[name=show_registration_only]');">
            <input type="checkbox" name="show_registration_only" value="1"<?=!$show_registration_only?'':' checked'?>>非出走レース
        </label>
    <?php endif;?>
    &nbsp;<input type="submit" value="切替実行">
    <?php
        $params= array_diff(array_diff_key($page_urlparam->toArray(),[
            'show_horse_note'=>false,
            'show_race_note'=>false,
            'show_registration_only'=>false,
        ]),[0,'',false]);
        foreach($params as $key => $val){
            MkTagInput::Hidden($key,$val)->print();
        }
    ?>
</form>
<a id="under_results_table"></a>
<?php
    $tpl=new TemplateImporter('horse/horse_page-profile_2.inc.php');
    if($tpl->fileExists()){
        print('<hr>');
        include $tpl;
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
    <?php include (new TemplateImporter('horse/horse_page-edit_menu.inc.php'));?>
<?php endif; ?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>