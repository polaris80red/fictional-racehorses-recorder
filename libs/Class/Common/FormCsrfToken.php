<?php
class FormCsrfToken {
    /** 
     * CSRFトークンのキー名 
     */
    public const TOKEN_KEY = 'FORM_CSRF_TOKEN';

    /**
     * CSRFトークンの値を保持するメンバ変数
     */
    private string $sessionToken  = '';
    private string $new_token  = '';

    public function __construct()
    {
        $this->sessionToken=$_SESSION[APP_INSTANCE_KEY][self::TOKEN_KEY]??'';
        unset($_SESSION[APP_INSTANCE_KEY][self::TOKEN_KEY]);
        $this->generateToken();
    }

    /**
     * CSRFトークンを生成してセッションにセットする
     */
    protected function generateToken(): void
    {
        $this->new_token=bin2hex(random_bytes(32));
        $_SESSION[APP_INSTANCE_KEY][self::TOKEN_KEY]=$this->new_token;
    }

    /**
     * hiddenのinputタグ文字列を返す
     */
    public function getHiddenInputTag(): string
    {
        $str="<input type=\"hidden\" name=".self::TOKEN_KEY." value=\"".$this->new_token."\">";
        return $str;
    }
    /**
     * hiddenのinputタグをprintする
     */
    public function printHiddenInputTag(){
        print $this->getHiddenInputTag();
    }

    /**
     * SESSIONとPOSTのトークンを比較
     */
    public function isValid(): bool
    {
        // セッション側トークンがない場合は認証NG
        if($this->sessionToken===''){
            ELog::debug(__CLASS__." エラー：SESSIONトークンなし");
            return false;
        }
        // POST側トークンがない場合は認証NG
        $post_token=(string)filter_input(INPUT_POST,self::TOKEN_KEY);
        if($post_token===''){
            ELog::debug(__CLASS__." エラー：POSTトークンなし");
            return false;
        }
        // 一致していればTRUE、それ以外はfalse
        if($post_token===$this->sessionToken){
            return true;
        }
        ELog::debug(__CLASS__." エラー：トークン不一致");
        return false;
    }
}