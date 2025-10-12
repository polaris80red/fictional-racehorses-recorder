<?php
class RaceResults extends Table{
    public const TABLE="dat_race_results";
    public const UNIQUE_KEY_COLUMN="number";
    public const ROW_CLASS = RaceResultsRow::class;
    /**
     * レースIDと競走馬IDで結果行を取得
     */
    public static function getRowByIds($pdo, string $raceId, string $horseId){
        $target_columns=(self::ROW_CLASS)::getColumnNames();
        $sql_select_columns_part=(new SqlMakeColumnNames($target_columns))->quotedString();

        $sql ="SELECT {$sql_select_columns_part} FROM `".self::TABLE;
        $sql.="` WHERE `race_id` LIKE :race_id AND `horse_id` LIKE :horse_id LIMIT 1;";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':race_id', SqlValueNormalizer::escapeLike($raceId), PDO::PARAM_STR);
        $stmt->bindValue(':horse_id', SqlValueNormalizer::escapeLike($horseId), PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if($result==false){
            return false;
        }
        return (new (static::ROW_CLASS))->setFromArray($result);
    }
    public static function SubtractionNonRegisteredPrevRaceNumber(PDO $pdo, string $race_id, string $horse_id, string|null $updated_at=null){
        $tbl=self::TABLE;
        $sql=<<<END
        UPDATE `{$tbl}`
        SET
            `non_registered_prev_race_number` = `non_registered_prev_race_number`-1,
            `updated_at`=:updated_at
        WHERE `race_id` LIKE :race_id AND `horse_id` LIKE :horse_id;
        END;
        $stmt=$pdo->prepare($sql);
        $stmt->bindValue(":updated_at",$updated_at,PDO::PARAM_STR);
        $stmt->bindValue(":race_id",SqlValueNormalizer::escapeLike($race_id),PDO::PARAM_STR);
        $stmt->bindValue(":horse_id",SqlValueNormalizer::escapeLike($horse_id),PDO::PARAM_STR);
        return $stmt->execute();
    }
}
