<?php
session_start();
require_once dirname(__DIR__,1).'/libs/init.php';
InAppUrl::init(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="ユーザー情報設定：処理実行";

if(!Session::isLoggedIn()){ $page->exitToHome(); }

$pdo=getPDO();
$inputId=Session::currentUser()->getId();

$TableClass=Users::class;
$TableRowClass=$TableClass::ROW_CLASS;
$form_item=($TableClass)::getById($pdo,$inputId);
if(!$form_item){
    $page->addErrorMsg("ID '{$inputId}' が指定されていますが該当するレコードがありません");
}
$password=(string)filter_input(INPUT_POST,'password');
if($password!==''){
    $form_item->password_hash=password_hash($password,PASSWORD_DEFAULT);
}
if($password==''){
    $page->addErrorMsg("新パスワード未送信");
}
do{
    if(!(new FormCsrfToken())->isValid()){
        ELog::error($page->title.": CSRFトークンエラー|".__FILE__);
        $page->addErrorMsg("登録編集フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
        break;
    }
    if(!$form_item->validate()){
        $page->addErrorMsgArray($form_item->errorMessages);
        break;
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
$form_item->updated_by=Session::currentUser()->getId();
$form_item->updated_at=PROCESS_STARTED_AT;
$result = ($TableClass)::UpdateFromRowObj($pdo,$form_item);
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?=h($page->renderTitle())?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink('js/functions.js'); ?>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
ユーザー情報を更新しました。
<form method="post" action="./registration_execute.php">
<table class="edit-form-table">
<tr>
    <th>ログインユーザー名</th>
    <td><?=h($form_item->username)?></td>
</tr>
<tr>
    <th>パスワード</th>
    <td><?php HTPrint::Hidden('password',$password); ?><?=str_repeat('*',mb_strlen($password))?></td>
</tr>
</table>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>