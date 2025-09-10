<?php
/**
 * レース結果・出馬表類のページの行クラス
 */
class RaceResultsPageRow{
    const IMPORT_PARAM_NAMES=[
    ];
    public HorseRow $horseRow;
    public RaceResultsRow $resultRow;
    public JockeyRow $jockeyRow;
    public TrainerRow $trainerRow;
    public RaceSpecialResultsRow $specialResultRow;
    public $age;
    public $sex;
    public $sexStr;
    public $jockeyName='';
    public $tc='';
    public $trainerName='';
    public $trainingCountry='';

    // 指定したパラメータだけを取り込む
    public function setByArray(array $row_data){
        $import_params=array_fill_keys(self::IMPORT_PARAM_NAMES,0);
        $row_data=array_intersect_key($row_data,$import_params);
        foreach($row_data as $key => $value){
            $this->$key=$value;
        }
    }
}
