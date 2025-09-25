<?php
class EnvConfigInitializer {
    public static function initialize(array $cfg, array $defaults){
        $has_error=false;
        $error_msgs=[];

        $cfg=array_merge($defaults,$cfg);
        define('DB_HOST',$cfg['DB_HOST']);
        define('DB_NAME',$cfg['DB_NAME']);
        define('DB_USER',$cfg['DB_USER']);
        define('DB_PASS',$cfg['DB_PASS']);
        define('DB_CHARSET',$cfg['DB_CHARSET']);
        
        define('SITE_NAME',$cfg['SITE_NAME']);

        define('ALLOW_REMOTE_ACCESS',
            filter_var($cfg['ALLOW_REMOTE_ACCESS'],FILTER_VALIDATE_BOOL));

        define('ALLOW_REMOTE_EDITOR_LOGIN',
            filter_var($cfg['ALLOW_REMOTE_EDITOR_LOGIN'],FILTER_VALIDATE_BOOL));

        define('FORCE_NOINDEX',
            filter_var($cfg['FORCE_NOINDEX'],FILTER_VALIDATE_BOOL));

        define('READONLY_MODE',
            filter_var($cfg['READONLY_MODE'],FILTER_VALIDATE_BOOL));
        
        $show_login_link=filter_var($cfg['SHOW_LOGIN_LINK'],FILTER_VALIDATE_BOOL);
        if(READONLY_MODE){ $show_login_link=false; }
        define('SHOW_LOGIN_LINK',$show_login_link);

        define('SHOW_DISPLAY_SETTINGS_FOR_GUESTS',
            filter_var($cfg['SHOW_DISPLAY_SETTINGS_FOR_GUESTS'],FILTER_VALIDATE_BOOL));

        define('ENABLE_ACCESS_COUNTER',
            filter_var($cfg['ENABLE_ACCESS_COUNTER'],FILTER_VALIDATE_BOOL));

        define('SHOW_PARENT_SITE_LINK',
            filter_var($cfg['SHOW_PARENT_SITE_LINK'],FILTER_VALIDATE_BOOL));
        define('PARENT_SITE_LINK_TEXT',$cfg['PARENT_SITE_LINK_TEXT']);
        define('PARENT_SITE_URL',$cfg['PARENT_SITE_URL']);

        define('STORAGE_DIR',$cfg['STORAGE_DIR']);

        define('LOG_DIR_PATH',$cfg['LOG_DIR_PATH']);
        define('LOG_FILE_PREFIX',$cfg['LOG_FILE_PREFIX']);
        define('LOG_LEVEL',$cfg['LOG_LEVEL']);
        define('ADMINISTRATOR_USER',$cfg['ADMINISTRATOR_USER']);
        define('ADMINISTRATOR_PASS',$cfg['ADMINISTRATOR_PASS']);

        $display_config_source=$cfg['DISPLAY_CONFIG_SOURCE'];
        if(!in_array($display_config_source,['db','json'])){
            // 対応していない値の場合はdbにする
           $display_config_source= 'db';
        }
        define('DISPLAY_CONFIG_SOURCE',$display_config_source);
        define('DISPLAY_CONFIG_JSON_PATH',$cfg['DISPLAY_CONFIG_JSON_PATH']);

        define('LOGIN_MAX_FAILED_ATTEMPTS',max((int)$cfg['LOGIN_MAX_FAILED_ATTEMPTS'],0));
        define('LOGIN_LOCK_DURATION_MINUTES',max((int)$cfg['LOGIN_LOCK_DURATION_MINUTES'],0));

        $app_instance_key=$cfg['APP_INSTANCE_KEY'];
        // 未設定の場合はアプリルートのディレクトリ名を付与する
        define('APP_INSTANCE_KEY',$app_instance_key?:basename(APP_ROOT_DIR));

        define('ANNONYMOUS_HORSE_NAME',$cfg['ANNONYMOUS_HORSE_NAME']);

        define('EDIT_MENU_TOGGLE',filter_var($cfg['EDIT_MENU_TOGGLE'],FILTER_VALIDATE_BOOL));

        define('ITEM_ID_FORBIDDEN_CHARS',$cfg['ITEM_ID_FORBIDDEN_CHARS']);

        define('AUTO_ID_DATE_PART_FORMAT',$cfg['AUTO_ID_DATE_PART_FORMAT']);

        define('AUTO_ID_DATE_NUMBER_SEPARATOR',$cfg['AUTO_ID_DATE_NUMBER_SEPARATOR']);
        define('AUTO_ID_NUMBER_MIN_LENGTH',(int)$cfg['AUTO_ID_NUMBER_MIN_LENGTH']);

        if(!in_array($cfg['AUTO_ID_RESET_MODE'],['y','m','d'],true)){
            $cfg['AUTO_ID_RESET_MODE']='';
            $error_msgs[]="[AUTO_ID_RESET_MODE]\ny,m,d以外の文字が設定されています。";
        }
        define('AUTO_ID_RESET_MODE',$cfg['AUTO_ID_RESET_MODE']);
        if($has_error){
            self::PrintCriticalError($error_msgs);
        }
    }
    protected static function PrintCriticalError(array $error_msgs){
        header('Content-Type: text/plain; charset=UTF-8');
        print "設定ファイルエラー\n\n";
        print implode("\n\n",$error_msgs);
        exit;
    }
}
