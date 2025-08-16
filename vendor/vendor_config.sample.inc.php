<?php
// ローカルファイルのみ対応
$cfg['VENDOR_Jquery_DatePicker_FILE']='datepicker-ja.js';

// ローカルファイルとCDNの切り替え対象
$cfg['VENDOR_Jquery_FILE']='jquery-3.7.1.min.js';
$cfg['VENDOR_Jquery_UI_DIR']='jquery-ui-1.14.1';

// trueにするとCDNの代わりにローカルファイルを使用します。
$cfg['USE_LOCAL_JQUERY_FILE']   =false;
// CDNからの読み込みタグまたはURL
$cfg['CDN_Jquery_TAG']          ='<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>';
$cfg['CDN_Jquery_UI_JS_TAG']    ='<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.14.1/jquery-ui.min.js"></script>';
$cfg['CDN_Jquery_UI_CSS_TAG']   ='<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.14.1/themes/smoothness/jquery-ui.css">';
