<?php
class RaceSearch extends Search{
    protected const SESSION_PARENT_KEY='race_list';

    public $world_id=0;// settingから

    public $min_year='';
    public $max_year='';
    public $year='';
    public $search_word='';

    public $age =0;
    const Age20 = 0b0001;
    const Age30 = 0b0010;
    const Age31 = 0b0100;
    const Age41 = 0b1000;
    
    public $is_generation_search=0;
    public $max_year_is_enabled_for_generation_search=0;

    public $grade       = 0;
    const Grade_G1      = 0b000000000001;
    const Grade_G2      = 0b000000000010;
    const Grade_G3      = 0b000000000100;
    const Grade_G       = 0b000000001000;
    const Grade_OP      = 0b000000010000;
    const Grade_L       = 0b000000100000;
    const Grade_W1      = 0b000001000000;
    const Grade_W2      = 0b000010000000;
    const Grade_W3      = 0b000100000000;
    const Grade_Maiden  = 0b001000000000;
    const Grade_All     = 0b001111111111;

    public $show_disabled=false;

    public $race_course='';

    public $distance='';

    public $course_type_tf=0;
    public $course_type_dt=0;
    public $course_type_hd=0;

    public $search_detail_tgl_status='';

    public $limit=50;
    public $page_number=0;
    public $search_detail_is_enabled=0;

    public $current_page_results_count=null;

    public $is_one_year_only=false;

    public $show_organization_jra=true;
    public $show_organization_nar=false;
    public $show_organization_other=false;

    public $session_is_not_update=false;

    function __construct($do_set=false){
        if($do_set){
            $this->setByUrl();
        }
        return;
    }
    public function setSetting(Setting $setting){
        parent::setSetting($setting);
        $this->min_year=$setting->select_zero_year - $setting->year_select_min_diff;
        $this->max_year=$setting->select_zero_year + $setting->year_select_max_diff;

        $this->show_organization_jra=$setting->race_search_org_jra;
        $this->show_organization_nar=$setting->race_search_org_nar;
        $this->show_organization_other=$setting->race_search_org_other;
        return $this;
    }
    public function setByUrl(){
        $this->search_detail_tgl_status=filter_input(INPUT_GET,'search_detail_tgl_status');
        switch($this->search_detail_tgl_status){
            case 'close':
                $this->search_detail_is_enabled=0;
                break;
            case 'open':
                $this->search_detail_is_enabled=1;
                break;
            default:
        }

        $search_word=filter_input(INPUT_GET,'search_word');
        if($search_word!=''){
            $search_word = trim($search_word);
        }
        $this->search_word=$search_word;

        $func_year_convert=(function(string $input){
            if($input===''){ return ''; }
            if($input==='0'){ return 0; }
            return (int)$input;
        });
        $year=(string)filter_input(INPUT_GET,'year');
        if($year===''){ $year=(string)filter_input(INPUT_GET,'year_raw');}
        $this->year=$func_year_convert($year);

        if(isset($_GET['min_year'])){
            $this->min_year=$func_year_convert((string)filter_input(INPUT_GET,'min_year'));
        }
        if(isset($_GET['max_year'])){
            $this->max_year=$func_year_convert((string)filter_input(INPUT_GET,'max_year'));
        }

        $this->is_generation_search=filter_input(INPUT_GET,'is_generation_search',FILTER_VALIDATE_INT);
        // 年が未指定の場合は強制的に世代検索オフ
        if(!$this->year){ $this->is_generation_search=0; }
        
        $this->show_organization_jra=filter_input(INPUT_GET,'show_organization_jra',FILTER_VALIDATE_BOOL);
        $this->show_organization_nar=filter_input(INPUT_GET,'show_organization_nar',FILTER_VALIDATE_BOOL);
        $this->show_organization_other=filter_input(INPUT_GET,'show_organization_other',FILTER_VALIDATE_BOOL);

        $age_search_is_enabled=true;
        $age_search_is_enabled=!filter_input(INPUT_GET,'age_reset',FILTER_VALIDATE_BOOL);
        if($this->is_generation_search){ $age_search_is_enabled = false; }
        if($age_search_is_enabled){
            $this->age=(function($age){
                $input_age=filter_input(INPUT_GET,'age',FILTER_VALIDATE_INT,[
                    'flags' => FILTER_REQUIRE_ARRAY
                ]);
                if(!empty($input_age[20])){$age|=self::Age20;}
                if(!empty($input_age[30])){$age|=self::Age30;}
                if(!empty($input_age[31])){$age|=self::Age31;}
                if(!empty($input_age[41])){$age|=self::Age41;}
                return $age;
            })($this->age);
        }

        if(!filter_input(INPUT_GET,'grade_reset',FILTER_VALIDATE_BOOL)){
            $this->grade=(function($g){
                $check_array=[
                    'grade_g1'=>self::Grade_G1,
                    'grade_g2'=>self::Grade_G2,
                    'grade_g3'=>self::Grade_G3,
                    'grade_gn'=>self::Grade_G,
                    'grade_1w'=>self::Grade_W1,
                    'grade_2w'=>self::Grade_W2,
                    'grade_3w'=>self::Grade_W3,
                    'grade_maiden'=>self::Grade_Maiden,
                ];
                foreach($check_array as $input_name=>$flag){
                    $input_result=filter_input(INPUT_GET,$input_name,FILTER_VALIDATE_BOOL);
                    if($input_result){ $g|=$flag; }
                }
                if(filter_input(INPUT_GET,'grade_op',FILTER_VALIDATE_BOOL)){
                    $g|=self::Grade_L;
                    $g|=self::Grade_OP;
                }
                return $g;
            })($this->grade);
        }
        $this->race_course=trim((string)filter_input(INPUT_GET,'race_course'));
        $this->course_type_tf=filter_input(INPUT_GET,'course_type_tf',FILTER_VALIDATE_BOOL);
        $this->course_type_dt=filter_input(INPUT_GET,'course_type_dt',FILTER_VALIDATE_BOOL);
        $this->course_type_hd=filter_input(INPUT_GET,'course_type_hd',FILTER_VALIDATE_BOOL);
        $this->distance=trim(mb_convert_kana(filter_input(INPUT_GET,'distance')?:'','a'));
        
        $this->max_year_is_enabled_for_generation_search=filter_input(INPUT_GET,'max_year_is_enabled_for_generation_search',FILTER_VALIDATE_BOOL);
        $this->show_disabled=filter_input(INPUT_GET,'show_disabled',FILTER_VALIDATE_BOOL);

        $limit=filter_input(INPUT_GET,'limit');
        if(isset($_GET['limit']) && $limit!==''){
            $this->limit=$limit;
        }
        $this->page_number=filter_input(INPUT_GET,'page',FILTER_VALIDATE_INT);

        $this->session_is_not_update=filter_input(INPUT_GET,'session_is_not_update',FILTER_VALIDATE_BOOL);
        if(!$this->session_is_not_update){
            $this->setToSessionByParamNameArray([
                'year',
                'max_year','min_year',
                'is_generation_search','search_word',
                'show_organization_jra',
                'show_organization_nar',
                'show_organization_other',
                'age',
                'grade',
                'race_course',
                'course_type_tf','course_type_dt','course_type_hd',
                'distance','show_disabled',
                'limit','page',
                'max_year_is_enabled_for_generation_search',
                'search_detail_tgl_status',
                'search_detail_is_enabled'
            ]);
        }
        return;
    }
    /**
     * セッションから復元
     */
    public function setBySession(){
        $target=[
            'year',
            'max_year','min_year',
            'is_generation_search','search_word',
            'show_organization_jra',
            'show_organization_nar',
            'show_organization_other',
            'age',
            'grade',
            'race_course',
            'course_type_tf','course_type_dt','course_type_hd',
            'distance','show_disabled',
            'limit',
            'max_year_is_enabled_for_generation_search',
            'search_detail_tgl_status',
            'search_detail_is_enabled'
        ];
        $this->setBySessionByParamNameArray($target);
        return;
    }
    public function getUrlParam(array $remove_param_names=[]){
        $param_names=[
            'year',
            'max_year','min_year',
            'is_generation_search','search_word',
            'show_organization_jra',
            'show_organization_nar',
            'show_organization_other',
            'race_course',
            'course_type_tf','course_type_dt','course_type_hd',
            'distance','show_disabled',
            'limit','page',
            'max_year_is_enabled_for_generation_search',
            'search_detail_tgl_status'
        ];
        $param_array=$this->toArray($param_names);
        (function($flags)use(&$param_array){
            if($flags->hasFlag(self::Age20)){ $param_array['age'][20]=1; }
            if($flags->hasFlag(self::Age30)){ $param_array['age'][30]=1; }
            if($flags->hasFlag(self::Age31)){ $param_array['age'][31]=1; }
            if($flags->hasFlag(self::Age41)){ $param_array['age'][41]=1; }
        })(new FlagChecker($this->age));

        (function($flags)use(&$param_array){
            if($flags->hasFlag(self::Grade_G1)){ $param_array['grade_g1']=1; }
            if($flags->hasFlag(self::Grade_G2)){ $param_array['grade_g2']=1; }
            if($flags->hasFlag(self::Grade_G3)){ $param_array['grade_g3']=1; }
            if($flags->hasFlag(self::Grade_G)) { $param_array['grade_gn']=1; }
            if($flags->hasFlag(self::Grade_OP) || $flags->hasFlag(self::Grade_L)){
                $param_array['grade_op']=1;
            }
            if($flags->hasFlag(self::Grade_W1)) { $param_array['grade_1w']=1; }
            if($flags->hasFlag(self::Grade_W2)) { $param_array['grade_2w']=1; }
            if($flags->hasFlag(self::Grade_W3)) { $param_array['grade_3w']=1; }
            if($flags->hasFlag(self::Grade_Maiden)) { $param_array['grade_maiden']=1; }
        })(new FlagChecker($this->grade));

        $param_array=array_diff_key_by_name($param_array,$remove_param_names);
        return http_build_query($param_array);
    }
    public function printStrIfNotEmpty($param_name,$output=''){
        if(empty($this->{$param_name})){
            return;
        }
        if($output===''){
            echo $this->{$param_name};
        }
        echo $output;
        return;
    }
    public function getParam($param_name){
        return $this->{$param_name};
    }
    /**
     * すべての必須条件が未設定か
     * @return bool
     */
    public function is_empty(){
        if(filter_input(INPUT_GET,'search_reset',FILTER_VALIDATE_BOOL)===true){
            return true;
        }
        $int_params=[
            'age',
            //'age_20','age_30','age_31','age_41',
            'grade',
        ];
        foreach($int_params as $val){
            if(intval($this->{$val})!==0){
                return false;
            }
        }
        $str_params=['year','search_word','race_course','distance'];
        foreach($str_params as $val){
            if(strval($this->{$val})!==''){
                return false;
            }
        }
        return true;
    }
    /**
     * 検索実行
     */
    public function SelectExec(PDO $pdo){
        $tbl=RaceResults::TABLE;
        $where_parts=[];
        $pre_bind=new StatementBinder();
        if($this->year!==''){
            // 年が設定されている
            if($this->is_generation_search){
                // 世代検索
                $where_part_g="(".implode("OR",[
                    "(age.search_id=20 AND `year`=:year_2yo)",
                    "(age.search_id=21 AND `year`>=:year_2yo AND `year`<=:year_max_yo)",
                    // 2歳以上：アベイドロンシャン賞など
                    "(age.search_id=30 AND `year`=:year)",
                    "(age.search_id=31 AND `year`>=:year AND `year`<=:year_max_yo)",
                    "(age.search_id=40 AND `year`=:year_4yo)",
                    // 4歳限定：香港クラシック
                    "(age.search_id=41 AND `year`>=:year_4yo AND `year`<=:year_max_yo)",
                ]).")";
                $where_parts[]=$where_part_g;
                $pre_bind->add(':year_2yo', ($this->year-1), PDO::PARAM_STR);
                $pre_bind->add(':year', $this->year, PDO::PARAM_STR);
                $pre_bind->add(':year_4yo', ($this->year+1), PDO::PARAM_STR);
                $g_max_year=$this->year+10;
                if($this->max_year_is_enabled_for_generation_search && $this->max_year!==''){
                    // 最初から超過する場合は上限に関係なく4歳まで
                    $g_max_year=$this->max_year;
                }
                $pre_bind->add(':year_max_yo', max($g_max_year,$this->year+1), PDO::PARAM_STR);
            }else{
                // 通常の年度検索
                $where_parts[]="`year`=:year";
                $pre_bind->add(':year', $this->year, PDO::PARAM_STR);
                $this->is_one_year_only=true;
            }
        }else{
            // 年が未指定の場合
            if($this->min_year!==''){
                $where_parts[]="`year`>=:min_year";
                $pre_bind->add(':min_year', (int)$this->min_year, PDO::PARAM_INT);
            }
            if($this->max_year!==''){
                $where_parts[]="`year`<=:max_year";
                $pre_bind->add(':max_year', (int)$this->max_year, PDO::PARAM_INT);
            }
        }
        
        if($this->search_word!=''){
            $this->search_word=str_replace('　',' ',$this->search_word);
            $search_words=explode(' ',$this->search_word);
            $search_word_parts=[];
            foreach($search_words as $key=>$word){
                $search_word_parts[]="`race_name` LIKE :search_word_{$key}";
                $pre_bind->add(":search_word_{$key}", "%{$word}%", PDO::PARAM_STR);
            }
            $where_parts[]="(".implode(' OR ',$search_word_parts).")";
        }

        $race_course_array=[];
        if($this->race_course!=''){
            $race_course_array=(function($race_course_str){
                $race_course_str=str_replace('　',' ',$race_course_str);
                $race_course_str=str_replace(',',' ',$race_course_str);
                $input_race_course_array=explode(' ',$race_course_str);

                $race_course_array=[];
                foreach($input_race_course_array as $val){
                    if($val==''){ continue; }
                    $race_course_array[]=$val;
                }
                return $race_course_array;
            })($this->race_course);
            $this->race_course=implode(' ',$race_course_array);
    
            $race_course_parts=[];
            foreach($race_course_array as $key=>$val){
                if($val==''){
                    continue;
                }
                $race_course_parts[]=":race_course_{$key}";
                $pre_bind->add(":race_course_{$key}", $val, PDO::PARAM_STR);
            }
            $whrer_or_parts=[
                "`race_course_name` IN (".implode(', ',$race_course_parts).")",
                "c.`short_name` IN (".implode(', ',$race_course_parts).")",
                "c.`short_name_m` IN (".implode(', ',$race_course_parts).")",
            ];
            $where_parts[]="(".implode(' OR ',$whrer_or_parts).")";
        }

        $distance_array=[];
        if($this->distance!=''){
            $distance_array=(function($distance_str){
                $distance_str=str_replace('　',' ',$distance_str);
                $distance_str=str_replace(',',' ',$distance_str);
                $input_distance_array=explode(' ',$distance_str);

                $distance_array=[];
                foreach($input_distance_array as $val){
                    $v=(int)$val;
                    if($v===0){ continue; }
                    $distance_array[]=$v;
                }
                return $distance_array;
            })($this->distance);
            $this->distance=implode(' ',$distance_array);
    
            $distance_parts=[];
            foreach($distance_array as $key=>$val){
                if(intval($val)===0){
                    continue;
                }
                $distance_parts[]=":distance_{$key}";
                $pre_bind->add(":distance_{$key}", $val>=100?$val:($val*100), PDO::PARAM_INT);
            }
            $where_parts[]="`distance` IN (".implode(', ',$distance_parts).")";
        }
        // コース種類
        $parts=[];
        if($this->course_type_tf){
            $parts[]="`course_type` LIKE '芝'";
        }
        if($this->course_type_dt){
            $parts[]="`course_type` LIKE 'ダ'";
        }
        if($this->course_type_hd){
            $parts[]="`course_type` LIKE '障'";
        }
        $parts_count=count($parts);
        if($parts_count>0 && $parts_count<3){
            // 3種ともオンなら検索条件から除外
            $where_parts[]='('.implode(' OR ',$parts).')';
        }
        
        // すべてtrue・すべてfalse以外の場合に絞り込み
        $is_all_true=$this->show_organization_jra & $this->show_organization_nar & $this->show_organization_other;
        $is_all_false=!($this->show_organization_jra|$this->show_organization_nar|$this->show_organization_other);
        if(!$is_all_true && !$is_all_false){
            if(!$this->show_organization_jra){
                // JRAを含まない＝地方＋海外
                $where_parts[]="`is_jra`=0";
                if($this->show_organization_nar && !$this->show_organization_other){
                    // 地方のみONである
                    $where_parts[]="`is_nar`=1";
                }
                if(!$this->show_organization_nar && $this->show_organization_other){
                    // 海外のみ
                    $where_parts[]="`is_nar`=0";
                }
            }else{
                // JRAを含む
                if($this->show_organization_nar){
                    // JRA＋地方
                    $where_parts[]="(`is_jra`=1 OR `is_nar`=1)";
                }else if($this->show_organization_other){
                    // JRA＋海外
                    $where_parts[]="(`is_jra`=1 OR `is_nar`=0)";
                }else{
                    // 地方も海外もオンでない場合、JRAのみ
                    $where_parts[]="`is_jra`=1";
                }
            }
        }
        // ▽age
        $age_where_parts=(function($flags){
            $parts=[];
            if($flags->hasFlag(self::Age20)){ $parts[]="age.search_id=20";}
            if($flags->hasFlag(self::Age30)){ $parts[]="age.search_id=30";}
            if($flags->hasFlag(self::Age31)){ $parts[]="age.search_id=31";}
            if($flags->hasFlag(self::Age41)){ $parts[]="age.search_id=41";}
            return $parts;
        })(new FlagChecker($this->age));
        if(count($age_where_parts)>0 && count($age_where_parts)<4){
            $where_parts[]="(".implode(" OR ",$age_where_parts).")";
        }
        // ▽グレード
        $grade_where_parts=(function($flags){
            $parts=[];
            if($flags->hasFlag(self::Grade_G1)){
                $parts[]="g.search_grade LIKE 'G1'";
                $parts[]="`grade` LIKE 'G1'";
            }
            if($flags->hasFlag(self::Grade_G2)){
                $parts[]="g.search_grade LIKE 'G2'";
                $parts[]="`grade` LIKE 'G2'";
            }
            if($flags->hasFlag(self::Grade_G3)){
                $parts[]="g.search_grade LIKE 'G3'";
                $parts[]="`grade` LIKE 'G3'";
            }
            if($flags->hasFlag(self::Grade_G)) {
                $parts[]="g.search_grade LIKE '重賞'";
                $parts[]="`grade` LIKE '重賞'";
            }
            if($flags->hasFlag(self::Grade_L)) {
                $parts[]="g.search_grade LIKE 'L'";
                $parts[]="`grade` LIKE 'L'";
            }
            if($flags->hasFlag(self::Grade_OP)){
                $parts[]="g.search_grade LIKE 'OP'";
                $parts[]="`grade` LIKE 'OP'";
            }
            if($flags->hasFlag(self::Grade_W1)){
                $parts[]="g.search_grade LIKE '1勝'";
                $parts[]="`grade` LIKE '1勝'";
            }
            if($flags->hasFlag(self::Grade_W2)){
                $parts[]="g.search_grade LIKE '2勝'";
                $parts[]="`grade` LIKE '2勝'";
            }
            if($flags->hasFlag(self::Grade_W3)){
                $parts[]="g.search_grade LIKE '3勝'";
                $parts[]="`grade` LIKE '3勝'";
            }
            if($flags->hasFlag(self::Grade_Maiden)){
                $parts[]="g.search_grade LIKE '未勝' OR g.search_grade LIKE '新馬'";
                $parts[]="`grade` LIKE '未勝' OR `grade` LIKE '新馬'";
            }
            return $parts;
        })(new FlagChecker($this->grade));
        // グレード条件が「全件でない」場合はに条件絞り込み（0件と全件は省く）
        if(count($grade_where_parts)>0 && $this->grade!=self::Grade_All){
            $where_parts[]="(".implode(" OR ",$grade_where_parts).")";
        }
        // △グレード

        if(!$this->show_disabled){
            $where_parts[]="r.`is_enabled`=1";
        }
        
        if($this->world_id>0){
            $where_parts[]="r.`world_id`=:world_id";
            $pre_bind->add(':world_id', $this->world_id, PDO::PARAM_INT);
        }
        // WHERE文結合
        $sql_where='';
        if(count($where_parts)>0){
            $sql_where="WHERE ".implode(" AND ",$where_parts);
        }
        $limit_offset_part='';
        if($this->limit>0){
            $limit_offset_part=" LIMIT {$this->limit}";
            $limit_offset_part.=" OFFSET ".($this->page_number*$this->limit);
        }
        $week_tbl=RaceWeek::TABLE;
        $age_tbl=RaceCategoryAge::TABLE;
        $grade_tbl=RaceGrade::TABLE;
        $course_mst_tbl=RaceCourse::TABLE;
        $sql=<<<END
        SELECT
            r.*
            ,w.month AS 'w_month'
            ,w.umm_month_turn
            ,g.short_name as grade_short_name
            ,g.css_class_suffix as grade_css_class_suffix
            ,c.short_name as race_course_mst_short_name
            ,c.short_name_m as race_course_mst_short_name_m
        FROM `{$tbl}` AS r
        LEFT JOIN `{$week_tbl}` as w ON r.week_id=w.id
        LEFT JOIN `{$age_tbl}` as age ON r.age_category_id=age.id
        LEFT JOIN `{$grade_tbl}` as g ON r.grade LIKE g.unique_name AND g.is_enabled=1
        LEFT JOIN `{$course_mst_tbl}` as c ON r.race_course_name LIKE c.unique_name AND c.is_enabled=1
        {$sql_where}
        ORDER BY
        `year` ASC,
        IFNULL(w.`month`,r.`month`) ASC,
        w.`sort_number` ASC,
        `date` ASC,
        `race_course_name` ASC, `race_number` ASC {$limit_offset_part};
        END;
        $stmt = $pdo->prepare($sql);
        $pre_bind->bindTo($stmt);
        $stmt->execute();
        return $stmt;
    }
    /**
     * 検索フォームをHTML出力
     */
    public function printForm($page, $detail_tgl_is_enabled=false, $detail_tgl_is_default_open=null){
        if($detail_tgl_is_default_open===null){
            $detail_tgl_is_default_open=($this->search_detail_is_enabled?true:false);
        }
        ?>
<form action="<?php echo $page->to_race_list_path; ?>" method="get">
<fieldset>
<?php if($detail_tgl_is_enabled): ?>
<input type="button" class="search_detail_tgl_btn" value="<?php
    if($detail_tgl_is_default_open){
        echo "▲ 詳細格納";
    }else{
        echo "▼ 詳細展開";
    }
?>"><?php endif; ?>
<input type="hidden" name="search_detail_tgl_status" class="search_detail_tgl_status" value="<?php echo $detail_tgl_is_default_open?'open':'close'; ?>">
<input type="text" name="search_word" style="width:200px;height:1.5em;" placeholder="レース名称" value="<?php echo $this->search_word; ?>">
<input type="submit" value="検索実行">
<input type="button" value="クリア" onclick="clearElmVal('*[name=search_word]');"><br>
<div class="search_detail" style="<?php if($detail_tgl_is_default_open===false){echo "display:none;";} ?>">
<hr>
<?php
$year_min=$this->setting->select_zero_year - $this->setting->year_select_min_diff;
$year_max=$this->setting->select_zero_year + $this->setting->year_select_max_diff;

?><!--<select name="min_year" style="width:6em;height:2em;">
<?php
    echo '<option value=""></option>'."\n";
    for($i=$year_min; $i<=$year_max; $i++){
        echo '<option value="'.$i,'"'.(($i==$this->min_year)?' selected ':'').'>';
        echo $this->setting->getYearSpecialFormat($i);
        if($this->setting->year_view_mode==0){ echo "年"; }
        if($this->setting->year_view_mode==2){ echo "年"; }
        echo '</option>'."\n";
    }
?></select> ～ -->
<select name="year" style="width:6em;height:2em;" onchange="clearElmVal('*[name=year_raw]');">
<?php
    echo '<option value=""></option>'."\n";
    $year_exists=false;
    for($i=$year_min; $i<=$year_max; $i++){
        echo '<option value="'.$i,'"'.(($i==$this->year)?' selected ':'').'>';
        if($i==$this->year){$year_exists=true;}
        echo $this->setting->getYearSpecialFormat($i);
        if($this->setting->year_view_mode==0){ echo "年"; }
        if($this->setting->year_view_mode==2){ echo "年"; }
        echo '</option>'."\n";
    }
?></select>／
<input name="year_raw" type="number" style="width:4em;height:1.5em;" value="<?php echo $year_exists?'':$this->year; ?>" onchange="clearElmVal('*[name=year]');">年
　<input type="button" value="年度をクリア" onclick="clearElmVal('*[name=year]');clearElmVal('*[name=year_raw]');">
<hr>
<div oncontextmenu="return false;">
<span>
<input type="hidden" name="show_organization_jra" value="0">
<input type="hidden" name="show_organization_nar" value="0">
<input type="hidden" name="show_organization_other" value="0">
<?php $cbox=MkTagInput::Checkbox('show_organization_',1); ?>
<label oncontextmenu="reset_and_checked('show_organization_','jra');"><?php print $cbox->name_s('jra')->checked($this->show_organization_jra); ?>中央競馬</label>
<label oncontextmenu="reset_and_checked('show_organization_','nar');"><?php print $cbox->name_s('nar')->checked($this->show_organization_nar); ?>地方競馬</label>
<label oncontextmenu="reset_and_checked('show_organization_','other');"><?php print $cbox->name_s('other')->checked($this->show_organization_other); ?>その他（海外）</label>
</span>
｜<label><?php print(MkTagInput::Checkbox('is_generation_search',1)->checked((bool)$this->is_generation_search)); ?>指定年に3歳の世代</label>
<br>
<span style="white-space:nowrap;">（
<input type="hidden" name="age[20]" value="0">
<input type="hidden" name="age[30]" value="0">
<input type="hidden" name="age[31]" value="0">
<input type="hidden" name="age[41]" value="0">
<?php
$cbox=MkTagInput::Checkbox('',1)->class('age_btn');
$a_flags=new FlagChecker($this->age);
?>
<label oncontextmenu="reset_and_checked('age[','20]');uncheck_if_checked('age[20]','is_generation_search');" onchange="uncheck_if_checked('age[20]','is_generation_search');"><?php print $cbox->name('age[20]')->checked($a_flags->hasFlag(self::Age20)); ?>2歳</label>
<label oncontextmenu="reset_and_checked('age[','30]');uncheck_if_checked('age[30]','is_generation_search');" onchange="uncheck_if_checked('age[30]','is_generation_search');"><?php print $cbox->name('age[30]')->checked($a_flags->hasFlag(self::Age30)); ?>3歳</label>
<label oncontextmenu="reset_and_checked('age[','31]');uncheck_if_checked('age[31]','is_generation_search');" onchange="uncheck_if_checked('age[31]','is_generation_search');"><?php print $cbox->name('age[31]')->checked($a_flags->hasFlag(self::Age31)); ?>3上</label>
<label oncontextmenu="reset_and_checked('age[','41]');uncheck_if_checked('age[41]','is_generation_search');" onchange="uncheck_if_checked('age[41]','is_generation_search');"><?php print $cbox->name('age[41]')->checked($a_flags->hasFlag(self::Age41)); ?>4上</label>
）</span>
<span style="white-space:nowrap;" oncontextmenu="return false;">
<input class="age_preset_3" type="button" value="3歳世代">
<input class="age_preset_4" type="button" value="古馬">
<input class="age_reset_button" type="button" value="全馬齢">
</span></div>
<hr>
<div oncontextmenu="return false;">
<?php
$cbox=MkTagInput::Checkbox();
$cbox->value(1)->class('grd_btn_g');
?>
<span style="white-space:nowrap;">（
<input type="hidden" name="grade_g1" value="0">
<label oncontextmenu="reset_and_checked('grade_','g1');"><?php
$g_flags=new FlagChecker($this->grade);
echo $cbox->name('grade_g1')->checked($g_flags->hasFlag(self::Grade_G1)); ?>G1</label>
<input type="hidden" name="grade_g2" value="0">
<label oncontextmenu="reset_and_checked('grade_','g2');"><?php
echo $cbox->name('grade_g2')->checked($g_flags->hasFlag(self::Grade_G2)); ?>G2</label>
<input type="hidden" name="grade_g3" value="0">
<label oncontextmenu="reset_and_checked('grade_','g3');"><?php
echo $cbox->name('grade_g3')->checked($g_flags->hasFlag(self::Grade_G3)); ?>G3</label>
<input type="hidden" name="grade_gn" value="0">
<label oncontextmenu="reset_and_checked('grade_','gn');"><?php
echo $cbox->name('grade_gn')->checked($g_flags->hasFlag(self::Grade_G)); ?>無格付</label>
<input type="hidden" name="grade_op" value="0">
<label  oncontextmenu="reset_and_checked('grade_','op');"><?php
echo $cbox->class()->name('grade_op')->checked($g_flags->hasFlag(self::Grade_OP)); ?>L・OP</label>
</span>
<span style="white-space:nowrap;">/
<?php $cbox->class('grd_btn_jouken'); ?>
<input type="hidden" name="grade_1w" value="0">
<label oncontextmenu="reset_and_checked('grade_','1w');"><?php
echo $cbox->name('grade_1w')->checked($g_flags->hasFlag(self::Grade_W1)); ?>1勝</label>
<input type="hidden" name="grade_2w" value="0">
<label oncontextmenu="reset_and_checked('grade_','2w');"><?php
echo $cbox->name('grade_2w')->checked($g_flags->hasFlag(self::Grade_W2)); ?>2勝</label>
<input type="hidden" name="grade_3w" value="0">
<label oncontextmenu="reset_and_checked('grade_','3w');"><?php
echo $cbox->name('grade_3w')->checked($g_flags->hasFlag(self::Grade_W3)); ?>3勝</label>
</span><span style="white-space:nowrap;">/
<input type="hidden" name="grade_maiden" value="0">
<label oncontextmenu="reset_and_checked('grade_','maiden');"><input type="checkbox" name="grade_maiden" value="1"<?php HTPrint::Checked($g_flags->hasFlag(self::Grade_Maiden)); ?>>未勝利</label>
）</span><br>
<input class="grade_juushou_tgl" type="button" value="重賞切替">
<input class="grade_open_tgl" type="button" value="オープン以上切替">
<input class="grade_jouken_tgl" type="button" value="条件戦切替">
｜<input class="grade_reset_btn" type="button" value="格クリア">
</div>
<hr>
競馬場：<input type="text" name="race_course" style="width:200px;height:1.5em;" placeholder="競馬場（スペース・カンマ区切り）" value="<?php echo $this->race_course; ?>">
<input type="button" value="競馬場をクリア" onclick="clearElmVal('*[name=race_course]');"><br>
<span style="white-space:nowrap;" oncontextmenu="return false;">（
<input type="hidden" name="course_type_tf" value="0">
<input type="hidden" name="course_type_dt" value="0">
<input type="hidden" name="course_type_hd" value="0">
<label oncontextmenu="reset_and_checked('course_type_','tf');"><input type="checkbox" name="course_type_tf" class="" value="1"<?php HTPrint::CheckedIfNotEmpty($this->course_type_tf); ?>>芝</label>
<label oncontextmenu="reset_and_checked('course_type_','dt');"><input type="checkbox" name="course_type_dt" class="" value="1"<?php HTPrint::CheckedIfNotEmpty($this->course_type_dt); ?>>ダート</label>
<label oncontextmenu="reset_and_checked('course_type_','hd');"><input type="checkbox" name="course_type_hd" class="" value="1"<?php HTPrint::CheckedIfNotEmpty($this->course_type_hd); ?>>障害</label>
）</span><input class="course_type_reset_button" type="button" value="全て" onclick="course_type_reset();"><br>
距離　：<input type="text" name="distance" style="width:200px;height:1.5em;" placeholder="距離（スペース・カンマ区切り）" value="<?php echo $this->distance; ?>">
<input type="button" value="距離をクリア" onclick="clearElmVal('*[name=distance]');">
<hr>
<span>年未指定時の範囲（<input type="number" name="min_year" value="<?php print $this->min_year; ?>" style="width:4em;">
～
<input type="number" name="max_year" value="<?php print $this->max_year; ?>" style="width:4em;">）</span>
<input type="hidden" name="max_year_is_enabled_for_generation_search" value="0">
<label title="世代検索に上限適用：上限のほうが4歳年より小さい場合は4歳年まで表示"><input type="checkbox" name="max_year_is_enabled_for_generation_search" value="1"<?php HTPrint::CheckedIfNotEmpty($this->max_year_is_enabled_for_generation_search); ?>>世代検索にも上限を適用（超過時は4歳年度）</label><br>
1ページあたり：<select name="limit">
<?php
foreach([20=>20, 24=>"24（中央平地G1）", 25=>25, 26=>"26（中央G1）", 30=>30, 50=>50,100=>100,150=>150, 0=>'無制限'] as $key=>$val){
    $selected=($this->limit==$key?' selected':'');
    echo "<option value=\"{$key}\"{$selected}>{$val}</option>\n";
}
?>
</select>件
<input type="hidden" name="page" value="0">
<input type="hidden" name="session_is_not_update" value="0">
<span style="white-space:nowrap;"><label><input type="checkbox" name="session_is_not_update" value="1">条件をセッションに保存しない</label></span>
<span style="white-space:nowrap;"><label><input type="checkbox" name="show_disabled" value="1"<?php HTPrint::CheckedIfNotEmpty($this->show_disabled); ?>>非表示レースを表示</label></span>
<hr>
<span style="white-space:nowrap;"><a href="?search_reset=1">[初期化して開きなおす]</a></span>
</div>
</fieldset>
</form><?php
    }
    /**
     * ページネーションのHTMLを出力
     */
    public function printPagination(){
        if($this->limit==0){return false;}
        $prev = (int)$this->page_number - 1;
        $next = 1+(int)$this->page_number;
        ?>
<?php if($this->page_number>0): ?>
<a href="?page=0&<?php echo $this->getUrlParam(['page']); ?>">[先頭]</a>
<a href="?page=<?php echo $prev; ?>&<?php echo $this->getUrlParam(['page']); ?>">[前のページ]</a>
<?php else: ?>
[先頭] [前のページ]
<?php endif; ?>
<a> [<?php echo str_pad($this->page_number+1,2,'0',STR_PAD_LEFT); ?>]</a>
<?php if(
    !is_null($this->current_page_results_count)
    &&(
        $this->current_page_results_count < $this->limit
        || $this->current_page_results_count===0
        )): ?>
[次のページ]
<?php else: ?>
<a href="?&page=<?php echo $next; ?>&<?php echo $this->getUrlParam(['page']); ?>">[次のページ]</a>
<?php endif; ?>
<?php
    }
    public function getSearchParamStr(){
        $params=[];
        if($this->search_word){
            $params[]="キーワード[".$this->search_word."]";
        }
        if($this->year){
            if($this->is_generation_search){
                $params[]="世代[".$this->year."年3歳]";
            }else{
                $params[]="年度[".$this->year."]";
            }
        }
        $organization=[];
        if($this->show_organization_jra){ $organization[]="中央"; }
        if($this->show_organization_nar){ $organization[]="地方"; }
        if($this->show_organization_other){ $organization[]="海外"; }
        if(count($organization)>0){
            $params[]="開催[".implode(',',$organization)."]";
        }
        $ages=(function($a){
            $name_list=[
                self::Age20=>"2歳",
                self::Age30=>"3歳",
                self::Age31=>"3上",
                self::Age41=>"4上",
            ];
            $ages=[];
            foreach($name_list as $flag=>$name){
                if($a & $flag){ $ages[]=$name; }
            }
            return $ages;
        })($this->age);
        if(count($ages)>0){
            $params[]="馬齢条件[".implode(',',$ages)."]";
        }
        $grades=(function($g){
            $grade_name_list=[
                self::Grade_G1=>"G1",
                self::Grade_G2=>"G2",
                self::Grade_G3=>"G3",
                self::Grade_G=>"重賞",
                self::Grade_OP=>"OP・L",
                self::Grade_W1=>"1勝",
                self::Grade_W2=>"2勝",
                self::Grade_W3=>"3勝",
                self::Grade_Maiden=>"新馬・未勝利",
            ];
            $grades=[];
            foreach($grade_name_list as $flag=>$name){
                if($g & $flag){ $grades[]=$name; }
            }
            return $grades;
        })($this->grade);
        if(count($grades)>0){
            $params[]="格付[".implode(',',$grades)."]";
        }
        if($this->race_course){
            $params[]="競馬場[".$this->race_course."]";
        }
        $c_type=[];
        if($this->course_type_tf){ $c_type[]="芝"; }
        if($this->course_type_dt){ $c_type[]="ダ"; }
        if($this->course_type_hd){ $c_type[]="障"; }
        if(count($c_type)>0){
            $params[]="区分[".implode(',',$c_type)."]";
        }
        if($this->distance){
            $params[]="距離[".$this->distance."]";
        }
        return implode(', ',$params);
    }
    public function selectRaceIdList(array $race_id_list){

    }
}
