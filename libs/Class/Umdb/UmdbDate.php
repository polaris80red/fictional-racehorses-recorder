<?php
/**
 * フォーマット済み日付を部分ごとに分割したまま保持し、必要に応じて個別に取り出せる
 */
class UmdbDate{
    public $year_or_age='';
    public $year_or_age_suffix='';

    public $year_or_age_m_separator='';

    public $month='';
    public $month_suffix='';

    public $month_dt_separator='';

    public $day_or_turn='';
    public $day_or_turn_suffix='';

    protected const PARAM_LIST=[
        'year_or_age',
        'year_or_age_suffix',
        'year_or_age_m_separator',
        'month',
        'month_suffix',
        'month_dt_separator',
        'day_or_turn',
        'day_or_turn_suffix',
    ];
    public function __toString(){
        return $this->getStrByParamNameList(self::PARAM_LIST);
    }
    public function getLimitedString($start_name=null,$end_name=null){
        $started=($start_name===null);
        $get_params=[];
        foreach(self::PARAM_LIST as $name){
            if(!$started){
                //開始前
                if($name===$start_name){
                    $started=true;
                }else{
                    continue; // startに一致するものが出るまでスキップ
                }
            }
            $get_params[]=$name;
            if($name===$end_name){
                // endに一致するものを取り出したら終了
                break;
            }
        }
        return $this->getStrByParamNameList($get_params);
    }
    public function getStrByParamNameList(array $param_names){
        $ret='';
        foreach($param_names as $name){
            $ret .= (string)$this->$name;
        }
        return $ret;
    }
}
