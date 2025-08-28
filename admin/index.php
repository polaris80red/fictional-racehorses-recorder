<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
defineAppRootRelPath(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->ForceNoindex();
$page->title="管理画面 - ".SITE_NAME;
$session=new Session();

if(!$session->is_logined()){
    header('Location: '.APP_ROOT_REL_PATH.'sign-in/');
}
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?></title>
    <meta charset="UTF-8">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
<style>
    th { background-color: #EEE; }
    td a { text-decoration: none; }
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php $page->printTitle(); ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<table>
    <tr>
        <th>項目</th>
        <th>概要</th>
    </tr>
    <tr>
        <td><?=(new MkTagA("表示設定",APP_ROOT_REL_PATH.'setting/'));?></td>
        <td>表示対象のワールドや年の範囲・日付形式などを設定します</td>
    </tr>
    <tr>
        <td><?=(new MkTagA("ワールド設定",APP_ROOT_REL_PATH.'admin/world/list.php'));?></td>
        <td>レース・競走馬の所属先を作成・管理します</td>
    </tr>
    <tr>
        <td><?=(new MkTagA("ストーリー設定",APP_ROOT_REL_PATH.'admin/world_story/list.php'));?></td>
        <td>表示設定の保存先になる項目を管理します</td>
    </tr>
    <tr>
        <td colspan="2"></td>
    </tr>
    <tr>
        <td><?=(new MkTagA("競馬場マスタ管理",APP_ROOT_REL_PATH.'admin/race_course/list.php'));?></td>
        <td>競馬場データを管理します</td>
    </tr>
    <tr>
        <td><?=(new MkTagA("所属マスタ管理",APP_ROOT_REL_PATH.'admin/affiliation/list.php'));?></td>
        <td></td>
    </tr>
    <tr>
        <td colspan="2"></td>
    </tr>
    <tr>
        <td><?=(new MkTagA("レース格付マスタ管理",APP_ROOT_REL_PATH.'admin/race_grade/list.php'));?></td>
        <td></td>
    </tr>
    <tr>
        <td><?=(new MkTagA("馬齢条件マスタ管理",APP_ROOT_REL_PATH.'admin/race_category_age/list.php'));?></td>
        <td></td>
    </tr>
    <tr>
        <td><?=(new MkTagA("性別条件マスタ設定",APP_ROOT_REL_PATH.'admin/race_category_sex/list.php'));?></td>
        <td></td>
    </tr>
    <tr>
        <td><?=(new MkTagA("レース週マスタ設定",APP_ROOT_REL_PATH.'admin/race_week/list.php'));?></td>
        <td>正確な日付無しのレース開催週の設定を調整します</td>
    </tr>
    <tr>
        <td><?=(new MkTagA("レース特殊結果マスタ設定",APP_ROOT_REL_PATH.'admin/race_special_results/list.php'));?></td>
        <td></td>
    </tr>
    <tr>
        <td colspan="2"></td>
    </tr>
    <tr>
        <td><?=(new MkTagA("テーマ設定",APP_ROOT_REL_PATH.'admin/themes/list.php'));?></td>
        <td>配色CSSファイルの保存先を作成・管理します</td>
    </tr>
</table>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>
