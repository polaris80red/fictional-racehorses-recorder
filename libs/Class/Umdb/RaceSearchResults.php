<?php
class RaceSearchResults{
    protected PDOStatement $stmt;

    // 繰り返し出現するマスタのキー=>インスタンスのキャッシュ
    protected array $grade_list;
    protected RaceGradeRow $empty_grade;
    protected array $course_list;
    protected RaceCourseRow $empty_course;
    protected array $week_list;
    protected RaceWeekRow $empty_week;

    public function __construct(PDOStatement $stmt){
        $this->stmt = $stmt;

        $this->grade_list=[];
        $this->empty_grade=new RaceGradeRow();
        $this->course_list=[];
        $this->empty_course=new RaceCourseRow();
        $this->week_list=[];
        $this->empty_week=new RaceWeekRow();
    }
    /**
     * 行を加工して取得
     */
    public function fetch():RaceSearchResultRow|false
    {
        $data = $this->stmt->fetch(PDO::FETCH_ASSOC);
        if($data === false){
            return false;
        }
        $row=new RaceSearchResultRow();
        $race=(new RaceRow())->setFromArray($data,Race::TABLE.'__');
        $row->raceRow=$race;
        $row->courseRow=$this->empty_course;
        if($race->race_course_name){
            if(!isset($this->course_list[$race->race_course_name])){
                $this->course_list[$race->race_course_name]=(new RaceCourseRow)->setFromArray($data,RaceCourse::TABLE.'__');
            }
            $row->courseRow=$this->course_list[$race->race_course_name];
        }
        $row->gradeRow=$this->empty_grade;
        if($race->grade){
            if(!isset($this->grade_list[$race->grade])){
                $this->grade_list[$race->grade]=(new RaceGradeRow)->setFromArray($data,RaceGrade::TABLE.'__');
            }
            $row->gradeRow=$this->grade_list[$race->grade];
        }
        $row->weekRow=$this->empty_week;
        if($race->week_id){
            if(!isset($this->week_list[$race->week_id])){
                $this->week_list[$race->week_id]=(new RaceWeekRow)->setFromArray($data,RaceWeek::TABLE.'__');
            }
            $row->weekRow=$this->week_list[$race->week_id];
        }
        $row->setByArray($data);
        return $row;
    }
    public function getAll(){
        $data=[];
        while(($row=$this->fetch())!==false){
            $data[]=$row;
        }
        return $data;
    }
}
