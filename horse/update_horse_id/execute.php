<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="競走馬ID一括修正・実行";
$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$horse_id=(string)filter_input(INPUT_POST,'horse_id');
$new_horse_id=(string)filter_input(INPUT_POST,'new_horse_id');

$pdo= getPDO();
# 対象取得
do{
    if(!(new FormCsrfToken())->isValid()){
        Elog::error($page->title.": CSRFトークンエラー|".__FILE__);
        $page->addErrorMsg("入力フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
        break;
    }
    if($horse_id==''){
        $page->addErrorMsg('元ID未入力');
    }
    if($new_horse_id==''){
        $page->addErrorMsg('新ID未入力');
    }
    if($horse_id!==htmlspecialchars($horse_id)){
        $page->addErrorMsg('元IDに特殊文字');
    }
    if($new_horse_id!==htmlspecialchars($new_horse_id)){
        $page->addErrorMsg('新IDに特殊文字');
    }
    if($page->error_exists){ break; }
    $horse_data=new Horse();
    $horse_data->setDataById($pdo,$horse_id);
    if(!$horse_data->record_exists){
        $page->addErrorMsg('元ID馬情報取得失敗');
        $page->addErrorMsg("入力元ID：{$horse_id}");
    }
    $new_id_horse_data=new Horse();
    $new_id_horse_data->setDataById($pdo,$new_horse_id);
    if($new_id_horse_data->record_exists){
        $page->addErrorMsg('新IDが既に存在');
        $page->addErrorMsg("入力新ID：{$new_horse_id}");
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
$updater=new IdUpdater($pdo,$horse_id,$new_horse_id);
$updater->addUpdateTarget(Horse::TABLE,'horse_id');
$updater->addUpdateTarget(RaceResultDetail::TABLE,'horse_id');
$updater->addUpdateTarget(Horse::TABLE,'mare_id');
$updater->addUpdateTarget(Horse::TABLE,'sire_id');
$updater->addUpdateTarget(HorseTag::TABLE,'horse_id');
$updater->execute();

?><!DOCTYPE html>
<html>
<head>
    <title><?php echo $page->title; ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
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
<h1 class="page_title"><?php echo $page->title; ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<form action="" method="post">
<table>
<tr>
    <th>対象馬</th>
    <td><?php echo $horse_data->name_ja."/".$horse_data->name_en; ?></td>
</tr>
<tr>
    <th>置換前競走馬ID</th>
    <td><?php printHiddenAndText('horse_id',$horse_id); ?></td>
</tr>
<tr>
    <th>置換後競走馬ID</th>
    <td><?php printHiddenAndText('new_horse_id',$new_horse_id); ?></td>
</tr>
</table>
<hr>
<a href="<?php echo $page->to_app_root_path; ?>horse/?horse_id=<?php echo $new_horse_id; ?>">馬情報に移動</a>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>
