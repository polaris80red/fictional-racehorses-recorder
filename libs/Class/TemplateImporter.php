<?php
class TemplateImporter {
    private static string $defaultDir='';
    private static string $themeDir='';
    private static string $userDir='';

    private function __construct() {}
    public static function setDefalutDir(string $defaultDir){
        self::$defaultDir=$defaultDir;
    }
    public static function setThemeDir(string $themeDir){
        self::$themeDir=$themeDir;
    }
    public static function setUserDir(string $userDir){
        self::$userDir=$userDir;
    }
    public static function include(string $templatePath){
        if(self::$userDir!=='' && self::includeIfExists(self::$userDir.'/'.$templatePath)){
            return true;
        }
        if(self::$themeDir!=='' && self::includeIfExists(self::$themeDir.'/'.$templatePath)){
            return true;
        }
        return self::includeIfExists(self::$defaultDir.'/'.$templatePath);
    }
    private static function includeIfExists($path){
        if(file_exists($path)){
            include $path;
            return true;
        }
        return false;
    }
}
