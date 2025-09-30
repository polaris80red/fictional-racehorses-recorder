<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
InAppUrl::init(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="ユーザーアカウント";
$page->title="{$base_title}登録：内容確認";
$page->ForceNoindex();

if(!Session::isLoggedIn()){ $page->exitToHome(); }
$currentUser=Session::currentUser();
$TableClass=Users::class;
$TableRowClass=$TableClass::ROW_CLASS;

do{
    if(!$currentUser->canManageUser()){
        $page->setErrorReturnLink('管理画面に戻る',InAppUrl::to('admin/'));
        $page->addErrorMsg("ユーザー管理には管理者権限が必要です。");
        $page->printCommonErrorPage();
        break;
    }
    $pdo=getPDO();
    $inputId=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
    $editMode=($inputId>0);
    if($editMode){
        $page->title.="（編集）";
        $form_item=($TableClass)::getById($pdo,$inputId);
        if($form_item===false){
            $page->addErrorMsg("ID '{$inputId}' が指定されていますが該当するレコードがありません");
            break;
        }
    }else{
        $form_item=new ($TableRowClass)();
    }
    $form_item->username=filter_input(INPUT_POST,'username');
    $password=(string)filter_input(INPUT_POST,'password');
    $password2=(string)filter_input(INPUT_POST,'password_2');
    if($password !== filter_input(INPUT_POST,'password_2')){
        $page->addErrorMsg('パスワード再入力が一致しません');
        break;
    }
    $form_item->display_name=filter_input(INPUT_POST,'display_name');
    $form_item->role=filter_input(INPUT_POST,'role',FILTER_VALIDATE_INT);
    $login_enabled_until=(string)filter_input(INPUT_POST,'login_enabled_until');
    $form_item->login_enabled_until=null;
    $datetime=$login_enabled_until===''?false:DateTime::createFromFormat('Y-m-d H:i:s',$login_enabled_until.' 23:59:59');
    if($datetime){
        $form_item->login_enabled_until=$datetime->format('Y-m-d H:i:s');
    }
    $login_url_token=(string)filter_input(INPUT_POST,'login_url_token');
    $login_url_token_generate=false;
    if(filter_input(INPUT_POST,'login_url_token_generate',FILTER_VALIDATE_BOOL)){
        $login_url_token_generate=true;
        $form_item->login_url_token='';
    }else{
        $form_item->login_url_token=$login_url_token;
        $tokenCheckUser=Users::getByToken($pdo,$form_item->login_url_token);
        if($form_item->login_url_token && $tokenCheckUser && $tokenCheckUser->id!==$form_item->id){
            $page->addErrorMsg("トークンが既存ユーザーと重複しています");
            break;
        }
    }
    if($form_item->role===Role::GuestAuthor && $form_item->login_enabled_until==''){
        $page->addErrorMsg("ゲスト投稿者権限のユーザーには必ず期限を制限してください");
        break;
    }
    $form_item->is_enabled=filter_input(INPUT_POST,'is_enabled',FILTER_VALIDATE_BOOL)?1:0;
    if(!$form_item->validate()){
        $page->addErrorMsgArray($form_item->errorMessages);
        break;
    }
}while(false);
$page->renderErrorsAndExitIfAny();
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?=h($page->renderTitle())?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?=$page->renderBaseStylesheetLinks()?>
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
<h1 class="page_title"><?=h($page->title)?></h1>
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
    <th>専用ログインページ<br>URLトークン</th>
    <td>
        <?php HTPrint::Hidden('login_url_token',$form_item->login_url_token);?>
        <?php HTPrint::Hidden('login_url_token_generate',$login_url_token_generate);?>
        <?=h($login_url_token_generate?'自動生成する':$form_item->login_url_token)?>
    </td>
</tr>
<tr>
    <th>表示名</th>
    <td><?php HTPrint::HiddenAndText('display_name',$form_item->display_name); ?></td>
</tr>
<tr>
    <th>役割・権限</th>
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
<?=(new FormCsrfToken())?>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>