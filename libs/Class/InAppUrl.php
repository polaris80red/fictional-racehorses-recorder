<?php
class InAppUrl {
    private static $basePath;
    public static function init(int $depth = 0) {
        self::$basePath = $depth > 0 ? str_repeat('../',$depth) : './';
    }
    public static function to(string $path='', array $params=[]){
        $url=self::$basePath.ltrim($path,'/');
        if($params!==[]){
            $url.="?".http_build_query($params);
        }
        return $url;
    }
}
