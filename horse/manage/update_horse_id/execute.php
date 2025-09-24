<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
defineAppRootRelPath(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="競走馬ID一括修正・実行";
$page->ForceNoindex();
$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }

$horse_id=(string)filter_input(INPUT_POST,'horse_id');
$new_horse_id=(string)filter_input(INPUT_POST,'new_horse_id');

$pdo= getPDO();
# 対象取得
do{
    if(!(new FormCsrfToken())->isValid()){
        ELog::error($page->title.": CSRFトークンエラー|".__FILE__);
        $page->addErrorMsg("入力フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
        break;
    }
    if($horse_id==''){
        $page->addErrorMsg('元ID未入力');
        break;
    }
    if($new_horse_id==''){
        $page->addErrorMsg('新ID未入力');
        break;
    }
    $horse_data=Horse::getByHorseId($pdo,$horse_id);
    if(!$horse_data){
        $page->addErrorMsg('元ID馬情報取得失敗');
        $page->addErrorMsg("入力元ID：{$horse_id}");
        break;
    }
    $horse_data->horse_id = $new_horse_id;
    $horse_data->validate();
    if($horse_data->hasErrors){
        $page->addErrorMsg('新IDエラー');
        $page->addErrorMsgArray($horse_data->errorMessages);
        break;
    }
    $new_id_horse=Horse::getByHorseId($pdo,$new_horse_id);
    if($new_id_horse!==false){
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
$updater->addUpdateTarget(RaceResults::TABLE,'horse_id');
$updater->addUpdateTarget(Horse::TABLE,'mare_id');
$updater->addUpdateTarget(Horse::TABLE,'sire_id');
$updater->addUpdateTarget(HorseTag::TABLE,'horse_id');
$updater->execute();

?><!DOCTYPE html>
<html>
<head>
    <title><?=h($page->title)?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
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
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<form action="" method="post">
<table>
<tr>
    <th>対象馬</th>
    <td><?=h(implode('/',array_diff([$horse_data->name_ja,$horse_data->name_en],[''])))?></td>
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
<a href="<?php echo $page->to_app_root_path; ?>horse/?horse_id=<?=h(urlencode($new_horse_id))?>">馬情報に移動</a>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>
