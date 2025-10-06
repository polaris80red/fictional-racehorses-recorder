<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
require_once __DIR__.'/libs/common.inc.php';
InAppUrl::init(2);
$page=new Page(2);
$page->title="インストーラー｜ログアウト";
$page->ForceNoindex();

InstallerSession::logout();
header("Location: ./login.php");
exit;
