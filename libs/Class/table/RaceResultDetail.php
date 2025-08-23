<?php
class RaceResultDetail extends Table{
    public const TABLE="dat_race_results_hourse";

    public $record_exists=false;

    public $race_results_id ='';
    public $horse_id ='';
    public $result_number =null;
    public $result_order =null;
    public $result_before_demotion =null;
    public $result_text =null;
    public $frame_number =0;
    public $horse_number =0;
    public $handicap =null;
    public $margin ='';
    public $corner_1 ='';
    public $corner_2 ='';
    public $corner_3 ='';
    public $corner_4 ='';
    public $favourite =0;
    public $syuutoku =0;
    public $sex =0;
    public $tc =null;
    public $training_country ='';
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
        'favourite',
        'syuutoku',
        'sex',
        'is_affliationed_nar',
        'non_registered_prev_race_number',
        'jra_thisweek_horse_sort_number',
    ];
    const STR_COLUMNS=[
        'race_results_id','horse_id',
        'result_text',
        'handicap',
        'margin',
        'tc',
        'training_country',
        'jra_thisweek_horse_1',
        'jra_thisweek_horse_2',
        'jra_sps_comment',
    ];

    public function __construct(){
    }
    /**
     * キーでデータベースから取得
     */
    public function setDataById($pdo, string $race_results_id, string $horse_id){
        $target_columns=array_diff(
            array_merge(self::INT_COLUMNS,self::STR_COLUMNS),['number']);
        $sql_select_columns_part=(new SqlMakeColumnNames($target_columns))->quotedString();

        $sql ="SELECT {$sql_select_columns_part} FROM `".self::TABLE;
        $sql.="` WHERE `race_results_id` LIKE :race_results_id AND `horse_id` LIKE :horse_id LIMIT 1;";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':race_results_id', $race_results_id, PDO::PARAM_STR);
        $stmt->bindValue(':horse_id', $horse_id, PDO::PARAM_STR);
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
        $this->handicap = filter_input($input_type,'handicap');
        $this->favourite = (int)filter_input($input_type,'favourite');
        if($this->favourite==0){
            $this->favourite = (int)filter_input($input_type,'favourite_select');
        }
        $this->margin = filter_input($input_type,'margin');
        $this->corner_1 = filter_input($input_type,'corner_1');
        $this->corner_2 = filter_input($input_type,'corner_2');
        $this->corner_3 = filter_input($input_type,'corner_3');
        $this->corner_4 = filter_input($input_type,'corner_4');
        $this->syuutoku = (int)filter_input($input_type,'syuutoku',FILTER_VALIDATE_INT);
        $this->sex = filter_input($input_type,'sex');
        $this->tc = filter_input($input_type,'tc');
        $this->training_country = filter_input($input_type,'training_country');
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
        $flag = $stmt->execute();
    }
    public function UpdateExec(PDO $pdo){
        $target_columns=array_diff(
            array_merge(
                self::INT_COLUMNS,self::STR_COLUMNS),
                ['number','race_results_id','horse_id']);
        $sql_update_set_part=(new SqlMakeColumnNames($target_columns))->updateSetString();
        $tbl=self::TABLE;
        $sql=<<<END
        UPDATE `{$tbl}`
        SET {$sql_update_set_part}
        WHERE `race_results_id` LIKE :race_results_id AND `horse_id` LIKE :horse_id;
        END;
        $stmt = $pdo->prepare($sql);
        $stmt = $this->BindValues($stmt);
        return $stmt->execute();
    }
    /**
     * Delete実行
     */
    public function DeleteExec(PDO $pdo){
        if($this->race_results_id==''){exit;};
        if($this->horse_id==''){exit;};
        $sql ="DELETE FROM ".self::TABLE;
        $sql.= " WHERE `race_results_id` LIKE :race_results_id";
        $sql.= " AND `horse_id` LIKE :horse_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":race_results_id",$this->race_results_id,PDO::PARAM_STR);
        $stmt->bindValue(":horse_id",$this->horse_id,PDO::PARAM_STR);
        $result = $stmt->execute();
    }
    /**
     * Insert/Updateのバインド
     */
    private function BindValues($stmt){
        $stmt->bindValue(':race_results_id', $this->race_results_id, PDO::PARAM_STR);
        $stmt->bindValue(':horse_id', $this->horse_id, PDO::PARAM_STR);
        $stmt->bindValue(':result_number', intOrNullIfZero($this->result_number), PDO::PARAM_INT);
        $stmt->bindValue(':result_order', intOrNullIfZero($this->result_order), PDO::PARAM_INT);
        $stmt->bindValue(':result_before_demotion', $this->result_before_demotion, PDO::PARAM_INT);
        $stmt->bindValue(':result_text', $this->result_text, PDO::PARAM_STR);
        $stmt->bindValue(':frame_number', intOrNullIfZero($this->frame_number), PDO::PARAM_INT);
        $stmt->bindValue(':horse_number', intOrNullIfZero($this->horse_number), PDO::PARAM_INT);
        $stmt->bindValue(':handicap', $this->handicap, PDO::PARAM_STR);
        $stmt->bindValue(':margin', $this->margin, PDO::PARAM_STR);
        $stmt->bindValue(':corner_1', intOrNullIfZero($this->corner_1), PDO::PARAM_INT);
        $stmt->bindValue(':corner_2', intOrNullIfZero($this->corner_2), PDO::PARAM_INT);
        $stmt->bindValue(':corner_3', intOrNullIfZero($this->corner_3), PDO::PARAM_INT);
        $stmt->bindValue(':corner_4', intOrNullIfZero($this->corner_4), PDO::PARAM_INT);
        $stmt->bindValue(':favourite', intOrNullIfZero($this->favourite), PDO::PARAM_INT);
        $stmt->bindValue(':syuutoku', $this->syuutoku, PDO::PARAM_INT);
        $stmt->bindValue(':sex', $this->sex, PDO::PARAM_INT);
        $stmt->bindValue(':tc', $this->tc, PDO::PARAM_STR);
        $stmt->bindValue(':training_country', $this->training_country, PDO::PARAM_STR);
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
        WHERE `race_results_id` LIKE :race_results_id AND `horse_id` LIKE :horse_id;
        END;
        $stmt=$pdo->prepare($sql);
        $stmt->bindValue(':race_results_id', $this->race_results_id, PDO::PARAM_STR);
        $stmt->bindValue(':horse_id', $this->horse_id, PDO::PARAM_STR);
        return $stmt->execute();
    }
}
