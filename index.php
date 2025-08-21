<?php
session_start();
require_once __DIR__.'/libs/init.php';
defineAppRootRelPath();
$page=new Page();
$setting=new Setting();
$page->setSetting($setting);
$page->title=SITE_NAME;
$session=new Session();
$session->login_return_url='';
// 暫定でログイン＝編集可能
$page->is_editable=SESSION::is_logined();
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle();  ?></title>
    <meta charset="UTF-8">
    <?php $page->printBaseStylesheetLinks(); ?>
</head>
<body>
<header>
 <?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php $page->printTitle(); ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php HorseSearch::printSimpleForm($page); ?>
<hr>
<a href="<?php echo $page->to_horse_search_path; ?>?reset=true">競走馬検索</a><br>
<a href="<?php echo APP_ROOT_REL_PATH; ?>race/search.php?search_reset=1">レース検索</a><br>
<hr>
<?php
$year_min=$setting->select_zero_year - $setting->year_select_min_diff;
$year_max=$setting->select_zero_year + $setting->year_select_max_diff;
?>
<ul>
<?php for($i=$year_min; $i<=$year_max; $i++): ?>
    <?php $url_prefix=$page->to_race_list_path.'?search_word=&year='.$i; ?>
    <li>
        <a href="<?php echo $url_prefix; ?>&grade_reset=1&age_reset=1&session_is_not_update=1&search_detail_tgl_status=open"><?php
            echo $setting->getYearSpecialFormat($i);
            if($setting->year_view_mode==0){ echo "年"; }
            if($setting->year_view_mode==2){ echo "年"; }
        ?></a>｜
        <a href="<?php echo $url_prefix; ?>&grade_g1=1&grade_g2=&grade_g3=&grade_op=&grade_grade_jouken=&show_organization_jra=1&age_reset=1&limit=30&session_is_not_update=1&search_detail_tgl_status=open">[中央G1]</a>｜
        <a href="<?php echo $url_prefix; ?>&grade_g1=1&grade_g2=1&grade_g3=1&grade_gn=1&grade_op=&grade_grade_jouken=0&show_organization_jra=1&age_reset=1&limit=150&session_is_not_update=1&search_detail_tgl_status=open">[中央重賞]</a>｜
    </li>
<?php endfor; ?>
</ul>
<?php if($page->is_editable){ ?>
<hr>
<a href="<?php echo APP_ROOT_REL_PATH; ?>horse/form.php">競走馬新規登録</a><br>
<a href="<?php echo APP_ROOT_REL_PATH; ?>race/result/form.php">レース結果新規登録</a><br>
<?php } ?>
<hr>
<a href="<?php echo APP_ROOT_REL_PATH; ?>race/list/access_history.php">最近アクセスしたレースの一覧</a><br>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>