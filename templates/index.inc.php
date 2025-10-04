<?php
/**
 * トップページのコンテンツ部
 * @var Page $page
 * @var Session $session
 * @var Setting $setting
 */
?><?php HorseSearch::printSimpleForm($page); ?>
<hr>
<?php TemplateImporter::include('index.description.inc.php'); ?>
<?php
$year_min=$setting->select_zero_year - $setting->year_select_min_diff;
$year_max=$setting->select_zero_year + $setting->year_select_max_diff;
?>
<ul>
<?php
$race_list_url='race/list/';
$url_param=['session_is_not_update'=>1,'search_detail_tgl_status'=>'open'];
?>
<?php for($i=$year_min; $i<=$year_max; $i++): ?>
    <?php $url_param['year']=$i; ?>
    <li>
        <a href="<?=h(InAppUrl::to($race_list_url,$url_param))?>"><?php
            echo $setting->getYearSpecialFormat($i);
            if($setting->year_view_mode==0){ echo "年"; }
            if($setting->year_view_mode==2){ echo "年"; }
        ?></a>｜
        <a href="<?=h(InAppUrl::to($race_list_url,array_merge($url_param,['grade_g1'=>1,'show_organization_jra'=>1,'show_empty'=>1,'limit'=>30])))?>">[中央G1]</a>｜
        <a href="<?=h(InAppUrl::to($race_list_url,array_merge($url_param,['grade_g1'=>1,'grade_g2'=>1,'grade_g3'=>1,'show_organization_jra'=>1,'limit'=>150])))?>">[中央重賞]</a>｜
        <a href="<?=h(InAppUrl::to("horse/search/",['birth_year'=>($i-3)]))?>">[世代馬]</a>
    </li>
<?php endfor; ?>
</ul>
<hr>
<a href="<?php echo $page->to_horse_search_path; ?>?reset=true">競走馬検索</a><br>
<a href="<?=h(InAppUrl::to('race/search.php?',['search_reset'=>1]))?>">レース検索</a><br>
<?php if($page->is_editable): ?>
<hr>
<a href="<?=h(InAppUrl::to('horse/manage/edit/'))?>">競走馬新規登録</a><br>
<a href="<?=h(InAppUrl::to('race/manage/edit/'))?>">レース結果新規登録</a><br>
<?php endif; ?>
<hr>
<a href="<?=h(InAppUrl::to('race/list/access_history.php'))?>">最近アクセスしたレースの一覧</a><br>
<?php if(ENABLE_ACCESS_COUNTER): ?>
<hr>
累計アクセスランキング：<a href="<?=h(InAppUrl::to('ranking/horse.php',['limit'=>20]))?>">競走馬</a>
｜<a href="<?=h(InAppUrl::to('ranking/race.php',['limit'=>20]))?>">レース結果</a><br>
<?php endif; ?>