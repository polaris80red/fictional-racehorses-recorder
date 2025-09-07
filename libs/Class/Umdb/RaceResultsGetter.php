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

        $horse_s_columns=new SqlMakeSelectColumns(Horse::TABLE);
        $horse_s_columns->addColumnsByArray([
            'name_ja','name_en','sex','birth_year'
        ]);
        $jockey_select_clause=Jockey::getPrefixedSelectClause('jk');
        $race_trainer_select_clause=Trainer::getPrefixedSelectClause('r_trainer','r_trainer__');
        $horse_trainer_select_clause=Trainer::getPrefixedSelectClause('h_trainer','h_trainer__');
        $horse_s_columns->addColumnAs('tc','horse_tc');
        $horse_s_columns->addColumnAs('trainer_unique_name','horse_trainer_unique_name');
        $horse_s_columns->addColumnAs('training_country','horse_training_country');
        $horse_s_columns->addColumnAs('is_affliationed_nar','horse_is_affliationed_nar');
        $sql_part_select_columns=implode(",\n",[
            "`r_results`.*",
            $horse_s_columns->get(true),
            "`race`.*",
            "`spr`.`is_registration_only`",
            $jockey_select_clause,
            $race_trainer_select_clause,
            $horse_trainer_select_clause,
        ]);
        
        $sql=<<<END
        SELECT
        {$sql_part_select_columns}
        FROM `{$race_tbl}` AS `race`
        LEFT JOIN `{$r_results_tbl}` AS `r_results`
            ON `race`.`race_id`=`r_results`.`race_id`
        LEFT JOIN `{$horse_tbl}`
            ON `r_results`.`horse_id`=`{$horse_tbl}`.`horse_id`
        LEFT JOIN `{$race_special_results_tbl}` as spr
            ON `r_results`.result_text LIKE spr.unique_name AND spr.is_enabled=1
        LEFT JOIN `{$jockey_tbl}` as `jk`
            ON `r_results`.`jockey_unique_name`=`jk`.`unique_name` AND `jk`.`is_enabled`=1
        LEFT JOIN `{$trainer_tbl}` as `h_trainer`
            ON `{$horse_tbl}`.`trainer_unique_name`=`h_trainer`.`unique_name` AND `h_trainer`.`is_enabled`=1
        LEFT JOIN `{$trainer_tbl}` as `r_trainer`
            ON `r_results`.`trainer_unique_name`=`r_trainer`.`unique_name` AND `r_trainer`.`is_enabled`=1
        WHERE `r_results`.`race_id` LIKE :race_id
        END;
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
            if(empty($data['horse_id'])){ continue; }
            $data['sex_str']=sex2String((int)$data['sex']);
            $data['age']=empty($data['birth_year'])?'':($this->raceYear-$data['birth_year']);
            // 騎手名のセット
            $jockey=(new JockeyRow())->setFromArray($data, Jockey::TABLE.'__');
            $data['jockey_name']=$data['jockey_unique_name'];
            if($jockey->is_enabled==1){
                if($jockey->is_anonymous==1){
                    $data['jockey_name']=(!$this->pageIsEditable)?'□□□□':($jockey->short_name_10?:$data['jockey_unique_name']);
                }else{
                    $data['jockey_name']=$jockey->short_name_10?:$data['jockey_unique_name'];
                }
            }
            $data['jockey_row']=$jockey;
            // レース側の所属がない場合は馬側の値を採用
            $data['tc']=$data['tc']?:$data['horse_tc'];
            // 調教師データのセット
            $race_trainer=(new TrainerRow())->setFromArray($data,'r_trainer__');
            $horse_trainer=(new TrainerRow())->setFromArray($data,'h_trainer__');
            $data['trainer_name']='';
            $data['trainer_row']=new TrainerRow();
            if($data['trainer_unique_name']){
                if($race_trainer->is_anonymous==1){
                    $data['trainer_name']=(!$this->pageIsEditable)?'□□□□':($race_trainer->short_name_10?:$data['trainer']);
                }else{
                    $data['trainer_name']=$race_trainer->short_name_10?:$data['trainer_unique_name'];
                }
                $data['trainer_row']=$race_trainer;
            }else if($data['horse_trainer_unique_name']){
                // レース側の調教師が空の場合は馬側の値を採用
                $data['trainer']=$data['horse_trainer_unique_name'];
                if($horse_trainer->is_anonymous==1){
                    $data['trainer_name']=(!$this->pageIsEditable)?'□□□□':($horse_trainer->short_name_10?:$data['horse_trainer_unique_name']);
                }else{
                    $data['trainer_name']=$horse_trainer->short_name_10?:$data['horse_trainer_unique_name'];
                }
                $data['trainer_row']=$horse_trainer;
            }
            // レースの調教国が空の場合は馬側の値を採用
            $data['training_country']=$data['training_country']?:$data['horse_training_country'];
            $table_data[]=$data;
        }
        return $table_data;
    }
}
