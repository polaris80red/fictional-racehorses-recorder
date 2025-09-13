<?php
function get_syutsuba_data(PDO $pdo, object $race, int $rr_count=4){
    # このレース情報取得
    $sql = (function(){
        $horse_tbl=Horse::TABLE;
        $race_tbl=Race::TABLE;
        $race_results_tbl=RaceResults::TABLE;
        $race_special_results_tbl=RaceSpecialResults::TABLE;
        $jockey_tbl=Jockey::TABLE;
        $trainer_tbl=Trainer::TABLE;
        $sql=<<<END
        SELECT
        `r_results`.*
        ,`Horse`.`name_ja`
        ,`Horse`.`name_en`
        ,`Horse`.`tc` AS 'horse_tc'
        ,`Horse`.`trainer_name` AS 'horse_trainer_name'
        ,`Horse`.`training_country` AS 'horse_training_country'
        ,`Horse`.`is_affliationed_nar` AS 'horse_is_affliationed_nar'
        ,`Horse`.`sex` AS 'horse_sex'
        ,`Horse`.`birth_year`
        ,`Horse`.`sire_name`
        ,`Horse`.`mare_name`
        ,`Horse`.`bms_name`
        ,`Horse`.`color`
        ,`Horse`.`breeding_country`
        ,`sire`.`name_ja` as `sire_name_ja`
        ,`sire`.`name_en` as `sire_name_en`
        ,`mare`.`name_ja` as `mare_name_ja`
        ,`mare`.`name_en` as `mare_name_en`
        ,`mare`.`sire_name` as `mare_sire_name`
        ,`bms`.`name_ja` as `bms_name_ja`
        ,`bms`.`name_en` as `bms_name_en`
        ,`race`.*
        ,`spr`.`is_registration_only`
        ,`jk`.`short_name_10` as `jockey_mst_short_name_10`
        ,`jk`.`is_anonymous` as `jockey_mst_is_anonymous`
        ,`jk`.`is_enabled` as `jockey_mst_is_enabled`
        ,`trainer`.`short_name_10` as `trainer_mst_short_name_10`
        ,`trainer`.`is_anonymous` as `trainer_mst_is_anonymous`
        ,`trainer`.`is_enabled` as `trainer_mst_is_enabled`
        ,`race_trainer`.`short_name_10` as `race_trainer_mst_short_name_10`
        ,`race_trainer`.`is_anonymous` as `race_trainer_mst_is_anonymous`
        ,`race_trainer`.`is_enabled` as `race_trainer_mst_is_enabled`
        FROM `{$race_tbl}` AS `race`
        LEFT JOIN `{$race_results_tbl}` AS `r_results`
            ON `race`.`race_id`=`r_results`.`race_id`
        LEFT JOIN `{$horse_tbl}` AS `Horse` ON `r_results`.`horse_id`=`Horse`.`horse_id`
        LEFT JOIN `{$horse_tbl}` AS `sire` ON `Horse`.`sire_id`=`sire`.`horse_id`
        LEFT JOIN `{$horse_tbl}` AS `mare` ON `Horse`.`mare_id`=`mare`.`horse_id`
        LEFT JOIN `{$horse_tbl}` AS `bms` ON `mare`.`sire_id`=`bms`.`horse_id`
        LEFT JOIN `{$race_special_results_tbl}` as spr ON `r_results`.result_text LIKE spr.unique_name AND spr.is_enabled=1
        LEFT JOIN `{$jockey_tbl}` as `jk` ON `r_results`.`jockey_name`=`jk`.`unique_name` AND `jk`.`is_enabled`=1
        LEFT JOIN `{$trainer_tbl}` as `trainer` ON `Horse`.`trainer_name`=`trainer`.`unique_name` AND `trainer`.`is_enabled`=1
        LEFT JOIN `{$trainer_tbl}` as `race_trainer` ON `r_results`.`trainer_name`=`race_trainer`.`unique_name` AND `race_trainer`.`is_enabled`=1
        WHERE `race`.`race_id`=:race_id
        ORDER BY
        IFNULL(`r_results`.`frame_number`,32) ASC,
        `r_results`.`horse_number` ASC,
        `Horse`.`name_ja` ASC,
        `Horse`.`name_en` ASC;
        END;
        return $sql;
    })();

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':race_id', $race->race_id, PDO::PARAM_STR);
    $flag = $stmt->execute();
    $table_data=[];

    $result_get_sql=(function($rr_count){
        $race_tbl=Race::TABLE;
        $race_results_tbl=RaceResults::TABLE;
        $grade_tbl=RaceGrade::TABLE;
        $race_course_tbl=RaceCourse::TABLE;
        $race_special_results_tbl=RaceSpecialResults::TABLE;
        $jockey_tbl=Jockey::TABLE;
        $sql=<<<DOC
        SELECT
        `r_results`.*
        ,`race`.`race_name`
        ,`race`.`race_short_name`
        ,`race`.`caption`
        ,`race`.`race_course_name`
        ,`race`.`course_type`
        ,`race`.`distance`
        ,`race`.`grade`
        ,`race`.`date`
        ,`race`.`year`
        ,`race`.`month`
        ,`race`.`is_tmp_date`
        ,g.short_name as grade_short_name
        ,g.css_class as grade_css_class
        ,c.short_name AS race_course_short_name
        ,c.short_name_m AS race_course_short_name_m
        ,`spr`.`short_name_2` as `special_result_short_name_2`
        ,`jk`.`short_name_10` as `jockey_mst_short_name_10`
        ,`jk`.`is_anonymous` as `jockey_mst_is_anonymous`
        ,`jk`.`is_enabled` as `jockey_mst_is_enabled`
        FROM `{$race_results_tbl}` AS `r_results`
        LEFT JOIN `{$race_tbl}` AS `race` ON
            `race`.`race_id`=`r_results`.`race_id`
            AND
            (
                ((`race`.`year`=:race_year AND `race`.`week_id`<:week_id) OR `race`.`year`<:race_year)
                OR
                `race`.`date`<:race_date
            )
        LEFT JOIN `{$grade_tbl}` as g ON `race`.grade=g.unique_name
        LEFT JOIN `{$race_course_tbl}` as c ON `race`.race_course_name=c.unique_name AND c.is_enabled=1
        LEFT JOIN `{$race_special_results_tbl}` as spr ON `r_results`.result_text LIKE spr.unique_name AND spr.is_enabled=1
        LEFT JOIN `{$jockey_tbl}` as `jk` ON `r_results`.`jockey_name`=`jk`.`unique_name` AND `jk`.`is_enabled`=1
        WHERE
            `r_results`.`horse_id`=:horse_id
            AND (`is_registration_only` IS NULL OR `is_registration_only`=0)
        ORDER BY
            `race`.`year` DESC
            ,`race`.`week_id` DESC
            ,`race`.`date` DESC
        LIMIT {$rr_count}
        DOC;
        return $sql;
    })($rr_count);

    $horse_id='';
    $stmt2 = $pdo->prepare($result_get_sql);
    $stmt2->bindParam(':horse_id', $horse_id, PDO::PARAM_STR);
    $stmt2->bindValue(':race_year', $race->year, PDO::PARAM_INT);
    $stmt2->bindValue(':week_id', $race->week_id, PDO::PARAM_INT);
    $stmt2->bindValue(':race_date', $race->date, PDO::PARAM_STR);

    $rr12HourseGetter=new RaceResults1stOr2ndHourseGetter($pdo);
    while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data['sex_str']=sex2String((int)($data['sex']?:$data['horse_sex']),2);
        $data['age']=empty($data['birth_year'])?'':($race->year-$data['birth_year']);

        $horse_id=$data['horse_id'];
        $stmt2->execute();
        $data['horse_results']=[];
        while($data2 = $stmt2->fetch(PDO::FETCH_ASSOC)){
            $res= $rr12HourseGetter->get($data2['race_id'],$data2['result_number'],$data['horse_id']);
            $data2['winner_or_runner_up']=[
                'horse_id'=>!empty($res['horse_id'])?$res['horse_id']:'',
                'name_ja'=>!empty($res['name_ja'])?$res['name_ja']:'',
                'name_en'=>!empty($res['name_en'])?$res['name_en']:'',
            ];
            $data['horse_results'][]=$data2;
        }
        $table_data[]=$data;
    }
    return $table_data;
}