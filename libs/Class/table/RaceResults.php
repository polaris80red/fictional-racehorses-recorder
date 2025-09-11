<?php
class RaceResults extends Table{
    public const TABLE="dat_race_results";
    public const ROW_CLASS = RaceResultsRow::class;

    public $record_exists=false;

    public $race_id ='';
    public $horse_id ='';
    public $result_number =null;
    public $result_order =null;
    public $result_before_demotion =null;
    public $result_text =null;
    public $frame_number =0;
    public $horse_number =0;
    public $jockey_unique_name =null;
    public $handicap =null;
    public $time ='';
    public $margin ='';
    public $corner_1 ='';
    public $corner_2 ='';
    public $corner_3 ='';
    public $corner_4 ='';
    public $f_time =null;
    public $h_weight ='';
    public $odds =0;
    public $favourite =0;
    public $earnings =null;
    public $syuutoku =null;
    public $sex =0;
    public $tc =null;
    public $trainer_unique_name =null;
    public $training_country ='';
    public $owner_name ='';
    public $is_affliationed_nar =0;
    public $non_registered_prev_race_number =0;
    public $jra_thisweek_horse_1 ='';
    public $jra_thisweek_horse_2 ='';
    public $jra_thisweek_horse_sort_number =null;
    public $jra_sps_comment ='';

    const INT_COLUMNS=[
        'number',
        'result_number','result_order','result_before_demotion',
        'frame_number','horse_number',
        'corner_1','corner_2','corner_3','corner_4',
        'h_weight',
        'favourite',
        'earnings',
        'syuutoku',
        'sex',
        'is_affliationed_nar',
        'non_registered_prev_race_number',
        'jra_thisweek_horse_sort_number',
    ];
    const STR_COLUMNS=[
        'race_id','horse_id',
        'result_text',
        'jockey_unique_name',
        'handicap',
        'time',
        'margin',
        'f_time',
        'odds',
        'tc',
        'trainer_unique_name',
        'training_country',
        'owner_name',
        'jra_thisweek_horse_1',
        'jra_thisweek_horse_2',
        'jra_sps_comment',
    ];

    public function __construct(){
    }
    /**
     * キーでデータベースから取得
     */
    public function setDataById($pdo, string $race_id, string $horse_id){
        $target_columns=array_diff(
            array_merge(self::INT_COLUMNS,self::STR_COLUMNS),['number']);
        $sql_select_columns_part=(new SqlMakeColumnNames($target_columns))->quotedString();

        $sql ="SELECT {$sql_select_columns_part} FROM `".self::TABLE;
        $sql.="` WHERE `race_id` LIKE :race_id AND `horse_id` LIKE :horse_id LIMIT 1;";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':race_id', SqlValueNormalizer::escapeLike($race_id), PDO::PARAM_STR);
        $stmt->bindValue(':horse_id', SqlValueNormalizer::escapeLike($horse_id), PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if($result==false){
            return false;
        }
        foreach($target_columns as $param){
            $this->{$param}=$result[$param];
        }
        $this->record_exists=true;
        return true;
    }
    /**
     * キー以外のカラムをForm送信内容からセット
     */
    public function setDataByForm($input_type=INPUT_POST){
        $this->result_number = (int)filter_input($input_type,'result_number');
        if($this->result_number==0){
            $this->result_number = (int)filter_input($input_type,'result_number_select');
        }
        if($this->result_number==0){ $this->result_number==null; }
        $this->result_order=filter_input($input_type,'result_order',FILTER_VALIDATE_INT)?:null;

        $this->result_before_demotion = (int)filter_input($input_type,'result_before_demotion');
        $this->result_text = filter_input($input_type,'result_text');
        $this->frame_number = filter_input($input_type,'frame_number');
        $this->horse_number = (int)filter_input($input_type,'horse_number');
        if($this->horse_number==0){
            $this->horse_number = (int)filter_input($input_type,'horse_number_select');
        }
        $this->jockey_unique_name = filter_input($input_type,'jockey')?:null;
        $this->handicap = filter_input($input_type,'handicap');
        $this->odds = filter_input($input_type,'odds')?:null;
        $this->favourite = (int)filter_input($input_type,'favourite');
        if($this->favourite==0){
            $this->favourite = (int)filter_input($input_type,'favourite_select');
        }
        $this->time = filter_input($input_type,'time');
        $this->margin = filter_input($input_type,'margin');
        $this->corner_1 = filter_input($input_type,'corner_1');
        $this->corner_2 = filter_input($input_type,'corner_2');
        $this->corner_3 = filter_input($input_type,'corner_3');
        $this->corner_4 = filter_input($input_type,'corner_4');
        $this->f_time = filter_input($input_type,'f_time');
        $this->h_weight = filter_input($input_type,'h_weight');
        $this->earnings = (int)filter_input($input_type,'earnings',FILTER_VALIDATE_INT)?:null;
        $this->syuutoku = (int)filter_input($input_type,'syuutoku',FILTER_VALIDATE_INT)?:null;
        $this->sex = filter_input($input_type,'sex');
        $this->tc = filter_input($input_type,'tc');
        $this->trainer_unique_name = filter_input($input_type,'trainer_unique_name')?:null;
        $this->training_country = filter_input($input_type,'training_country');
        $this->owner_name = filter_input($input_type,'owner_name')?:null;
        $this->is_affliationed_nar = (int)filter_input($input_type,'is_affliationed_nar');
        $this->non_registered_prev_race_number = (int)filter_input($input_type,'non_registered_prev_race_number');
        $this->jra_thisweek_horse_1 = filter_input($input_type,'jra_thisweek_horse_1');
        $this->jra_thisweek_horse_2 = filter_input($input_type,'jra_thisweek_horse_2');
        $this->jra_thisweek_horse_sort_number = filter_input($input_type,'jra_thisweek_horse_sort_number');
        $this->jra_sps_comment = filter_input($input_type,'jra_sps_comment');
        return true;
    }
    public function InsertExec(PDO $pdo){
        $target_columns=array_diff(
            array_merge(self::INT_COLUMNS,self::STR_COLUMNS),['number']);
        $columns=new SqlMakeColumnNames($target_columns);
        $insert_columns=$columns->quotedString();
        $insert_placeholders=$columns->placeholdersString();
        $tbl=self::TABLE;
        $sql=<<<END
        INSERT INTO `{$tbl}` ( `number`, {$insert_columns} )
        VALUES ( 
            NULL, {$insert_placeholders} );
        END;
        $stmt = $pdo->prepare($sql);
        $stmt = $this->BindValues($stmt);
        $stmt->bindValue(":race_id",$this->race_id,PDO::PARAM_STR);
        $stmt->bindValue(":horse_id",$this->horse_id,PDO::PARAM_STR);
        $flag = $stmt->execute();
    }
    public function UpdateExec(PDO $pdo){
        $target_columns=array_diff(
            array_merge(
                self::INT_COLUMNS,self::STR_COLUMNS),
                ['number','race_id','horse_id']);
        $sql_update_set_part=(new SqlMakeColumnNames($target_columns))->updateSetString();
        $tbl=self::TABLE;
        $sql=<<<END
        UPDATE `{$tbl}`
        SET {$sql_update_set_part}
        WHERE `race_id` LIKE :race_id AND `horse_id` LIKE :horse_id;
        END;
        $stmt = $pdo->prepare($sql);
        $stmt = $this->BindValues($stmt);
        $stmt->bindValue(":race_id",SqlValueNormalizer::escapeLike($this->race_id),PDO::PARAM_STR);
        $stmt->bindValue(":horse_id",SqlValueNormalizer::escapeLike($this->horse_id),PDO::PARAM_STR);
        return $stmt->execute();
    }
    /**
     * Delete実行
     */
    public function DeleteExec(PDO $pdo){
        if($this->race_id==''){exit;};
        if($this->horse_id==''){exit;};
        $sql ="DELETE FROM ".self::TABLE;
        $sql.= " WHERE `race_id` LIKE :race_id";
        $sql.= " AND `horse_id` LIKE :horse_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":race_id",SqlValueNormalizer::escapeLike($this->race_id),PDO::PARAM_STR);
        $stmt->bindValue(":horse_id",SqlValueNormalizer::escapeLike($this->horse_id),PDO::PARAM_STR);
        $result = $stmt->execute();
    }
    /**
     * Insert/Updateのバインド
     */
    private function BindValues($stmt){
        $stmt->bindValue(':result_number', intOrNullIfZero($this->result_number), PDO::PARAM_INT);
        $stmt->bindValue(':result_order', intOrNullIfZero($this->result_order), PDO::PARAM_INT);
        $stmt->bindValue(':result_before_demotion', $this->result_before_demotion, PDO::PARAM_INT);
        $stmt->bindValue(':result_text', $this->result_text, PDO::PARAM_STR);
        $stmt->bindValue(':frame_number', intOrNullIfZero($this->frame_number), PDO::PARAM_INT);
        $stmt->bindValue(':horse_number', intOrNullIfZero($this->horse_number), PDO::PARAM_INT);
        $stmt->bindValue(':jockey_unique_name', $this->jockey_unique_name, PDO::PARAM_STR);
        $stmt->bindValue(':handicap', $this->handicap, PDO::PARAM_STR);
        $stmt->bindValue(':time', $this->time?:null, PDO::PARAM_STR);
        $stmt->bindValue(':margin', $this->margin, PDO::PARAM_STR);
        $stmt->bindValue(':corner_1', intOrNullIfZero($this->corner_1), PDO::PARAM_INT);
        $stmt->bindValue(':corner_2', intOrNullIfZero($this->corner_2), PDO::PARAM_INT);
        $stmt->bindValue(':corner_3', intOrNullIfZero($this->corner_3), PDO::PARAM_INT);
        $stmt->bindValue(':corner_4', intOrNullIfZero($this->corner_4), PDO::PARAM_INT);
        $stmt->bindValue(':f_time', $this->f_time?:null, PDO::PARAM_STR);
        $stmt->bindValue(':h_weight', $this->h_weight?:null, PDO::PARAM_INT);
        $stmt->bindValue(':odds', $this->odds?:null, PDO::PARAM_STR);
        $stmt->bindValue(':favourite', intOrNullIfZero($this->favourite), PDO::PARAM_INT);
        $stmt->bindValue(':earnings', $this->earnings?:null, PDO::PARAM_INT);
        $stmt->bindValue(':syuutoku', $this->syuutoku?:null, PDO::PARAM_INT);
        $stmt->bindValue(':sex', $this->sex, PDO::PARAM_INT);
        $stmt->bindValue(':tc', $this->tc, PDO::PARAM_STR);
        $stmt->bindValue(':trainer_unique_name', $this->trainer_unique_name?:null, PDO::PARAM_STR);
        $stmt->bindValue(':training_country', $this->training_country, PDO::PARAM_STR);
        $stmt->bindValue(':owner_name', $this->owner_name?:null, PDO::PARAM_STR);
        $stmt->bindValue(':is_affliationed_nar', $this->is_affliationed_nar, PDO::PARAM_STR);
        $stmt->bindValue(':non_registered_prev_race_number', $this->non_registered_prev_race_number, PDO::PARAM_INT);
        $stmt->bindValue(':jra_thisweek_horse_1', $this->jra_thisweek_horse_1, PDO::PARAM_STR);
        $stmt->bindValue(':jra_thisweek_horse_2', $this->jra_thisweek_horse_2, PDO::PARAM_STR);
        $stmt->bindValue(':jra_thisweek_horse_sort_number', intOrNullIfZero($this->jra_thisweek_horse_sort_number), PDO::PARAM_INT);
        $stmt->bindValue(':jra_sps_comment', $this->jra_sps_comment, PDO::PARAM_STR);
        return $stmt;
    }
    public function SubtractionNonRegisteredPrevRaceNumber(PDO $pdo){
        $tbl=self::TABLE;
        $sql=<<<END
        UPDATE `{$tbl}`
        SET
            `non_registered_prev_race_number` = `non_registered_prev_race_number`-1
        WHERE `race_id` LIKE :race_id AND `horse_id` LIKE :horse_id;
        END;
        $stmt=$pdo->prepare($sql);
        $stmt->bindValue(":race_id",SqlValueNormalizer::escapeLike($this->race_id),PDO::PARAM_STR);
        $stmt->bindValue(":horse_id",SqlValueNormalizer::escapeLike($this->horse_id),PDO::PARAM_STR);
        return $stmt->execute();
    }
}
