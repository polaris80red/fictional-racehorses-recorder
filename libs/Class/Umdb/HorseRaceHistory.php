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
    protected string $horse_id='';

    protected PDO $pdo;
    protected PDOStatement $stmt;
    protected RaceResults1stOr2ndHourseGetter $rr12HourseGetter;
    // 騎手のキー=>インスタンスのキャッシュ
    protected array $jockey_list;
    protected JockeyRow $empty_jockey;
    // グレードのキー=>インスタンスのキャッシュ
    protected array $grade_list;
    protected RaceGradeRow $empty_grade;
    // コースのキー=>インスタンスのキャッシュ
    protected array $course_list;
    protected RaceCourseRow $empty_course;

    protected $date_order='ASC';

    public $race_count_1=0;
    public $race_count_2=0;
    public $race_count_3=0;
    public $race_count_4=0;
    public $race_count_5=0;
    public $race_count_n=0;
    public $race_count_all=0;

    public $has_unregistered_race_results=false;

    public function __construct(PDO $pdo,string $horse_id){
        $this->pdo = $pdo;
        $this->horse_id = $horse_id;

        $this->rr12HourseGetter=new RaceResults1stOr2ndHourseGetter($pdo);
        $this->jockey_list=[];
        $this->empty_jockey=new JockeyRow();
        $this->grade_list=[];
        $this->empty_grade=new RaceGradeRow();
        $this->course_list=[];
        $this->empty_course=new RaceCourseRow();
    }
    public function setDateOrder($order){
        if($order==='DESC'){
            $this->date_order='DESC';
        }else{
            $this->date_order='ASC';
        }
        return $this;
    }
    public function getData(){
        $date_order=$this->date_order;
        # レース着順取得
        $sql=(function()use($date_order){
            $race_tbl=Race::TABLE;
            $race_results_tbl=RaceResults::TABLE;
            $race_week_tbl=RaceWeek::TABLE;
            $grade_tbl=RaceGrade::TABLE;
            $course_mst_tbl=RaceCourse::TABLE;
            $race_special_results_tbl=RaceSpecialResults::TABLE;
            $jockey_tbl=Jockey::TABLE;

            $jockey_select_col=Jockey::getPrefixedSelectClause('jk');
            $race_select_col=Race::getPrefixedSelectClause('race');
            $course_select_col=RaceCourse::getPrefixedSelectClause('c');
            $grade_select_col=RaceGrade::getPrefixedSelectClause('g');
            $sql=<<<END
            SELECT
            `race_results`.`race_id`
            ,`race_results`.`result_number`
            ,`race_results`.`result_text`
            ,`race_results`.`result_order`
            ,`race_results`.`result_before_demotion`
            ,`race_results`.`jockey_unique_name`
            ,`race_results`.`handicap`
            ,`race_results`.`frame_number`
            ,`race_results`.`horse_number`
            ,`race_results`.`favourite`
            ,`spr`.`is_registration_only`
            ,`race_results`.`non_registered_prev_race_number`
            ,`race_results`.`jra_thisweek_horse_1`
            ,`race_results`.`jra_thisweek_horse_2`
            ,`race_results`.`tc`
            ,`race_results`.`trainer_unique_name`
            ,`race_results`.`training_country`
            ,`race_results`.`sex`
            ,`race_results`.`is_affliationed_nar`
            ,`race_results`.`race_id`
            ,{$race_select_col}
            ,{$jockey_select_col}
            ,w.month AS `w_month`
            ,w.umm_month_turn
            ,{$grade_select_col}
            ,{$course_select_col}
            ,`spr`.short_name_2 as special_result_short_name_2
            ,IFNULL(`spr`.is_excluded_from_race_count,0) AS is_excluded_from_race_count
            FROM `{$race_results_tbl}` AS `race_results`
            LEFT JOIN `{$race_tbl}` AS `race` ON `race`.`race_id`=`race_results`.`race_id`
            LEFT JOIN `{$race_week_tbl}` AS w ON `race`.`week_id` = w.id
            LEFT JOIN `{$grade_tbl}` as g ON `race`.grade LIKE g.unique_name
            LEFT JOIN `{$course_mst_tbl}` as c ON `race`.race_course_name LIKE c.unique_name AND c.is_enabled=1
            LEFT JOIN `{$race_special_results_tbl}` as spr ON `race_results`.result_text LIKE `spr`.unique_name AND `spr`.is_enabled=1
            LEFT JOIN `{$jockey_tbl}` as `jk` ON `race_results`.`jockey_unique_name`=`jk`.`unique_name` AND `jk`.`is_enabled`=1
            WHERE `race_results`.`horse_id`=:horse_id
            ORDER BY
            `race`.`year` {$date_order},
            `race`.`month` {$date_order},
            `race`.`date` {$date_order};
            END;
            return $sql;
        })();

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':horse_id', $this->horse_id, PDO::PARAM_STR);
        $stmt->execute();
        $this->stmt=$stmt;

        while (($row = $this->fetch())!==false){
            $this->data[]=$row;
        }
        return;
    }
    protected function fetch(){
        if(($data = $this->stmt->fetch(PDO::FETCH_ASSOC))===false){
            return false;
        }
        $row=new HorseRaceHistoryRow();
        $row->setByArray($data);

        // 騎手行をセット
        if($row->jockey_unique_name==''){
            $row->jockey_row=$this->empty_jockey;
        }else if(isset($this->jockey_list[$row->jockey_unique_name])){
            $row->jockey_row = $this->jockey_list[$row->jockey_unique_name];
        }else{
            $this->jockey_list[$row->jockey_unique_name]=(new JockeyRow())->setFromArray($data,Jockey::TABLE."__");
            $row->jockey_row = $this->jockey_list[$row->jockey_unique_name];
        }
        // レース行をセット
        $row->race_row = (new RaceRow())->setFromArray($data,Race::TABLE."__");
        // グレード行をセット
        if($row->race_row->grade==''){
            $row->grade_row=$this->empty_grade;
        }else if(isset($this->grade_list[$row->race_row->grade])){
            $row->grade_row = $this->grade_list[$row->race_row->grade];
        }else{
            $this->grade_list[$row->race_row->grade]=(new RaceGradeRow())->setFromArray($data,RaceGrade::TABLE."__");
            $row->grade_row = $this->grade_list[$row->race_row->grade];
        }
        // コース行のセット
        if($row->race_row->race_course_name==''){
            $row->course_row=$this->empty_course;
        }else if(isset($this->course_list[$row->race_row->race_course_name])){
            $row->course_row = $this->course_list[$row->race_row->race_course_name];
        }else{
            $this->course_list[$row->race_row->race_course_name]=(new RaceCourseRow())->setFromArray($data,RaceCourse::TABLE."__");
            $row->course_row = $this->course_list[$row->race_row->race_course_name];
        }
        $res= $this->rr12HourseGetter->get($data['race_id'],$data['result_number'],$this->horse_id);
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
        return $row;
    }
}
