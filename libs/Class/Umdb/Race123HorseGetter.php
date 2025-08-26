<?php
/**
 * レース結果表表示の1～3着馬を取得
 */
class Race123HorseGetter{
    private PDOStatement $stmt;
    private $race_id;

    public function __construct(PDO $pdo)
    {
        $results_detail_table=RaceResultDetail::TABLE;
        $horse_table=Horse::TABLE;
        $sql=<<<END
        SELECT
        `detail`.`horse_id`,`name_ja`,`name_en`,`result_number`
        FROM `{$results_detail_table}` AS `detail`
        LEFT JOIN `{$horse_table}` AS `Horse`
        ON `Horse`.`horse_id`= `detail`.`horse_id`
        WHERE
        `race_results_id` = :race_id
        AND
        `result_number`<=3
        ORDER BY
        `result_number` ASC,
        `result_order` IS NULL,
        `result_order` ASC;
        END;
        $this->stmt = $pdo->prepare($sql);
        $this->stmt->bindParam(':race_id', $this->race_id, PDO::PARAM_STR);
    }
    public function execute($race_id)
    {
        $this->race_id=$race_id;
        $this->stmt->execute();
        $row = $this->stmt->fetch(PDO::FETCH_ASSOC);
        $data=[];

        if(isset($row['result_number']) && $row['result_number']===1){
            $data['r1']['horse_id']=isset($row['horse_id'])?$row['horse_id']:"";
            $data['r1']['name_ja'] =isset($row['name_ja'])?$row['name_ja']:"";
            $data['r1']['name_en'] =isset($row['name_en'])?$row['name_en']:"";
            $row = $this->stmt->fetch(PDO::FETCH_ASSOC);
        }
        if(isset($row['result_number']) && $row['result_number']<=2){
            $data['r2']['horse_id']=isset($row['horse_id'])?$row['horse_id']:"";
            $data['r2']['name_ja'] =isset($row['name_ja'])?$row['name_ja']:"";
            $data['r2']['name_en'] =isset($row['name_en'])?$row['name_en']:"";
            $row = $this->stmt->fetch(PDO::FETCH_ASSOC);
        }
        if(isset($row['result_number']) && $row['result_number']<=3){
            $data['r3']['horse_id']=isset($row['horse_id'])?$row['horse_id']:"";
            $data['r3']['name_ja'] =isset($row['name_ja'])?$row['name_ja']:"";
            $data['r3']['name_en'] =isset($row['name_en'])?$row['name_en']:"";
        }
        return $data;
    }
    public function __invoke($race_id)
    {
        return $this->execute($race_id);
    }
}
