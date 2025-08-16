<?php
spl_autoload_register(function ($class_name) {
    $f_ckeck=substr($class_name,0,5);
    if($f_ckeck==='MkSql'){
        $path = __DIR__.'/'.$class_name . '.php';
        if(file_exists($path)){
            require_once $path;
            return;
        }
    }
});
