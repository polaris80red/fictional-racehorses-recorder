<?php
class VendorConfigInitializer {
    public static function initialize(array $cfg, array $defaults){
        $cfg=array_merge($defaults,$cfg);

        define('VENDOR_Jquery_FILE',$cfg['VENDOR_Jquery_FILE']);
        define('VENDOR_Jquery_UI_DIR',$cfg['VENDOR_Jquery_UI_DIR']);

        define('VENDOR_Jquery_DatePicker_FILE',$cfg['VENDOR_Jquery_DatePicker_FILE']);

        $use_local_jquery=filter_var($cfg['USE_LOCAL_JQUERY_FILE'],FILTER_VALIDATE_BOOL);
        define('USE_LOCAL_JQUERY_FILE',$use_local_jquery);

        define('CDN_Jquery_TAG',$cfg['CDN_Jquery_TAG']);
        define('CDN_Jquery_UI_JS_TAG',$cfg['CDN_Jquery_UI_JS_TAG']);
        define('CDN_Jquery_UI_CSS_TAG',$cfg['CDN_Jquery_UI_CSS_TAG']);
    }
}
