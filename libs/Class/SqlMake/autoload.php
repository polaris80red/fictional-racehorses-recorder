<?php
spl_autoload_register(function ($class_name) {
    $f_ckeck=substr($class_name,0,7);
    if($f_ckeck==='SqlMake'){
        $path = __DIR__.'/'.$class_name . '.class.php';
        if(file_exists($path)){
            require_once $path;
            return;
        }
    }
});
