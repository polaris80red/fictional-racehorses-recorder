<?php
session_start();
require_once dirname(__DIR__).'/libs/init.php';
defineAppRootRelPath(1);
$setting=new Setting();
$session=new Session();

$url=APP_ROOT_REL_PATH;
if(!$session->is_logined()){
    $url.="sign-in/";
}
header('Location: '.$url);
exit;