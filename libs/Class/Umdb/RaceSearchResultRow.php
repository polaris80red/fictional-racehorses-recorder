<?php
/**
 * レース検索結果形式のレース表の行クラス
 */
class RaceSearchResultRow{
    const IMPORT_PARAM_NAMES=[
    ];
    public RaceRow $raceRow;
    public RaceGradeRow $gradeRow;
    public RaceCourseRow $courseRow;
    public RaceWeekRow $weekRow;

    // 指定したパラメータだけを取り込む
    public function setByArray(array $row_data){
        $import_params=array_fill_keys(self::IMPORT_PARAM_NAMES,0);
        $row_data=array_intersect_key($row_data,$import_params);
        foreach($row_data as $key => $value){
            $this->$key=$value;
        }
    }
}
