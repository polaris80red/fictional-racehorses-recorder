<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
InAppUrl::init(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$session=new Session();
$return_url=(string)$session->login_return_url;
$id=(string)filter_input(INPUT_POST,'id');
$password=(string)filter_input(INPUT_POST,'password');
$login_url_token=(string)filter_input(INPUT_POST,'login_url_token');
$pdo=getPDO();
$page->setErrorReturnLink('ログインフォームに戻る',InAppUrl::to('sign-in/'));

$page->title="ログイン実行";
$LoginAttemptIp=new LoginAttemptIp($pdo,$_SERVER['REMOTE_ADDR']);
$LoginAttemptIpRow=$LoginAttemptIp->get();
$errorHeader='HTTP/1.1 403 Forbidden';
do{
    if(Session::isLoggedIn()){
        $page->setErrorReturnLink('トップに戻る',InAppUrl::to());
        $page->addErrorMsg('既にログイン中です');
        break;
    }
    if(READONLY_MODE){
        $msg ="読み取り専用モードのためログインできません。\n";
        $msg.="設定ファイルの 'READONLY_MODE' を確認してください。";
        $page->addErrorMsg($msg);
        break;
    }
    if(!ALLOW_REMOTE_EDITOR_LOGIN && is_remote_access()){
        $msg ="実行中のコンピューター以外からのログインは設定で禁止されています。\n";
        $msg.="設定ファイルの 'ALLOW_REMOTE_EDITOR_LOGIN' を確認してください。";
        $page->addErrorMsg($msg);
        break;
    }
    $ipLoginLockedUntil=DateTime::createFromFormat('Y-m-d H:i:s',$LoginAttemptIpRow['login_locked_until']??'');
    $nowDateTime=new DateTime(PROCESS_STARTED_AT);
    if($ipLoginLockedUntil && $ipLoginLockedUntil>$nowDateTime){
        $page->addErrorMsg('同一IPの連続ログイン失敗により一時的にログインを禁止しています');
        ELog::error("IPロック中のログイン試行: REMOTE_ADDR={$_SERVER['REMOTE_ADDR']}, input_username={$id}");
        break;
    }
    if($id===''){
        $page->addErrorMsg('ユーザー名未入力');
        break;
    }
    try {
        $user=Users::getByUsername($pdo,$id);
    } catch (PDOException $e) {
        $page->setErrorReturnLink('インストーラーへ移動',InAppUrl::to(Routes::INSTALLER));
        $page->addErrorMsg('ユーザー取得エラー');
        $page->addErrorMsg('テーブルが未作成の可能性があります');
        break;
    }
    /**
     * ログイン成功・失敗時のユーザーレコード更新処理クラス
     */
    $userUpdater=new class($pdo,$user){
        private PDO $pdo;
        private UsersRow|false $user;
        public function __construct(PDO $pdo,UsersRow|false $user){
            $this->pdo=$pdo;
            $this->user=$user;
        }
        /**
         * ログイン失敗時のカウントアップ・時間制限セット
         */
        public function failed(){
            if($this->user===false){return;}
            $this->user;
            $this->user->failed_login_attempts+=1;
            if(LOGIN_MAX_FAILED_ATTEMPTS && LOGIN_LOCK_DURATION_MINUTES){
                if($this->user->failed_login_attempts>=LOGIN_MAX_FAILED_ATTEMPTS){
                    $this->user->failed_login_attempts=0;
                    $limit=LOGIN_LOCK_DURATION_MINUTES;
                    $this->user->login_locked_until=(new DateTime(PROCESS_STARTED_AT))->modify("+{$limit}min")->format('Y-m-d H:i:s');
                    ELog::error("連続ログイン失敗によりロック開始:username={$this->user->username}, REMOTE_ADDR={$_SERVER['REMOTE_ADDR']}");
                }
            }
            Users::UpdateFromRowObj($this->pdo,$this->user);
        }
        /**
         * ログイン成功時の失敗情報リセット、最終ログイン更新
         */
        public function success(){
            if($this->user==null){return;}
            $this->user->failed_login_attempts=0;
            $this->user->login_locked_until=null;
            $this->user->last_login_at=PROCESS_STARTED_AT;
            Users::UpdateFromRowObj($this->pdo,$this->user);
        }
    };
    if($user && LOGIN_LOCK_DURATION_MINUTES){
        $login_locked_until=DateTime::createFromFormat('Y-m-d H:i:s',$user->login_locked_until?:'');
        if($login_locked_until && $login_locked_until > $nowDateTime){
            $page->addErrorMsg('連続ログイン失敗により一時的にログインを禁止しています');
            ELog::error("ロック中のログイン試行:username={$user->username}, REMOTE_ADDR={$_SERVER['REMOTE_ADDR']}");
            break;
        }
    }
    if($id===ADMINISTRATOR_USER){
        // SuperAdminの場合のログイン処理（期限や有効無効チェックを行わず設定ファイルのパスワードを適用する）
        if(ALLOW_REMOTE_EDITOR_LOGIN && ADMINISTRATOR_PASS===''){
            $msg ="[設定エラー]\n";
            $msg.="リモートログインを許可している場合に使用できないパスワードが設定されています。\n";
            $msg.="設定ファイルの 'ALLOW_REMOTE_EDITOR_LOGIN','ADMINISTRATOR_PASS' を確認してください。";
            $page->addErrorMsg($msg);
            break;
        }
        if(ADMINISTRATOR_ALLOWED_IPS!=[]){
            if(in_array($_SERVER['REMOTE_ADDR'],ADMINISTRATOR_ALLOWED_IPS)===false){
                $msg ="このユーザーは現在のIPアドレス {$_SERVER['REMOTE_ADDR']} からはログインできません。\n";
                $msg.="設定ファイルの 'ADMINISTRATOR_ALLOWED_IPS' を確認してください。";
                $page->addErrorMsg($msg);
                break;
            }
        }
        if(ADMINISTRATOR_PASS==='' && $password===''){
            // パスワード未設定ではパスワードなしで通す
        }else if(!password_verify($password,ADMINISTRATOR_PASS)){
            $userUpdater->failed(); // 同ユーザー名のレコードがある場合は管理者も連続ログイン失敗回数判定を利用
            $page->addErrorMsg('ID・パスワードを確認してください');
            break;
        }
        Session::loginSuperAdmin($user?:null);
    }else{
        if(!$user){
            // 該当ユーザーなし
            $page->addErrorMsg('ID・パスワードを確認してください');
            break;
        }
        if(!$user->is_enabled){
            // ユーザーが無効化されている
            $page->addErrorMsg('このアカウントは現在利用できません');
            break;
        }
        $until=DateTime::createFromFormat('Y-m-d H:i:s',$user->login_enabled_until??'');
        if($until && $nowDateTime>$until){
            // 期限切れ
            $page->addErrorMsg('アカウントの利用可能期限が切れています');
            break;
        }
        if($user && $user->login_url_token && $user->login_url_token!==$login_url_token){
            // 専用URLのあるユーザーは専用URL以外からのアクセスを弾く
            // 専用URL外からのアクセスはユーザー単位の失敗回数にはカウントしない
            $page->setErrorReturnLink('トップに戻る',InAppUrl::to());
            $page->addErrorMsg("このユーザー '{$user->username}' は専用URLからログインしてください");
            break;
        }
        if(!password_verify($password,$user->password_hash)){
            $userUpdater->failed();
            $page->addErrorMsg('ID・パスワードを確認してください');
            break;
        }
        Session::Login($user);
    }
    $userUpdater->success();
    if($LoginAttemptIpRow){
        // IP単位のログイン試行レコードがあればログイン成功でリセットする
        $LoginAttemptIp->reset();
    }
    $setting=new Setting();// ログイン処理時に表示設定を初期化しなおす
    redirect_exit($page->to_app_root_path.$return_url);
}while(false);
if(LOGIN_IP_MAX_FAILED_ATTEMPTS && LOGIN_IP_LOCK_DURATION_MINUTES){
    if(($LoginAttemptIpRow['login_failed_attempts']??0)+1 >= LOGIN_IP_MAX_FAILED_ATTEMPTS){
        // 今回のログイン失敗で規定回数に達する場合
        $LoginAttemptIp->lock($nowDateTime->modify("+".LOGIN_IP_LOCK_DURATION_MINUTES."min")->format('Y-m-d H:i:s'));
        $page->addErrorMsg("同一IPの連続ログイン失敗回数が規定を超えたため一定時間ログインを制限します");
        ELog::error("IP連続ログイン失敗によりロック開始: REMOTE_ADDR={$_SERVER['REMOTE_ADDR']}, input={$id}");
    }else{
        $LoginAttemptIp->increment();
    }
}
header($errorHeader);
$page->printCommonErrorPage();
