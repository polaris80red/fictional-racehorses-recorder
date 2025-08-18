<?php
function get_syutsuba_data(PDO $pdo, object $race, int $rr_count=4){
    # このレース情報取得
    $sql = (function(){
        $horse_tbl=Horse::TABLE;
        $race_results_tbl=RaceResults::TABLE;
        $race_results_detail_tbl=RaceResultDetail::TABLE;
        $sql=<<<END
        SELECT
        `RR_Detail`.*
        ,`Horse`.`name_ja`
        ,`Horse`.`name_en`
        ,`Horse`.`tc` AS 'horse_tc'
        ,`Horse`.`training_country` AS 'horse_training_country'
        ,`Horse`.`is_affliationed_nar` AS 'horse_is_affliationed_nar'
        ,`Horse`.`sex` AS 'horse_sex'
        ,`Horse`.`birth_year`
        ,`Horse`.`sire_name`
        ,`Horse`.`mare_name`
        ,`Horse`.`bms_name`
        ,`Horse`.`color`
        ,`RResults`.*
        FROM `{$race_results_tbl}` AS `RResults`
        LEFT JOIN `{$race_results_detail_tbl}` AS `RR_Detail`
            ON `RResults`.`race_id`=`RR_Detail`.`race_results_id`
        LEFT JOIN `{$horse_tbl}` AS `Horse` ON `RR_Detail`.`horse_id`=`Horse`.`horse_id`
        WHERE `RResults`.`race_id`=:race_id
        ORDER BY
        IFNULL(`RR_Detail`.`frame_number`,32) ASC,
        `RR_Detail`.`horse_number` ASC,
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
        $race_results_tbl=RaceResults::TABLE;
        $race_results_detail_tbl=RaceResultDetail::TABLE;
        $grade_tbl=RaceGrade::TABLE;
        $race_course_tbl=RaceCourse::TABLE;
        $sql=<<<DOC
        SELECT
        `RR_Detail`.*
        ,`RR`.`race_name`
        ,`RR`.`race_short_name`
        ,`RR`.`caption`
        ,`RR`.`race_course_name`
        ,`RR`.`course_type`
        ,`RR`.`distance`
        ,`RR`.`grade`
        ,`RR`.`date`
        ,`RR`.`year`
        ,`RR`.`month`
        ,`RR`.`is_tmp_date`
        ,g.short_name as grade_short_name
        ,g.css_class_suffix as grade_css_class_suffix
        ,c.short_name AS race_course_short_name
        ,c.short_name_m AS race_course_short_name_m
        FROM `{$race_results_detail_tbl}` AS `RR_Detail`
        LEFT JOIN `{$race_results_tbl}` AS `RR` ON
            `RR`.`race_id`=`RR_Detail`.`race_results_id`
            AND
            `RR`.`date`<:race_date
            AND
            `RR_Detail`.`is_registration_only`= 0
        LEFT JOIN `{$grade_tbl}` as g ON RR.grade=g.unique_name
        LEFT JOIN `{$race_course_tbl}` as c ON RR.race_course_name=c.unique_name AND c.is_enabled=1
        WHERE
            `RR_Detail`.`horse_id`=:horse_id
        ORDER BY
            `RR`.`year` DESC
            ,`RR`.`month` DESC
            ,`RR`.`date` DESC
        LIMIT {$rr_count}
        DOC;
        return $sql;
    })($rr_count);

    $horse_id='';
    $stmt2 = $pdo->prepare($result_get_sql);
    $stmt2->bindParam(':horse_id', $horse_id, PDO::PARAM_STR);
    $stmt2->bindValue(':race_date', $race->date, PDO::PARAM_STR);

    $rr12HourseGetter=new RaceResults1stOr2ndHourseGetter($pdo);
    while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data['sex_str']=sex2String((int)($data['sex']?:$data['horse_sex']),2);
        $data['age']=empty($data['birth_year'])?'':($race->year-$data['birth_year']);

        $horse_id=$data['horse_id'];
        $stmt2->execute();
        $data['horse_results']=[];
        while($data2 = $stmt2->fetch(PDO::FETCH_ASSOC)){
            $res= $rr12HourseGetter->get($data2['race_results_id'],$data2['result_number'],$data['horse_id']);
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