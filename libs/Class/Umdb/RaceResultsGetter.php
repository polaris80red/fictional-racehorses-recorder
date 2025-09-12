<?php
/**
 * 出馬表・レース結果など1レース分のレコードの取得処理
 */
class RaceResultsGetter{
    protected PDO $pdo;
    private string $raceId;
    private PDOStatement $stmt;
    public $where_and_parts=[];
    public $order_parts=[];

    public bool $pageIsEditable=false;
    private int $raceYear;

    public function __construct(PDO $pdo, string $raceId, int $raceYear) {
        $this->pdo=$pdo;
        $this->raceId=$raceId;
        $this->raceYear=$raceYear;
    }
    public function addOrder(string $order){ $this->order_parts[]=$order; }
    public function addOrderParts(array $order_parts){
        $this->order_parts=array_merge($this->order_parts,$order_parts);
    }
    private function execute(){
        # レース着順取得
        $horse_tbl=Horse::TABLE;
        $race_tbl=Race::TABLE;
        $r_results_tbl=RaceResults::TABLE;
        $race_special_results_tbl=RaceSpecialResults::TABLE;
        $jockey_tbl=Jockey::TABLE;
        $trainer_tbl=Trainer::TABLE;

        $horse_select_clause=Horse::getPrefixedSelectClause('horse');
        $rr_select_clause=RaceResults::getPrefixedSelectClause('r_results');
        $spr_select_clause=RaceSpecialResults::getPrefixedSelectClause('spr');
        $jockey_select_clause=Jockey::getPrefixedSelectClause('jk');
        $race_trainer_select_clause=Trainer::getPrefixedSelectClause('r_trainer','r_trainer__');
        $horse_trainer_select_clause=Trainer::getPrefixedSelectClause('h_trainer','h_trainer__');

        $sql_part_select_columns=implode(",\n",[
            $rr_select_clause,
            $horse_select_clause,
            $jockey_select_clause,
            $race_trainer_select_clause,
            $horse_trainer_select_clause,
            $spr_select_clause,
        ]);
        
        $sql=<<<END
        SELECT
        {$sql_part_select_columns}
        FROM `{$race_tbl}` AS `race`
        LEFT JOIN `{$r_results_tbl}` AS `r_results`
            ON `race`.`race_id`=`r_results`.`race_id`
        LEFT JOIN `{$horse_tbl}` AS `horse`
            ON `r_results`.`horse_id`=`horse`.`horse_id`
        LEFT JOIN `{$race_special_results_tbl}` as spr
            ON `r_results`.result_text LIKE spr.unique_name AND spr.is_enabled=1
        LEFT JOIN `{$jockey_tbl}` as `jk`
            ON `r_results`.`jockey_name`=`jk`.`unique_name` AND `jk`.`is_enabled`=1
        LEFT JOIN `{$trainer_tbl}` as `h_trainer`
            ON `horse`.`trainer_unique_name`=`h_trainer`.`unique_name` AND `h_trainer`.`is_enabled`=1
        LEFT JOIN `{$trainer_tbl}` as `r_trainer`
            ON `r_results`.`trainer_unique_name`=`r_trainer`.`unique_name` AND `r_trainer`.`is_enabled`=1
        WHERE `r_results`.`race_id` LIKE :race_id
        END;
        //print_r($sql);exit;
        if(count($this->where_and_parts)>0){
            $sql.=" WHERE ".implode(' AND ',$this->where_and_parts);
        }
        if(count($this->order_parts)>0){
            $sql.=" ORDER BY ".implode(',',$this->order_parts);
        }
        $this->stmt=$this->pdo->prepare($sql);
        $this->stmt->bindValue('race_id',SqlValueNormalizer::escapeLike($this->raceId),PDO::PARAM_STR);
        $this->stmt->execute();
        return $this->stmt;
    }
    public function getTableData(){
        $this->execute();
        $table_data=[];
        while ($data = $this->stmt->fetch(PDO::FETCH_ASSOC)) {
            $row=new RaceResultsPageRow();
            $horse=(new HorseRow())->setFromArray($data, Horse::TABLE.'__');
            $raceResult=(new RaceResultsRow())->setFromArray($data, RaceResults::TABLE.'__');
            $spr=(new RaceSpecialResultsRow())->setFromArray($data, RaceSpecialResults::TABLE.'__');
            $row->horseRow=$horse;
            $row->resultRow=$raceResult;
            $row->specialResultRow=$spr;

            $row->sex = $raceResult->sex?:$horse->sex;
            $row->sexStr=sex2String((int)$row->sex);
            $row->age=empty($horse->birth_year)?'':$this->raceYear-$horse->birth_year;

            // 騎手名のセット
            $jockey=(new JockeyRow())->setFromArray($data, Jockey::TABLE.'__');
            $row->jockeyName=$raceResult->jockey_name;
            if($jockey->is_enabled==1){
                if($jockey->is_anonymous==1){
                    $row->jockeyName=(!$this->pageIsEditable)?'□□□□':($jockey->short_name_10?:$data['jockey_name']);
                }else{
                    $row->jockeyName=$jockey->short_name_10?:$raceResult->jockey_name;
                }
            }
            $row->jockeyRow=$jockey;
            // レース側の所属がない場合は馬側の値を採用
            $row->tc=$raceResult->tc?:$horse->tc;
            // 調教師データのセット
            $race_trainer=(new TrainerRow())->setFromArray($data,'r_trainer__');
            $horse_trainer=(new TrainerRow())->setFromArray($data,'h_trainer__');
            $row->trainerName='';
            $row->trainerRow=new TrainerRow();
            if($raceResult->trainer_unique_name){
                if($race_trainer->is_anonymous==1){
                    $row->trainerName=(!$this->pageIsEditable)?'□□□□':($race_trainer->short_name_10?:$raceResult->trainer_unique_name);
                }else{
                    $row->trainerName=$race_trainer->short_name_10?:$raceResult->trainer_unique_name;
                }
                $row->trainerRow=$race_trainer;
            }else if($horse->trainer_unique_name){
                // レース側の調教師が空の場合は馬側の値を採用
                if($horse_trainer->is_anonymous==1){
                    $row->trainerName=(!$this->pageIsEditable)?'□□□□':($horse_trainer->short_name_10?:$horse->trainer_unique_name);
                }else{
                    $row->trainerName=$horse_trainer->short_name_10?:$horse->trainer_unique_name;
                }
                $row->trainerRow=$horse_trainer;
            }
            // レースの調教国が空の場合は馬側の値を採用
            $row->trainingCountry=$raceResult->training_country?:$horse->training_country;
            //$table_data[]=$data;
            $table_data[]=$row;
        }
        return $table_data;
    }
}
