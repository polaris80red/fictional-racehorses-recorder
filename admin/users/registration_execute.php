<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
InAppUrl::init(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$base_title="ユーザーアカウント";
$page->title="{$base_title}登録：処理実行";

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
    if(!(new FormCsrfToken())->isValid()){
        ELog::error($page->title.": CSRFトークンエラー|".__FILE__);
        $page->addErrorMsg("登録編集フォームまで戻り、内容確認からやりなおしてください（CSRFトークンエラー）");
        break;
    }
    $pdo=getPDO();
    $inputId=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT)?:null;
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
    if($password!==''){
        $form_item->password_hash=password_hash($password,PASSWORD_DEFAULT);
    }
    $form_item->display_name=filter_input(INPUT_POST,'display_name');
    $form_item->role=filter_input(INPUT_POST,'role',FILTER_VALIDATE_INT);
    $login_enabled_until=(string)filter_input(INPUT_POST,'login_enabled_until');
    $form_item->login_enabled_until=null;
    $datetime=$login_enabled_until===''?false:DateTime::createFromFormat('Y-m-d H:i:s',$login_enabled_until);
    if($datetime){
        $form_item->login_enabled_until=$datetime->format('Y-m-d H:i:s');
    }
    if(filter_input(INPUT_POST,'login_url_token_generate',FILTER_VALIDATE_BOOL)){
        $bytes=16;
        $maxRetry=100;
        $i=0;
        do{
            $token = base64_encode(random_bytes($bytes));
            $token = strtr($token, '+=/', '--.');
            if(Users::getByToken($pdo,$token)===false){
                $form_item->login_url_token=$token;
                break;
            }
            $i++;
        }while($i<$maxRetry);
        if($i>=$maxRetry){
            $page->addErrorMsg("自動生成トークンの重複再生成の回数が規定を超えたため中止しました");
            break;
        }
    }else{
        // 手動指定の場合
        $form_item->login_url_token=(string)filter_input(INPUT_POST,'login_url_token');
        $tokenCheckUser=Users::getByToken($pdo,$form_item->login_url_token);
        if($form_item->login_url_token && $tokenCheckUser && $tokenCheckUser->id!==$form_item->id){
            $page->addErrorMsg("トークンが既存ユーザーと重複しています");
            break;
        }
    }
    $form_item->is_enabled=filter_input(INPUT_POST,'is_enabled',FILTER_VALIDATE_BOOL)?1:0;
    if(!$form_item->validate()){
        $page->addErrorMsgArray($form_item->errorMessages);
        break;
    }
}while(false);
$page->renderErrorsAndExitIfAny();

$form_item->updated_by=$currentUser->getId();
$form_item->updated_at=PROCESS_STARTED_AT;
if($editMode){
    // 編集モード
    $result = ($TableClass)::UpdateFromRowObj($pdo,$form_item);
}else{
    // 新規登録モード
    $form_item->created_by = $form_item->updated_by;
    $form_item->created_at = $form_item->updated_at;
    $result = ($TableClass)::InsertFromRowObj($pdo,$form_item);
}
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
<p>登録が完了しました。</p>
<a href="./list.php">一覧へ戻る</a>
<form>
<table class="edit-form-table">
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
</table>
<?php if($form_item->login_url_token):?>
<table class="edit-form-table">
<tr>
    <th>専用ログインURL</th>
    <td><input type="button" value="クリップボードにコピー" onclick="copyToClipboard('#login_url');"></td>
</tr>
<tr>
    <?php
    $url=getSignInURL(3,['t'=>$form_item->login_url_token]);
    ?>
    <td colspan="2">
        <?=h($url)?><input type="hidden" id="login_url" value="<?=h($url)?>"><br>
    </td>
</tr>
</table>
<?php endif;?>
</form>
<a href="./list.php">一覧へ戻る</a>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>