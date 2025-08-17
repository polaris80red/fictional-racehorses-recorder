<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
defineAppRootRelPath(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="競走馬情報";
$session=new Session();
// 暫定でログイン＝編集可能
$page->is_editable=Session::is_logined();

$page->error_return_url=APP_ROOT_REL_PATH."horse_search";
$page->error_return_link_text="競走馬検索に戻る";
$pdo= getPDO();

$is_edit_mode = false;
if(filter_input(INPUT_GET,'mode')==='edit'){
    $is_edit_mode = true;
}
$is_edit_mode=true;
if(empty($_GET['horse_id'])){
    $page->error_msgs[]="競走馬ID未指定";
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
$horse=new Horse();
$horse->setDataById($pdo, $horse_id);
if(!$horse->record_exists){
    $page->error_msgs[]="競走馬情報取得失敗";
    $page->error_msgs[]="入力ID：{$horse_id}";
    $page->printCommonErrorPage();
    exit;
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
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink("js/functions.js"); ?>
<style>
.horse_base_data th{ min-width:80px; }
.horse_base_data td{ min-width:160px; }
.disabled_row{ background-color: #dddddd; }

td.track_condition{ text-align:center; }
td.grade{ text-align:center; }
td.number_of_starters{ text-align:right; }
td.frame_number{ text-align:center; }
td.favourite{ text-align:right; }
td.result_number{ text-align:right; }
table.horse_base_data a {text-decoration: none;}

</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php $page->printTitle(); ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php
if($horse->name_ja){
    echo "<span style=\"font-size:1.2em;\">".$horse->name_ja."</span>　";
}else if($horse->name_en===''){
    echo "<span style=\"font-size:1.2em;\">".ANNONYMOUS_HORSE_NAME."</span>";
}
if($horse->name_en){
    echo "<span style=\"font-size:1.1em;\">{$horse->name_en}</span>";
}
if($horse->birth_year>0){
    echo "（{$horse->birth_year}）\t";
}else{
    echo " ";
}
echo "{$horse->color} {$sex_str}";
?>
<hr>
<table class="horse_base_data">
    <tr>
        <th>馬名</th>
        <td style="min-width: 12em;"><?php print_h($horse->name_ja); ?></td>
    </tr>
    <tr>
        <th>馬名(欧字)</th>
        <td><?php print_h($horse->name_en); ?></td>
    </tr>
    <tr>
        <th>所属</th>
        <td><?php print_h($horse->tc); ?></td>
    </tr>
    <tr>
        <th><?php print($setting->birth_year_mode===0?"生年":"世代"); ?></th>
        <td><?php
        if($horse->birth_year>0){
            $birth_year_search=new HorseSearch();
            $birth_year_search->birth_year=$horse->birth_year;
            $url =$page->to_horse_search_path.'?'.$birth_year_search->getUrlParam();
            $birth_year_str=$setting->getBirthYearFormat($horse->birth_year);
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
        if($horse->sire_id!=''){
            $sire_search=new HorseSearch();
            $sire_search->sire_id=$horse->sire_id;
            $sire_search->order='birth_year__asc';
            $url .= $sire_search->getUrlParam();
            (new MkTagA(h($horse->sire_name)?:ANNONYMOUS_HORSE_NAME,"?horse_id={$horse->sire_id}"))->print();
            $a_tag_sanku->href($url)->print();
        } else if($horse->sire_name!=''){
            $sire_search=new HorseSearch();
            $sire_search->sire_name=$horse->sire_name;
            $sire_search->order='birth_year__asc';
            $url .= $sire_search->getUrlParam();
            (new MkTagA(h($horse->sire_name),$url))->print();
            $a_tag_sanku->href($url)->print();
        }
        ?></td>
    </tr>
    <tr>
        <th>母</th>
        <td><?php
        $url =$page->to_horse_search_path.'?';
        if($horse->mare_id!=''){
            $mare_search=new HorseSearch();
            $mare_search->mare_id=$horse->mare_id;
            $mare_search->order='birth_year__asc';
            $url .= $mare_search->getUrlParam();
            (new MkTagA(h($horse->mare_name)?:ANNONYMOUS_HORSE_NAME,"?horse_id={$horse->mare_id}"))->print();
            $a_tag_sanku->href($url)->print();
        } else if($horse->mare_name!=''){
            $mare_search=new HorseSearch();
            $mare_search->mare_name=$horse->mare_name;
            $mare_search->order='birth_year__asc';
            $url .= $mare_search->getUrlParam();
            (new MkTagA(h($horse->mare_name),$url))->print();
            $a_tag_sanku->href($url)->print();
        }
        ?></td>
    </tr>
    <tr>
        <th>母の父</th>
        <td><?php
        if($horse->bms_name!=''){
            $bms_name_search=new HorseSearch();
            $bms_name_search->bms_name=$horse->bms_name;
            $url =$page->to_horse_search_path.'?'.$bms_name_search->getUrlParam();
            (new MkTagA(h($horse->bms_name),$url))->print();
        }
        ?></td>
    </tr>
</table>
<hr>
<span><?php
echo $race_history->race_count_1."-";
echo $race_history->race_count_2."-";
echo $race_history->race_count_3."-";
?><span style="<?php echo $race_history->has_unregistered_race_results?'color:#999999;':''; ?>"><?php
echo $race_history->race_count_4+$race_history->race_count_5+$race_history->race_count_n." / ";
echo $race_history->race_count_all."戦";
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
    <th><?php
        if($setting->age_view_mode===Setting::AGE_VIEW_MODE_UMAMUSUME
        || $setting->age_view_mode===Setting::AGE_VIEW_MODE_UMAMUSUME_S){
            print '人数';
        }else{
            print '頭数';
        }
    ?></th>
    <th>枠</th>
    <th>人気</th><th>着順</th><th>斤量</th>
    <th>1着馬<span class="nowrap">(2着馬)</span></th><th>　</th>
<?php if($page->is_editable): ?><th></th><?php endif; ?>
</tr><?php

$FUNC_print_empty_row=function($non_registered_prev_race_number,$next_race_id='') use($page,$horse_id){
    if($non_registered_prev_race_number>0){
        print "<tr><td style=\"color:#999999;\">（{$non_registered_prev_race_number}戦～）</td>";
        print "<td></td>"."<td></td>"."<td></td>"."<td></td>"."<td>……</td>"."<td></td>"."<td></td>"."<td></td>"."<td></td>"."<td></td><td></td><td></td>";
        if($page->is_editable){
            $url =APP_ROOT_REL_PATH."race/horse_result/form.php?";
            $url.="horse_id={$horse_id}";
            if($next_race_id!==''){
                $url.="&next_race_id={$next_race_id}";
            }
            print "<td><a href=\"{$url}\">新</td>";
        }
        print "</tr>\n";
    }
};
$registration_only_race_is_exists=false;
$latest_race_is_exists=false;
foreach ($race_history as $data) {
    $data=(array)$data;
    $tr_class=new Imploader(' ');

    if(empty($data['race_id'])){ continue; }
    if(!empty($session->latest_race['id'])&&
        $session->latest_race['id']===$data['race_id'])
    {
        $latest_race_is_exists=true;
    }
    // 空行の追加
    if($date_order=='ASC'){
        $FUNC_print_empty_row($data['non_registered_prev_race_number'],$data['race_id']);
    }
    // 特別登録のみのデータは表示フラグがなければスキップ
    $race_url_add_param='';
    if($data['is_registration_only']==1){
        $registration_only_race_is_exists=true;
        if(!$show_registration_only){
            continue;
        }else{
            $tr_class->add('disabled_row');
            $race_url_add_param='&show_registration_only=true';
        }
    }
    $tr_class->add('race_grade_'.$data['grade_css_class_suffix']);
    if($data['is_enabled']===0){ $tr_class->add('disabled_row'); }
    echo '<tr class="'.$tr_class.'">';

    $datetime=null;
    if($data['date']!=null && $data['is_tmp_date']==0){
        $datetime=new DateTime($data['date']);
    }
    $month=$data['month'];
    // ウマ娘ターン表記の場合は補正済み月を優先
    if($setting->horse_record_date==='umm' && $data['w_month']>0){
        $month=$data['w_month'];
    }
    $day=is_null($datetime)?0:(int)$datetime->format('d');
    $date_str=$setting->getRaceListDate([
        'year'=>$data['year'],
        'month'=>$month,
        'day'=>$month,$day,
        'turn'=>$data['umm_month_turn'],
        'age'=>$data['year']-$horse->birth_year]);
    $url = '';
    if($setting->horse_record_date==='umm'){
        if($data['umm_month_turn']>0){
            $url = $page->getTurnRaceListUrl($data['year'],$month,$data['umm_month_turn']);
        }
    }else if($datetime!==null){
        $url=$page->getDateRaceListUrl($datetime);
    }
    $date_str=(new MkTagA($date_str,$url))->get();
    echo "<td>{$date_str}</td>";

    $a_tag=new MkTagA($data['race_course_name']);
    if($datetime!==null){
        $a_tag->href($page->getDateRaceListUrl(
            $datetime,
            ['race_course_name'=>$data['race_course_name']]
        ));
    }
    echo "<td class=\"race_course\">{$a_tag}</td>";
    echo "<td class=\"distance\">{$data['course_type']}{$data['distance']}</td>";
    echo "<td class=\"track_condition\">{$data['track_condition']}</td>";

    echo "<td class=\"grade\">".($data['grade_short_name']??$data['grade'])."</td>";
    echo "<td class=\"race_name\">";
    echo '<a href="'.$page->getRaceResultUrl($data['race_id']).$race_url_add_param.'" title="'.h($data['race_name'].($data['caption']?'：'.$data['caption']:'')).'">';
    echo h($data['race_name']);
    echo "</a>\t";
    echo "</td>";

    $add_class=getResultClass($data['result_number']);
    echo "<td class=\"number_of_starters\">{$data['number_of_starters']}</td>";
    echo "<td class=\"frame_number\">{$data['frame_number']}</td>";
    echo "<td class=\"favourite favourite_{$data['favourite']}\">{$data['favourite']}</td>";
    $result_txt='';
    if($data['result_text']!=''){
        $result_txt=$data['result_text'];
    }else if($data['result_number']>0){
        if($data['result_before_demotion']>0){
            $result_txt.="<span title=\"※".$data['result_before_demotion']."位入線降着\">(降)</span>";
        }
        $result_txt.=$data['result_number']."着";
    }
    echo "<td class=\"result_number {$add_class}\">{$result_txt}</td>";
    echo "<td class=\"handicap\">{$data['handicap']}</td>";
    echo "<td class=\"r_horse\">";
    if($data['result_number']==1){ echo "("; }
    echo '<a href="'.APP_ROOT_REL_PATH.'horse/?horse_id='.$data['r_horse_id'].'">';
    echo ($data['r_name_ja']?:$data['r_name_en']);
    echo "</a>";
    if($data['result_number']==1){ echo ")"; }
    echo "</td>";
    $a_tag=new MkTagA();
    if($data['has_jra_thisweek']){
        $a_tag->setLinkText('記');
        $a_tag->href(APP_ROOT_REL_PATH."race/j_thisweek.php?race_id=".$data['race_id'].$race_url_add_param);
    }
    echo "<td>{$a_tag}</td>";
    if($page->is_editable){
        echo "<td class=\"edit_link\">";
        if(!empty($data['race_id'])){
            $url_param=(new UrlParams())->toString([
                'race_id'=>$data['race_id'],
                'horse_id'=>$horse->horse_id,
                'edit_mode'=>1,
            ]); 
            $url=APP_ROOT_REL_PATH."race/horse_result/form.php?{$url_param}";
            echo '<a href="'.$url.'">編</a>';
        }
        echo "</td>";
    }
    #echo "<td>".$data['race_id']."</td>";
    
    #echo "<pre>".var_dump($data)."</pre>"."\n";
    echo "</tr>\n";
    // 空行の追加
    if($date_order=='DESC'){
        $FUNC_print_empty_row($data['non_registered_prev_race_number'],$data['race_id']);
    }
}
?></table>
<?php if($page->is_editable): ?>
<hr><input type="button" id="edit_tgl" value="編集">
<input type="hidden" id="hiddden_horse_id" value="<?php echo $horse->horse_id; ?>">
<input type="button" value="競走馬IDをクリップボードにコピー" onclick="copyToClipboard('#hiddden_horse_id');">
(horse_id=<?php echo $horse->horse_id; ?>)
<input type="hidden" id="edit_menu_states" value="0">
<div class="edit_menu" style="display:none;">
<hr>
<?php $url=APP_ROOT_REL_PATH."horse/form.php?horse_id={$horse->horse_id}"; ?>
<a href="<?php echo $url; ?>">[この馬の情報を編集]</a>
　
<?php
    $url=APP_ROOT_REL_PATH."race/horse_result/form.php?horse_id={$horse->horse_id}";
    $a_tag=new MkTagA('[この馬の戦績を追加]');
        $a_tag->href($url);
    if($horse->birth_year==null){
        $a_tag->href('')->setStyle('text-decoration','line-through');
        $a_tag->title("生年仮登録馬のため戦績追加不可");
    }
    print $a_tag;
?>
<?php if(!empty($session->latest_race['id'])): ?>
<hr>
<?php
    $url=APP_ROOT_REL_PATH."race/horse_result/form.php?horse_id={$horse->horse_id}&race_id={$session->latest_race['id']}";
    $a_tag=new MkTagA('[最後に開いたレースにこの馬の戦績を追加]');
    $a_tag->href($url);
    if($horse->birth_year==null){
        $a_tag->href('')->setStyle('text-decoration','line-through');;
        $a_tag->title("生年仮登録馬のため戦績追加不可");
    }
    if($latest_race_is_exists===true){
        $a_tag->href('')->setStyle('text-decoration','line-through');;
        $a_tag->title("最後に開いたレースには既に登録されています");
    }
    print $a_tag."<br>\n";
?>
<?php $url=$page->getRaceResultUrl($session->latest_race['id']); ?>
（<a href="<?php echo $url; ?>"><?php echo $session->latest_race['year']." ".($session->latest_race['name']?:$session->latest_race['id']); ?></a>）
<?php endif; ?>
<hr>
<a href="./update_horse_id/form.php?horse_id=<?php echo $horse->horse_id; ?>">[競走馬ID修正]</a>
</div><!-- /.edit_menu -->
<script>
$(function() {
    //チェックボックス操作時
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
<?php if($horse->search_text!=''):?>
<hr>
<?php
        $search_text_search=new HorseSearch();
        $search_text=str_replace('　',' ',$horse->search_text);
        $search_texts=explode(' ',$search_text);
        $links=new Imploader('　');
        foreach($search_texts as $val){
            if($val===''){
                continue;
            }
            $search_text_search->search_text=$val;
            $url ="{$page->to_horse_search_path}?".$search_text_search->getUrlParam();
            $links->add("<a href=\"{$url}\">#{$val}</a>");
        }
        print($links);
?>
<?php endif; ?>
<hr>
<table class="horse_base_data">
<tr><th>馬名意味</th><td><?php print_h($horse->meaning); ?></td></tr>
<tr><th>備考</th><td><pre><?php print_h($horse->note); ?></pre></td></tr>
</table>
<?php
if($registration_only_race_is_exists||$show_registration_only){
    $a_tag=new MkTagA("特別登録のみのレースを".($show_registration_only?"非表示(現在:表示)":"表示(現在:非表示)")."");
    $a_tag->href("?".$page_urlparam->toString(['show_registration_only'=>!$show_registration_only]));
    $a_tag->print();
    print('<hr>');
}
?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>