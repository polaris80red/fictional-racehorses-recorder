<?php
class HorseRaceHistory implements Iterator{
    private $pos = 0;
    public function rewind():void {
        $this->pos = 0;
    }
    /**
     * @return HorseRaceHistoryRow
     */
    public function current(): HorseRaceHistoryRow
    {
        return $this->data[$this->pos];
    }
    public function key():mixed
    {
        return $this->pos;
    }
    public function next(): void {
        ++$this->pos;
    }
    public function valid(): bool {
        return isset($this->data[$this->pos]);
    }
    protected $data=[];

    public $date_order='ASC';
    public $horse_id='';

    public $race_count_1=0;
    public $race_count_2=0;
    public $race_count_3=0;
    public $race_count_4=0;
    public $race_count_5=0;
    public $race_count_n=0;
    public $race_count_all=0;

    public $has_unregistered_race_results=false;

    public function setDateOrder($order){
        if($order==='DESC'){
            $this->date_order='DESC';
        }else{
            $this->date_order='ASC';
        }
        return $this;
    }
    public function getDataByHorseId(PDO $pdo, string $horse_id){
        $date_order=$this->date_order;
        # レース着順取得
        $sql=(function()use($date_order){
            $race_tbl=Race::TABLE;
            $race_results_tbl=RaceResults::TABLE;
            $race_week_tbl=RaceWeek::TABLE;
            $grade_tbl=RaceGrade::TABLE;
            $course_mst_tbl=RaceCourse::TABLE;
            $race_special_results_tbl=RaceSpecialResults::TABLE;
            $sql=<<<END
            SELECT
            `race`.`date`
            ,`race`.`race_course_name`
            ,`race`.`race_name`
            ,`race_results`.`result_number`
            ,`race_results`.`result_before_demotion`
            ,`race_results`.`result_text`
            ,`race_results`.`handicap`
            ,`race_results`.`frame_number`
            ,`race_results`.`favourite`
            ,`spr`.`is_registration_only`
            ,`race_results`.`non_registered_prev_race_number`
            ,`race_results`.`jra_thisweek_horse_1`
            ,`race_results`.`jra_thisweek_horse_2`
            ,`race`.`race_id`
            ,`race`.*
            ,w.month AS `w_month`
            ,w.umm_month_turn
            ,g.short_name as grade_short_name
            ,g.css_class_suffix as grade_css_class_suffix
            ,c.short_name as race_course_mst_short_name
            ,`spr`.short_name_2 as special_result_short_name_2
            ,IFNULL(`spr`.is_excluded_from_race_count,0) AS is_excluded_from_race_count
            FROM `{$race_results_tbl}` AS `race_results`
            LEFT JOIN `{$race_tbl}` AS `race` ON `race`.`race_id`=`race_results`.`race_results_id`
            LEFT JOIN `{$race_week_tbl}` AS w ON `race`.`week_id` = w.id
            LEFT JOIN `{$grade_tbl}` as g ON `race`.grade LIKE g.unique_name
            LEFT JOIN `{$course_mst_tbl}` as c ON `race`.race_course_name LIKE c.unique_name AND c.is_enabled=1
            LEFT JOIN `{$race_special_results_tbl}` as spr ON `race_results`.result_text LIKE `spr`.unique_name AND `spr`.is_enabled=1
            WHERE `race_results`.`horse_id`=:horse_id
            ORDER BY
            `race`.`year` {$date_order},
            `race`.`month` {$date_order},
            `race`.`date` {$date_order};
            END;
            return $sql;
        })();

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':horse_id', $horse_id, PDO::PARAM_STR);
        $stmt->execute();

        $table_data=[];
        # 自身以外の1着馬取得
        $rr12HourseGetter=new RaceResults1stOr2ndHourseGetter($pdo);

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row=new HorseRaceHistoryRow();
            $row->setByArray($data);

            if(empty($data['race_id'])){ continue; }
            $res= $rr12HourseGetter->get($data['race_id'],$data['result_number'],$horse_id);
            $row->r_horse_id=$data['r_horse_id']=isset($res['horse_id'])?$res['horse_id']:"";
            $row->r_name_ja=$data['r_name_ja'] =isset($res['name_ja'])?$res['name_ja']:"";
            $row->r_name_en=$data['r_name_en'] =isset($res['name_en'])?$res['name_en']:"";

            if($data['jra_thisweek_horse_1']||$data['jra_thisweek_horse_2']){
                $row->has_jra_thisweek = true;
            }

            # レース数カウント
            if($data['result_number']===1){
                $this->race_count_1+=1;
            }else if($data['result_number']===2){
                $this->race_count_2+=1;
            }else if($data['result_number']===3){
                $this->race_count_3+=1;
            }else if($data['result_number']===4){
                $this->race_count_4+=1;
            }else if($data['result_number']===5){
                $this->race_count_5+=1;
            }else{
                $this->race_count_n+=1;
            }
            $this->race_count_all+=1;
            // 除外判定が1のレコードはカウントしない
            if($data['is_excluded_from_race_count']){
                $this->race_count_n-=1;
                $this->race_count_all-=1;
            }
            if($data['non_registered_prev_race_number']>0){
                $this->race_count_n+=$data['non_registered_prev_race_number'];
                $this->race_count_all+=$data['non_registered_prev_race_number'];
                $this->has_unregistered_race_results=true;
            }
            $table_data[]=$data;

            $this->data[]=$row;
        }
    }
    public function getAllData(){
        return $this->data;
    }
}
