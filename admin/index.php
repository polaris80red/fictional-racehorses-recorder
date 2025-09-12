<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
InAppUrl::init(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="管理画面 - ".SITE_NAME;
$page->ForceNoindex();
$session=new Session();

if(!$session->is_logined()){
    header('Location: '.InAppUrl::to('sign-in/'));
}
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
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
        <td><?=(new MkTagA("表示設定",InAppUrl::to('setting/')));?></td>
        <td>どのワールドを表示するかや、年の範囲・日付形式などを設定します</td>
    </tr>
    <tr>
        <td colspan="2"></td>
    </tr>
    <tr>
        <td><?=(new MkTagA("ワールド設定",InAppUrl::to('admin/world/list.php')));?></td>
        <td>レース・競走馬の所属先を作成・管理します</td>
    </tr>
    <tr>
        <td><?=(new MkTagA("ストーリー設定",InAppUrl::to('admin/world_story/list.php')));?></td>
        <td>表示設定の保存先になる項目を管理します</td>
    </tr>
    <tr>
        <td colspan="2"></td>
    </tr>
    <tr>
        <td><?=(new MkTagA("テーマ設定",InAppUrl::to('admin/themes/list.php')));?></td>
        <td>配色CSSファイルやテンプレートの保存先を作成・管理します</td>
    </tr>
    <tr>
        <th colspan="2">競走馬関連マスタ管理</th>
    </tr>
    <tr>
        <td><?=(new MkTagA("所属マスタ管理",InAppUrl::to('admin/affiliation/list.php')));?></td>
        <td></td>
    </tr>
    <tr>
        <td><?=(new MkTagA("騎手マスタ管理",InAppUrl::to('admin/jockey/list.php')));?></td>
        <td></td>
    </tr>
    <tr>
        <td><?=(new MkTagA("調教師マスタ管理",InAppUrl::to('admin/trainer/list.php')));?></td>
        <td></td>
    </tr>
    <tr>
        <td><?=(new MkTagA("レース特殊結果マスタ設定",InAppUrl::to('admin/race_special_results/list.php')));?></td>
        <td></td>
    </tr>
    <tr>
        <th colspan="2">レース情報関連マスタ</th>
    </tr>
    <tr>
        <td><?=(new MkTagA("競馬場マスタ管理",InAppUrl::to('admin/race_course/list.php')));?></td>
        <td>競馬場データを管理します</td>
    </tr>
    <tr>
        <td><?=(new MkTagA("レース格付マスタ管理",InAppUrl::to('admin/race_grade/list.php')));?></td>
        <td></td>
    </tr>
    <tr>
        <td colspan="2"></td>
    </tr>
    <tr>
        <td><?=(new MkTagA("馬齢条件マスタ管理",InAppUrl::to('admin/race_category_age/list.php')));?></td>
        <td></td>
    </tr>
    <tr>
        <td><?=(new MkTagA("性別条件マスタ設定",InAppUrl::to('admin/race_category_sex/list.php')));?></td>
        <td></td>
    </tr>
    <tr>
        <td><?=(new MkTagA("レース週マスタ設定",InAppUrl::to('admin/race_week/list.php')));?></td>
        <td>正確な日付無しのレース開催週の設定を調整します</td>
    </tr>
</table>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>
