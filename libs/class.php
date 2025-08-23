<?php
/**
 * 自分以外の1着馬 または 2着馬の取得
 */
class RaceResults1stOr2ndHourseGetter{
    private $stmt=null;
    public function __construct($pdo){
        $horse_table=Horse::TABLE;
        $results_detail_table=RaceResultDetail::TABLE;
        $sql=<<<END
SELECT
`results`.`horse_id`,`name_ja`,`name_en`
FROM `{$results_detail_table}` AS `results`
    LEFT JOIN `{$horse_table}` AS `horse`
        ON `horse`.`horse_id`= `results`.`horse_id`
WHERE
    `race_results_id` LIKE :race_id 
    AND `result_number`<=:result_num 
    AND `results`.`horse_id` NOT LIKE :horse_id
ORDER BY
    `result_number` ASC,
    `result_order` IS NULL,
    `result_order` ASC;
END;
        $this->stmt = $pdo->prepare($sql);
    }
    public function get($race_id, $result_number, $horse_id){
        if($result_number===1){
            // 1着なら「2着以内の一番上」（同着1着を含む）
            $result_num=2;
        }else{
            // 2着以降なら1着馬のみ
            $result_num=1;
        }
        $this->stmt->bindValue(':race_id', $race_id, PDO::PARAM_STR);
        $this->stmt->bindValue(':result_num', $result_num, PDO::PARAM_INT);
        $this->stmt->bindValue(':horse_id', $horse_id, PDO::PARAM_STR);
        $this->stmt->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

}