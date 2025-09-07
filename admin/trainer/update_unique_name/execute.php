<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
defineAppRootRelPath(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="調教師マスタ管理｜キー名称の一括変更：実行";
$page->ForceNoindex();
$session=new Session();
if(!Session::is_logined()){ $page->exitToHome(); }

$u_name=(string)filter_input(INPUT_POST,'u_name');
$new_unique_name=trim((string)filter_input(INPUT_POST,'new_unique_name'));

$pdo= getPDO();
# 対象取得
do{
    if(!(new FormCsrfToken())->isValid()){
        ELog::error($page->title.": CSRFトークンエラー|".__FILE__);
        $page->addErrorMsg("入力フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
        break;
    }
    if($u_name==''){
        $page->addErrorMsg('変換対象の名称が未入力');
    }
    if($new_unique_name==''){
        $page->addErrorMsg('新しい名称が未入力');
    }
    if($page->error_exists){ break; }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
$updater=new IdUpdater($pdo,$u_name,$new_unique_name);
$new_id_exists=$updater->new_id_exists(Trainer::TABLE,'unique_name');
if(!$new_id_exists){
    // UNIQUE制約があるため、存在しない場合のみunique_nameを変更
    $updater->addUpdateTarget(Trainer::TABLE,'unique_name');
}
$updater->addUpdateTarget(Horse::TABLE,'trainer_unique_name');
$updater->addUpdateTarget(RaceResults::TABLE,'trainer_unique_name');
$updater->execute();
?><!DOCTYPE html>
<html>
<head>
    <title><?php echo $page->title; ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php echo $page->title; ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<form action="" method="post">
<table class="edit-form-table">
<tr>
    <th>置換前</th>
    <td><?php HTPrint::HiddenAndText('race_id',$u_name); ?></td>
</tr>
<tr>
    <th>置換後</th>
    <td><?php HTPrint::HiddenAndText('new_race_id',$new_unique_name); ?></td>
</tr>
</table>
<hr>
<a href="../list.php">調教師マスタ一覧に移動</a>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>
