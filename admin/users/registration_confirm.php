<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="ユーザーアカウント";
$page->title="{$base_title}登録：内容確認";
$page->ForceNoindex();

if(!Session::isLoggedIn()){ $page->exitToHome(); }

$pdo=getPDO();
$inputId=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);

$editMode=($inputId>0);
$TableClass=Users::class;
$TableRowClass=$TableClass::ROW_CLASS;

if($editMode){
    $page->title.="（編集）";
    $form_item=($TableClass)::getById($pdo,$inputId);
    if($form_item===false){
        $page->addErrorMsg("ID '{$inputId}' が指定されていますが該当するレコードがありません");
    }
}else{
    $form_item=new ($TableRowClass)();
}

$form_item->username=filter_input(INPUT_POST,'username');
$password=(string)filter_input(INPUT_POST,'password');
$password2=(string)filter_input(INPUT_POST,'password_2');
if($password !== filter_input(INPUT_POST,'password_2')){
    $page->addErrorMsg('パスワード再入力が一致しません');
}
$form_item->display_name=filter_input(INPUT_POST,'display_name');
$form_item->role=filter_input(INPUT_POST,'role',FILTER_VALIDATE_INT);
$login_enabled_until=(string)filter_input(INPUT_POST,'login_enabled_until');
$form_item->login_enabled_until=null;
$datetime=$login_enabled_until===''?false:DateTime::createFromFormat('Y-m-d H:i:s',$login_enabled_until.' 23:59:59');
if($datetime){
    $form_item->login_enabled_until=$datetime->format('Y-m-d H:i:s');
}
$form_item->is_enabled=filter_input(INPUT_POST,'is_enabled',FILTER_VALIDATE_BOOL)?1:0;

do{
    if(!$form_item->validate()){
        $page->addErrorMsgArray($form_item->errorMessages);
        break;
    }
}while(false);
if($page->error_exists){
    $page->printCommonErrorPage();
    exit;
}
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle();  ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink('js/functions.js'); ?>
<style>
    select{
        height: 2em;
    }
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php $page->printTitle(); ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<form method="post" action="./registration_execute.php">
<table class="edit-form-table">
<tr>
    <th>ID</th>
    <td><?php
        print_h($form_item->id?:"新規登録");
        HTPrint::Hidden('id',$form_item->id);
    ?></td>
</tr>
<tr>
    <th>ログインユーザー名</th>
    <td><?php HTPrint::HiddenAndText('username',$form_item->username); ?></td>
</tr>
<tr>
    <th>パスワード</th>
    <td><?php HTPrint::Hidden('password',$password); ?><?=str_repeat('*',mb_strlen($password))?></td>
</tr>
<tr>
    <th>表示名</th>
    <td><?php HTPrint::HiddenAndText('display_name',$form_item->display_name); ?></td>
</tr>
<tr>
    <th>表示名</th>
    <td>
        <?php HTPrint::Hidden('role',$form_item->role);?>
        <?=h(Role::RoleInfoList[$form_item->role]['name']??'')?>
    </td>
</tr>
<tr>
    <th>ログイン可能期限</th>
    <?php
        $datetime=Datetime::createFromFormat('Y-m-d H:i:s',$form_item->login_enabled_until??'');
        $dateStr=$datetime===false?'':$datetime->format('Y-m-d');
    ?>
    <td>
        <?php HTPrint::Hidden('login_enabled_until',$datetime===false?'':$datetime->format('Y-m-d H:i:s')); ?>
        <?=$datetime===false?'':$datetime->format('Y-m-d')?>
    </td>
</tr>
<tr>
    <th>利用可否</th>
    <td><?php
        HTPrint::Hidden('is_enabled',$form_item->is_enabled);
        print $form_item->is_enabled?'有効':'無効';
    ?></td>
</tr>
<tr><td colspan="2" style="text-align: left;"><input type="submit" value="登録実行"></td></tr>
</table>
<?php (new FormCsrfToken())->printHiddenInputTag(); ?>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>