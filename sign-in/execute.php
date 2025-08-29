<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$session=new Session();
$return_url=(string)$session->login_return_url;
$id=filter_input(INPUT_POST,'id');
$password=filter_input(INPUT_POST,'password');

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
    if($id!==ADMINISTRATOR_USER){
        break;
    }
    if(ADMINISTRATOR_PASS==='' && $password===''){
        // パスワード未設定ではパスワードなしで通る

    }else if(!password_verify($password,ADMINISTRATOR_PASS)){
    //if($password!==ADMINISTRATOR_PASS){
        break;
    }
    $session->Login();
    $setting=new Setting();// ログイン処理時に初期化しなおす
    redirect_exit($page->to_app_root_path.$return_url);
}while(false);
redirect_exit('./?error=true');
