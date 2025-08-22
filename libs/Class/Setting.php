<?php
class Setting{
    public $year_view_mode;
    public $age_view_mode;
    public $horse_record_year;
    public $horse_record_date;
    public $horse_record_day;
    public $zero_year;
    public $select_zero_year;
    public $year_select_min_diff;
    public $year_select_max_diff;
    public $birth_year_mode;
    public $syutsuba_year;
    public $syutsuba_date;
    public $world_id;
    public $race_search_org_jra;
    public $race_search_org_nar;
    public $race_search_org_other;
    public $theme_dir_name;
    public $hors_history_sort_is_desc;
    const PARAM_NAME_LIST=[
        'year_select_min_diff'=>['label'=>'年度プルダウン下限',],
        'year_select_max_diff'=>['label'=>'年度プルダウン上限',],
        'year_view_mode'=>['label'=>'年度表示モード',],
        'age_view_mode'=>['label'=>'生年モード',],
        'horse_record_year'=>['label'=>'個別戦績の年形式',],
        'horse_record_date'=>['label'=>'戦績の日付形式',],
        'horse_record_day'=>['label'=>'戦績の日形式',],
        'zero_year'=>['label'=>'相対年数カウント起点の年',],
        'select_zero_year'=>['label'=>'年度プルダウン等起点',],
        'birth_year_mode'=>['label'=>'馬齢モード',],
        'syutsuba_year'=>['label'=>'出馬表の年',],
        'syutsuba_date'=>['label'=>'出馬表の日付形式',],
        'world_id'=>['label'=>'ワールドID',],
        'theme_dir_name'=>['label'=>'配色',],
        'hors_history_sort_is_desc'=>['label'=>'個別戦績のデフォルト並び順',],
    ];

    protected $setting_json_file_path='';

    const YEAR_VIEW_MODE_DEFAULT=0;
    const YEAR_VIEW_MODE_NNM    =1;
    const YEAR_VIEW_MODE_PLMN   =2;
    const YEAR_VIEW_MODE_KI     =3;
    const YEAR_VIEW_MODE__LIST=[
        self::YEAR_VIEW_MODE_DEFAULT=>'正規日付',
        self::YEAR_VIEW_MODE_KI=>'期',
        self::YEAR_VIEW_MODE_NNM=>'年目/年前',
        self::YEAR_VIEW_MODE_PLMN=>'プラスマイナス',
    ];

    const AGE_VIEW_MODE_DEFAULT     =0;
    const AGE_VIEW_MODE_EN          =2;
    const AGE_VIEW_MODE_UMAMUSUME   =1;
    const AGE_VIEW_MODE_UMAMUSUME_S =4;
    const AGE_VIEW_MODE__LIST=[
        self::AGE_VIEW_MODE_DEFAULT     =>'通常',
        self::AGE_VIEW_MODE_EN          =>'英語表記',
        self::AGE_VIEW_MODE_UMAMUSUME   =>'ウマ娘',
        self::AGE_VIEW_MODE_UMAMUSUME_S   =>'ウマ娘(戦績も短縮)',
    ];
    const HORSE_RECORD_YEAR__YEAR=0; //年で表示
    const HORSE_RECORD_YEAR__AGE=1; // 馬齢で表示
    const HORSE_RECORD_YEAR=[
        self::HORSE_RECORD_YEAR__YEAR   =>'年で表示',
        self::HORSE_RECORD_YEAR__AGE    =>'可能なら馬齢で表示',
    ];
    const HORSE_RECORD_DATE=[
        'ymd'=>'年月日',
        'ym'=>'年月まで',
        'y'=>'年のみ',
        'umm'=>'年月と前後半(ウマ娘式)',
    ];
    const HORSE_RECORD_DAY=[
        'd'=>'日',
        't'=>'月の前後半(ウマ娘式ターン)',
    ];
    const BIRTH_YEAR_MODE=[
        0=>'生年',
        2=>'世代（3歳年度2桁）',
        1=>'世代（3歳年度）',
    ];
    const SYUTSUBA_DATE=[
        'mx'=>'月とダミー日付',
        'md'=>'月日',
        'm'=>'月まで',
        'none'=>'非表示',
    ];
    public function __construct(bool $activateToSession = true ){
        if(!$activateToSession){ return false; }
        $this->setting_json_file_path=SETTING_JSON_FILE_PATH;
        $this->setDefault();
        if(!isset($_SESSION['setting'])){
            // セッション側に設定がない場合、ファイルからの設定を試みる
            $setting_is_imported=$this->setFromJsonFile();
            if(!$setting_is_imported){
                // 読み込み失敗した場合もデフォルト値のまま初期設定する
                $this->saveToSessionAll();
            }
        }else{
            // セッション側に設定があればこのオブジェクトに設定
            $this->setSettingBySession();
            // 定数的パラメータの設定
            $this->setConstLikeParam();
        }
    }
    public function setFromJsonFile(){
        if(!file_exists($this->setting_json_file_path)){ return false; }
        $json=json_decode(file_get_contents($this->setting_json_file_path));
        if($json===null){ return false;}
        $this->setByStdClass($json);// JSONから読み取ってオブジェクトに設定          
        $this->saveToSessionAll();
        return true;
    }
    public function setDefault(){
        $this->year_select_min_diff=2;
        $this->year_select_max_diff=5;
        $this->year_view_mode=0;
        $this->age_view_mode=0;

        $this->horse_record_year=0;
        $this->horse_record_date='ymd';
        $this->horse_record_day='d';
        $this->zero_year=2030;
        $this->select_zero_year=2030;
        $this->birth_year_mode=0;
        $this->syutsuba_year=1;
        $this->syutsuba_date='mx';
        $this->world_id=2;
        $this->race_search_org_jra=true;
        $this->race_search_org_nar=false;
        $this->race_search_org_other=false;
        $this->theme_dir_name='';
        $this->hors_history_sort_is_desc=0;
    }
    /**
     * セッションから設定を取得
     */
    public function getBySession($param_name,$if_not_set_return=false){
        if(isset($_SESSION['setting'][$param_name])){
            return $_SESSION['setting'][$param_name];
        }
        return $if_not_set_return;
    }
    /**
     * 設定画面用にこのオブジェクトに取得
     */
    public function setByJson(){
        $json=new stdClass;
        if(file_exists($this->setting_json_file_path)){
            $json=json_decode(file_get_contents($this->setting_json_file_path));
        }
    }
    /**
     * セッションからこのオブジェクトに取得
     */
    public function setSettingBySession(){
        $this->setByStdClass($_SESSION['setting']);
        return;
    }
    /**
     * StdClassまたは配列からこのオブジェクトに反映
     */
    public function setByStdClass($input){
        if(!is_object($input)){ $input=(object)$input; }
        if(isset($input->zero_year)){
            $this->zero_year=filter_var($input->zero_year,FILTER_VALIDATE_INT);
        }
        if(isset($input->select_zero_year)){
            $this->select_zero_year=filter_var($input->select_zero_year);
        }
        if($this->select_zero_year===''){
            $this->select_zero_year=(int)$this->zero_year;
        }
        if(isset($input->year_select_min_diff)){
            $this->year_select_min_diff=max(filter_var($input->year_select_min_diff,FILTER_VALIDATE_INT),0);
        }
        if(isset($input->year_select_max_diff)){
            $this->year_select_max_diff=max(filter_var($input->year_select_max_diff,FILTER_VALIDATE_INT),0);
        }

        if(isset($input->year_view_mode)){
            $this->year_view_mode=filter_var($input->year_view_mode,FILTER_VALIDATE_INT);
        }
        if(isset($input->age_view_mode)){
            $this->age_view_mode=filter_var($input->age_view_mode,FILTER_VALIDATE_INT);
        }
        if(isset($input->horse_record_year)){
            $this->horse_record_year=filter_var($input->horse_record_year,FILTER_VALIDATE_INT);
        }
        if(isset($input->horse_record_date)){
            $this->horse_record_date=filter_var($input->horse_record_date)?:'ymd';
        }
        if(isset($input->horse_record_day)){
            $this->horse_record_day=filter_var($input->horse_record_day)?:'d';
        }
        if(isset($input->birth_year_mode)){
            $this->birth_year_mode=filter_var($input->birth_year_mode,FILTER_VALIDATE_INT);
        }
        if(isset($input->syutsuba_year)){
            $this->syutsuba_year=filter_var($input->syutsuba_year,FILTER_VALIDATE_INT);
        }
        if(isset($input->syutsuba_date)){
            $this->syutsuba_date=filter_var($input->syutsuba_date);
        }
        if(isset($input->world_id)){
            $this->world_id=filter_var($input->world_id,FILTER_VALIDATE_INT);
        }
        if(isset($input->theme_dir_name)){
            $this->theme_dir_name=filter_var($input->theme_dir_name);
        }
        if(isset($input->race_search_org_jra)){
            $this->race_search_org_jra=filter_var($input->race_search_org_jra,FILTER_VALIDATE_BOOL);
        }
        if(isset($input->race_search_org_nar)){
            $this->race_search_org_nar=filter_var($input->race_search_org_nar,FILTER_VALIDATE_BOOL);
        }
        if(isset($input->race_search_org_other)){
            $this->race_search_org_other=filter_var($input->race_search_org_other,FILTER_VALIDATE_BOOL);
        }
        if(isset($input->hors_history_sort_is_desc)){
            $this->hors_history_sort_is_desc=filter_var($input->hors_history_sort_is_desc,FILTER_VALIDATE_INT);
        }
    }
    /**
     * 設定を配列化して出力する
     * @param bool $excludeIfNull trueなら設定値の無い項目は除去する
     */
    public function getSettingArray(bool $excludeIfNull=false){
        $setting=[
            'world_id'=>$this->world_id,
            'theme_dir_name'=>$this->theme_dir_name,
            'select_zero_year'=>$this->select_zero_year,
            'year_select_max_diff'=>$this->year_select_max_diff,
            'year_select_min_diff'=>$this->year_select_min_diff,
            'year_view_mode'=>$this->year_view_mode,
            'zero_year'=>$this->zero_year,
            'birth_year_mode'=>$this->birth_year_mode,
            'age_view_mode'=>$this->age_view_mode,
            'horse_record_year'=>$this->horse_record_year,
            'horse_record_date'=>$this->horse_record_date,
            'horse_record_day'=>$this->horse_record_day,
            'hors_history_sort_is_desc'=>$this->hors_history_sort_is_desc,
            'syutsuba_year'=>$this->syutsuba_year,
            'syutsuba_date'=>$this->syutsuba_date,
            'race_search_org_jra'=>$this->race_search_org_jra,
            'race_search_org_nar'=>$this->race_search_org_nar,
            'race_search_org_other'=>$this->race_search_org_other,
        ];
        if($excludeIfNull){
            $setting=array_diff($setting,[null]);
        }
        return $setting;
    }
    /**
     * 1つのパラメータをセッションに保存する
     */
    public function saveToSession($param_name,$value){
        if(!isset($this->$param_name)){return false;}
        $this->$param_name=$value;
        $_SESSION['setting'][$param_name]=$value;
        return true;        
    }
    /**
     * 一通りの設定をセッションに保存する
     */
    public function saveToSessionAll(){
        $_SESSION['setting']=$this->getSettingArray();
        $this->setConstLikeParam();
    }
    /**
     * 設定をJSONファイルとして出力する
     */
    public function ExportJson(string $path='') {
        if($path===''){ $path=$this->setting_json_file_path; }
        $json=json_encode($this->getSettingArray(),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
        file_put_contents($path,$json);        
    }
    public $year_month_separator='';
    //日付形式の部分
    public $date_format_part=[
        'year_suffix'=>'', // 年のみ表記時
        'ymd'=>[
            'ya_suffix'=>'',
            'ya_m_separator'=>'/',    'm_suffix'=>'',
            'm_dt_separator'=>'/',    'd_suffix'=>'',
        ],
        'ym'=>[
            'ya_suffix'=>'',
            'ya_m_separator'=>'/',    'm_suffix'=>'',
        ],
        'ymt'=>[
            'ya_suffix'=>'',
            'ya_m_separator'=>'/',    'm_suffix'=>'',
            'm_dt_separator'=>'',
        ],
        // 月のみ表記時
        'm'=>['m_suffix'=>'月'],
        // 月日のみ
        'md'=>['m_suffix'=>'',  'm_dt_separator'=>'/', 'd_suffix'=>''],
        // 月とターン
        'mt'=>['m_suffix'=>'月', 'm_dt_separator'=>''],
        'amd'=>[ // 年齢＋月日
            'ya_m_separator'=>' ',    'm_suffix'=>'',
            'm_dt_separator'=>'/',    'd_suffix'=>'',
        ],
        'am'=>[ // 年齢＋月まで
            'ya_m_separator'=>' ',    'm_suffix'=>'月',
        ],
        'amt'=>[ // 年齢＋月ターン
            'ya_m_separator'=>' ',    'm_suffix'=>'月',
            'm_dt_separator'=>'',
        ],
    ];
    protected function setConstLikeParam(){
        switch($this->year_view_mode){
            case self::YEAR_VIEW_MODE_DEFAULT:
                $this->date_format_part['year_suffix']='年';
                $this->date_format_part['ymt']=[
                    'ya_suffix'=>'年',
                    'ya_m_separator'=>' ',    'm_suffix'=>'月',
                    'm_dt_separator'=>'',
                ];
                $this->date_format_part['am']['ya_m_separator']='';
                break;
            case self::YEAR_VIEW_MODE_KI:
                $this->date_format_part['ymd']['ya_m_separator']=' ';
                $this->date_format_part['ymt']['ya_m_separator']=' ';
                $this->date_format_part['ymt']['m_suffix']='月';
                $this->date_format_part['ymt']['m_dt_separator']='';
                $this->date_format_part['ym']['ya_m_separator']=' ';
                $this->date_format_part['ym']['m_suffix']='月';
                break;
            case self::YEAR_VIEW_MODE_PLMN:
                break;
            case self::YEAR_VIEW_MODE_NNM:
                $this->date_format_part['ymd']['ya_m_separator']=' ';
                $this->date_format_part['ymt']['ya_m_separator']=' ';
                $this->date_format_part['ym']['ya_m_separator']=' ';
                $this->date_format_part['ym']['m_suffix']='月';
                break;
            default:
                break;
        }
        switch($this->age_view_mode){
            case self::AGE_VIEW_MODE_UMAMUSUME:
                break;
            case self::AGE_VIEW_MODE_EN:
                break;
            default:
                break;
        }
        $mode=$this->year_view_mode;
        if($mode===self::YEAR_VIEW_MODE_DEFAULT||$mode===self::YEAR_VIEW_MODE_PLMN){
            $this->year_month_separator= "/";
        }else{
            $this->year_month_separator=" ";
        }
    }
    public function getYearSpecialFormat(int $year, int $month=0, $input_mode=null){
        $year_diff=$year-$this->zero_year;
        $month=(int)$month;
        $ret_str='';
        if($input_mode!==null){
            $mode = $input_mode;
        }else{
            $mode=$this->year_view_mode;
        }
        if($mode==self::YEAR_VIEW_MODE_KI){
            $ret_str=$year_diff."期";
            if($month>0){
                $ret_str.=" ".str_pad($month,2,'0',STR_PAD_LEFT)."月";
            }
            return $ret_str;
        }
        if($mode==2){
            $ret_str=(($year_diff>=0)?'+':'').$year_diff;
            if($month>0){
                $ret_str.="/ ".str_pad($month,2,'0',STR_PAD_LEFT)."月";
            }
            return $ret_str;
        }else if($mode==1){
            if($year_diff>0){
                $ret_str=$year_diff."年目";
            }else{
                $ret_str=(($year_diff-1)*-1)."年前";
            }
            if($month>0){
                $ret_str.=" ".str_pad($month,2,'0',STR_PAD_LEFT)."月";
            }
        } else{
            $ret_str=$year;
            if($month>0){
                $ret_str.="/".str_pad($month,2,'0',STR_PAD_LEFT);
            }
        }
        return $ret_str;
    }
    /**
     * 設定に応じた年齢型式を返す
     */
    public function getConvertedAge($age, $age_view_mode=null){
        if($age_view_mode===null){
            $age_view_mode=$this->age_view_mode;
        }
        if($age_view_mode==self::AGE_VIEW_MODE_UMAMUSUME_S){
            return self::getAgeUmamusume2($age);
        }
        if($age_view_mode==self::AGE_VIEW_MODE_UMAMUSUME){
            if($age==2){ return 'ジュニア'; }
            if($age==3){ return 'クラシック'; }
            if($age==4){ return 'シニア'; }
            return 'シニア'.($age-3)."年目";
        }
        if($age_view_mode==self::AGE_VIEW_MODE_EN){
            return self::getAgeSexEn($age);
        }
        return $age."歳";
    }
    /**
     * レース結果用の性齢
     */
    public function getAgeSexSpecialFormat(int $age, int $sex=0){
        $sex_prefix=$sex_suffix='';
        switch($this->age_view_mode){
            case self::AGE_VIEW_MODE_UMAMUSUME:
            case self::AGE_VIEW_MODE_UMAMUSUME_S:
                return self::getAgeUmamusume2($age);
            case self::AGE_VIEW_MODE_EN:
                return self::getAgeSexEn($age,$sex);
        }
        return $sex_prefix.$age.$sex_suffix;
    }
    /**
     * 英語型式の性齢または英語馬齢
     */
    public static function getAgeSexEn(int $age, int $sex=0){
        if($sex===3){
            return 'G'.$age;
        }
        if($sex===1){
            return ($age>3?'H':'C').$age;
        }
        if($sex===2){
            return ($age>3?'M':'F').$age;
        }
        return $age.'yo';
    }

    public static function getAgeUmamusume2(int $age){
        $ret_str='';
        if($age===2){ $ret_str='JU';}
        if($age===3){ $ret_str='CL';}
        if($age>3){
            $ret_str='S'.($age-3);
        }
        return $ret_str;
    }
    public function getBirthYearFormat(int $birth_year){
        switch($this->birth_year_mode){
            case 1:
                $birth_year_str=$birth_year+3;
                break;
            case 2:
                $birth_year_str=substr($birth_year+3,2);
                break;
            default:
                $birth_year_str=$birth_year;
        }
        return $birth_year_str;
    }
    /**
     * レース検索などでの日付列の取得
     */
    public function getRaceListDate($input_array,bool $is_one_year_only=false){
        return $this->getConvertedDate(
            $input_array,
            $this->horse_record_date,
            $this->horse_record_year===self::HORSE_RECORD_YEAR__AGE,
            $is_one_year_only);
    }
    public function getConvertedDate($input_array, $ya_m_dt_format=null, $show_age_if_age_exists=null, bool $is_one_year_only=false){
        // 引数未設定なら環境別フォーマット
        if($ya_m_dt_format===null){ $ya_m_dt_format=$this->horse_record_date;}
        if($show_age_if_age_exists===null){
            $show_age_if_age_exists=($this->horse_record_year===self::HORSE_RECORD_YEAR__AGE);
        }
        $defaults=['year'=>null, 'month'=>null, 'day'=>0, 'turn'=>0, 'age'=>null];
        $date_array=array_merge($defaults,$input_array);
        $year=$date_array['year'];
        $month=$date_array['month'];
        $day=(int)$date_array['day']?:0;
        $turn=(int)$date_array['turn']?:0;
        $age=$date_array['age'];

        $age_mode=false;
        if($age!=null && $show_age_if_age_exists===true){
            //「年齢があれば年齢を使うモード」でなおかつ年齢がある場合、年齢モード
            $age_mode=true;
        }
        $umdb_date=new UmdbDate();
        // 1年分のみモードでなければ年を取得。
        // ただし、1年分のみモードでも年しか表示しない設定では強制的に取得する
        if(!$is_one_year_only || $ya_m_dt_format==='y'){
            if($age_mode){
                // 馬齢モードの場合
                $umdb_date->year_or_age=$this->getConvertedAge($age);
            }else{
                $umdb_date->year_or_age=$this->getYearSpecialFormat($year);
            }
        }
        if(in_array($ya_m_dt_format,['y','a'],true)){
            // 年のみモードは1年分検索にも関係なく年/年齢を返して終了
            // 年齢モードでなければ年のみ用のsuffixを付与
            $umdb_date->year_or_age_suffix.=$age_mode?'':$this->date_format_part['year_suffix'];
            return $umdb_date;
        }
        // フォーマット別キーの切り替え
        if(in_array($ya_m_dt_format,['umm','amt','ymt'],true)){
            $key=$age_mode?'amt':'ymt';
        }
        if(in_array($ya_m_dt_format,['amd','ymd'],true)){ $key=$age_mode?'amd':'ymd'; }
        if(in_array($ya_m_dt_format,['ym','am'],true)) { $key=$age_mode?'am':'ym'; }
        if($is_one_year_only){
            // 1年指定検索の場合は年を付与しないので年無しフォーマットに切り替え
            if($key==='ymd'||$key==='amd'){ $key='md'; }
            if($key==='ymt'||$key==='amt'){ $key='mt'; }
            if($key==='ym' ||$key==='am') { $key='m'; }
        }
        // 月をセット
        $umdb_date->month=str_pad($month, 2, "0", STR_PAD_LEFT);
        if($umdb_date->year_or_age!==''){
            // 年が空でない場合は月の前の項目を付与
            $umdb_date->year_or_age_suffix=$this->date_format_part[$key]['ya_suffix']??'';
            $umdb_date->year_or_age_m_separator=$this->date_format_part[$key]['ya_m_separator'];
        }
        $umdb_date->month_suffix=$this->date_format_part[$key]['m_suffix']??'';
        if($ya_m_dt_format==='ymd'){
            // 日まで表示するフォーマットで、日まで渡されている場合日を付与
            if($day>0){
                $umdb_date->month_dt_separator=$this->date_format_part['ymd']['m_dt_separator']??'';
                $umdb_date->day_or_turn=str_pad($day, 2, "0", STR_PAD_LEFT);
                $umdb_date->day_or_turn_suffix=$this->date_format_part['ymd']['d_suffix']??'';

            }
        }else if($ya_m_dt_format==='umm'){
            // ウマ娘フォーマットで、ターンが1か2の場合にターン付与
            if($turn===1){
                $umdb_date->day_or_turn="前半";
            }else if($turn===2){
                $umdb_date->day_or_turn="後半";
            }
            if($umdb_date->day_or_turn!==''){
                $umdb_date->month_dt_separator=$this->date_format_part[$key]['m_dt_separator'];
            }
        }
        return $umdb_date;
    }
}
