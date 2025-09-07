<?php
class TemplateImporter {
    private static string $defaultDir='';
    private static string $themeDir='';
    private static string $userDir='';
    private $templatePath='';

    /**
     * グローバルスコープにincludeする際用のパス取得
     */
    public function __construct($templatePath) {
        $this->templatePath=$templatePath;
    }
    public function getPath(){
        $path=self::$userDir.'/'.$this->templatePath;
        if(self::$userDir!=='' && file_exists($path)){
            return $path;
        }
        $path=self::$themeDir.'/'.$this->templatePath;
        if(self::$themeDir!=='' && file_exists($path)){
            return $path;
        }
        return self::$defaultDir.'/'.$this->templatePath;   
    }
    public function __toString(){
        return $this->getPath();
    }
    public static function setDefalutDir(string $defaultDir){
        self::$defaultDir=$defaultDir;
    }
    public static function setThemeDir(string $themeDir){
        self::$themeDir=$themeDir;
    }
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
