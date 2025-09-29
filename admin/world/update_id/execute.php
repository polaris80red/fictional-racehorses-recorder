<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
defineAppRootRelPath(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="ワールド管理｜IDの一括変更：実行";
$page->ForceNoindex();
$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }

$world_id=(string)filter_input(INPUT_POST,'world_id');
$new_world_id=trim((string)filter_input(INPUT_POST,'new_world_id'));

$pdo= getPDO();
# 対象取得
do{
    if(!Session::currentUser()->canManageSystemSettings()){
        header("HTTP/1.1 403 Forbidden");
        $page->addErrorMsg('システム設定管理権限がありません');
    }
    if(!(new FormCsrfToken())->isValid()){
        ELog::error($page->title.": CSRFトークンエラー|".__FILE__);
        $page->addErrorMsg("入力フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
        break;
    }
    if($world_id==''){
        $page->addErrorMsg('変換対象の名称が未入力');
    }
    if($new_world_id==''){
        $page->addErrorMsg('新しい名称が未入力');
    }
    $updater=new IdUpdater($pdo,$world_id,$new_world_id,PDO::PARAM_INT);
    if($updater->new_id_exists(World::TABLE,'id')){
        $page->addErrorMsg('新しいIDのワールドが既に存在します');
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
$updater->addUpdateTarget(World::TABLE,'id');
$updater->addUpdateTarget(Horse::TABLE,'world_id');
$updater->addUpdateTarget(Race::TABLE,'world_id');
$updater->execute();
?><!DOCTYPE html>
<html>
<head>
    <title><?=h($page->renderTitle())?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<form action="" method="post">
<table class="edit-form-table">
<tr>
    <th>置換前</th>
    <td><?php HTPrint::HiddenAndText('race_id',$world_id); ?></td>
</tr>
<tr>
    <th>置換後</th>
    <td><?php HTPrint::HiddenAndText('new_race_id',$new_world_id); ?></td>
</tr>
</table>
<hr>
<a href="../list.php">ワールド一覧に移動</a>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>
