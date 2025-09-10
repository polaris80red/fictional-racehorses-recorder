<?php
class TemplateImporter {
    private static string $defaultDir='';
    private static string $themeDir='';
    private static string $userDir='';
    private bool $fileExists=false;
    private string $templateFullPath='';

    /**
     * グローバルスコープにincludeする際用にインスタンスを生成してパスをセット
     */
    public function __construct(string $templatePath) {
        $path=self::$userDir.'/'.$templatePath;
        if(self::$userDir!=='' && file_exists($path)){
            $this->templateFullPath=$path;
            $this->fileExists=true;
            return;
        }
        $path=self::$themeDir.'/'.$templatePath;
        if(self::$themeDir!=='' && file_exists($path)){
            $this->templateFullPath=$path;
            $this->fileExists=true;
            return;
        }
        $path=self::$defaultDir.'/'.$templatePath;
        $this->templateFullPath=$path;
        if(file_exists($path)){
            $this->fileExists=true;
            return;
        }
    }
    /**
     * 指定ファイルが存在していればtrue
     * （必須にしないテンプレートでエラーを起こさないようincludeを省略する場合の事前判定用）
     */
    public function fileExists():bool
    {
        return $this->fileExists;
    }
    public function __toString(){
        return $this->templateFullPath;
    }
    /**
     * デフォルトのディレクトリの設定
     * @param string $defaultDir デフォルト読み込み元ディレクトリのパス
     */
    public static function setDefalutDir(string $defaultDir){
        self::$defaultDir=$defaultDir;
    }
    /**
     * テーマ用ディレクトリの設定
     * @param string $themeDir テーマ別ディレクトリのパス
     */
    public static function setThemeDir(string $themeDir){
        self::$themeDir=$themeDir;
    }
    /**
     * ユーザー用ディレクトリの設定
     * @param string $userDir ユーザーディレクトリのパス
     */
    public static function setUserDir(string $userDir){
        self::$userDir=$userDir;
    }
    public static function include(string $templatePath, Page|null $page = null){
        if(self::$userDir!=='' && self::includeIfExists(self::$userDir.'/'.$templatePath,$page)){
            return true;
        }
        if(self::$themeDir!=='' && self::includeIfExists(self::$themeDir.'/'.$templatePath,$page)){
            return true;
        }
        return self::includeIfExists(self::$defaultDir.'/'.$templatePath,$page);
    }
    private static function includeIfExists($path, Page|null $page = null){
        if(file_exists($path)){
            include $path;
            return true;
        }
        return false;
    }
}
