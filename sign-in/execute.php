<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$session=new Session();
$return_url=(string)$session->login_return_url;
$id=(string)filter_input(INPUT_POST,'id');
$password=(string)filter_input(INPUT_POST,'password');
$pdo=getPDO();

// ALLOW_REMOTE_EDITOR_LOGIN で許可されていない場合、localhost以外からのログインは拒否する
(function(){
    if(READONLY_MODE){
        header('HTTP/1.1 403 Forbidden');
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'Access denied: 読み取り専用モードのためログインできません。';
        exit;        
    }
    if(ALLOW_REMOTE_EDITOR_LOGIN===true){
        if(ADMINISTRATOR_PASS===''){
            header('HTTP/1.1 403 Forbidden');
            header('Content-Type: text/plain; charset=UTF-8');
            echo 'Access denied: リモートIPからのアクセスを許可している場合、管理者パスワード未設定ではログインできません。';
            exit;
        }
        return;
    }
    if(is_remote_access()){
        header('HTTP/1.1 403 Forbidden');
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'Access denied: 実行中のコンピューター以外からのログインは設定で禁止されています。';
        exit;
    }
})();
$error_exists=false;

$page->title="ログイン実行";
do{
    if((new FormCsrfToken())->isValid()==false){
        break;
    }
    if($id===''){
        break;
    }
    try {
        // ユーザーテーブル自体が未作成の場合も設定ファイルの固定管理者での最低限の使用は可能にする
        $user=Users::getByUsername($pdo,$id);
    } catch (Exception $e) {
        $user=false;
    }
    if($id===ADMINISTRATOR_USER){
        // SuperAdminの場合のログイン処理
        if(ADMINISTRATOR_PASS==='' && $password===''){
            // パスワード未設定ではパスワードなしで通す
        }else if(!password_verify($password,ADMINISTRATOR_PASS)){
            break;
        }
        Session::loginSuperAdmin($user?:null);
    }else{
        if(!$user){
            // 該当ユーザーなし
            break;
        }
        if(!$user->is_enabled){
            // 無効化されている
            break;
        }
        if($user->login_enabled_until!=null){
            $until=new DateTime($user->login_enabled_until);
            $now=new DateTime();
            if($now>$until){
                // 期限切れ
                break;
            }
        }
        if(!password_verify($password,$user->password_hash)){
            break;
        }
        Session::Login($user);
    }
    if($user){
        $user->last_login_at=PROCESS_STARTED_AT;
        Users::UpdateFromRowObj($pdo,$user);
    }
    $setting=new Setting();// ログイン処理時に初期化しなおす
    redirect_exit($page->to_app_root_path.$return_url);
}while(false);
redirect_exit('./?error=true');
