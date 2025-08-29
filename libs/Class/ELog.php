<?php
class ELog{
    protected static $output_dir='';
    protected static $logfile_prefix='';
    protected static int $log_level=0;
    protected const LogLevel=[
        1=>'error',
        2=>'debug',
    ];
    public static function setExportDir(string $dir_path,string $logfile_prefix){
        self::$output_dir=$dir_path;
        self::$logfile_prefix=$logfile_prefix;
    }
    public static function setLogLevel(int $log_level){
        self::$log_level=$log_level;
    }
    protected static function write(int $log_level,$log_value,$log_optional_value=''){
        $datetime=new DateTime();
        $path=self::$output_dir.'/'.self::$logfile_prefix."_".$datetime->format('Y-m-d').".log";
        file_put_contents($path,
            "[".$datetime->format('Y-m-d H:i:s')."] "
            .(self::LogLevel[$log_level]??'xxxxx')
            .': '
            .print_r($log_value,true)
            .($log_optional_value!==''?(' '.print_r($log_optional_value,true)):'')
            ."\n",
            FILE_APPEND);
    }
    public static function error($log_value,$log_optional_value=''){
        if(self::$log_level<0||self::$log_level>=1){
            self::write(1,$log_value,$log_optional_value);
        }
    }
    public static function debug($log_value,$log_optional_value=''){
        if(self::$log_level<0||self::$log_level>=2){
            self::write(2,$log_value,$log_optional_value);
        }
    }
}
