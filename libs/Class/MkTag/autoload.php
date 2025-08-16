<?php
spl_autoload_register(function ($class_name) {
    // 前方一致で長いものからautoloadする
    $f_ckeck=substr($class_name,0,9);
    if($f_ckeck==='MkTagAttr'){
        $path = __DIR__.'/MkTagAttr/'.$class_name . '.php';
        if(file_exists($path)){
            require_once $path;
            return;
        }
    }
    // MkTagディレクトリのクラス
    $f_ckeck=substr($f_ckeck,0,5);
    if($f_ckeck==='MkTag'){
        $path = __DIR__.'/'.$class_name . '.php';
        if(file_exists($path)){
            require_once $path;
            return;
        }
    }
});
