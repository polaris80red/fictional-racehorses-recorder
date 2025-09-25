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
    /**
     * ログイン成功・失敗時のユーザーレコード更新処理
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
                    $this->user->login_locked_until=(new DateTime())->modify("+{$limit}min")->format('Y-m-d H:i:s');
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
        if($login_locked_until && $login_locked_until>(new DateTime())){
            header('HTTP/1.1 403 Forbidden');
            $page->addErrorMsg('連続ログイン失敗により一時的にログインを禁止しています');
            ELog::error("ロック中のログイン試行:username={$user->username}, REMOTE_ADDR={$_SERVER['REMOTE_ADDR']}");
            break;
        }
    }
    if($id===ADMINISTRATOR_USER){
        // SuperAdminの場合のログイン処理
        if(ADMINISTRATOR_PASS==='' && $password===''){
            // パスワード未設定ではパスワードなしで通す
        }else if(!password_verify($password,ADMINISTRATOR_PASS)){
            $userUpdater->failed();
            header('HTTP/1.1 403 Forbidden');
            $page->addErrorMsg('ID・パスワードを確認してください');
            break;
        }
        Session::loginSuperAdmin($user?:null);
    }else{
        if(!$user){
            // 該当ユーザーなし
            header('HTTP/1.1 403 Forbidden');
            $page->addErrorMsg('ID・パスワードを確認してください');
            break;
        }
        if(!$user->is_enabled){
            // 無効化されている
            header('HTTP/1.1 403 Forbidden');
            $page->addErrorMsg('このアカウントは現在利用できません');
            break;
        }
        if($user->login_enabled_until!=null){
            $until=DateTime::createFromFormat('Y-m-d H:i:s',$user->login_enabled_until);
            $now=new DateTime();
            if($until && $now>$until){
                // 期限切れ
                header('HTTP/1.1 403 Forbidden');
                $page->addErrorMsg('アカウントの利用可能期限が切れています');
                break;
            }
        }
        if(!password_verify($password,$user->password_hash)){
            $userUpdater->failed();
            header('HTTP/1.1 403 Forbidden');
            $page->addErrorMsg('ID・パスワードを確認してください');
            break;
        }
        Session::Login($user);
    }
    $userUpdater->success();
    $setting=new Setting();// ログイン処理時に初期化しなおす
    redirect_exit($page->to_app_root_path.$return_url);
}while(false);
$page->printCommonErrorPage();
