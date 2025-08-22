<?php
class HorseTag extends Table{
    public const TABLE = 'dat_horse_tag';
    public const UNIQUE_KEY_COLUMN="number";
    protected const DEFAULT_ORDER_BY ='';
    public const ROW_CLASS = HorseTagRow::class;

    private PDO $pdo;
    public function __construct(PDO $pdo) {
        $this->pdo=$pdo;
    }
    private const SEPARATOR=["　","\r","\n",",","、","_","#"];
    public static function TagsStrToArray(string $tags_text){
        $tags_text=str_replace(self::SEPARATOR," ",$tags_text);
        return array_diff(explode(" ",$tags_text),['']);
    }
    public function getTags($horse_id){
        $sql ="SELECT * FROM `{$this->tableName()}`";
        $sql.=" WHERE `horse_id` LIKE :id AND `is_enabled`=1";
        $sql.=" ORDER BY `tag_text` ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id',$horse_id,PDO::PARAM_STR);
        $stmt->execute();
        $results=[];
        while(($row=$stmt->fetch(PDO::FETCH_ASSOC))!==false){
            $results[]=(new (static::ROW_CLASS)())->setFromArray($row);
        }
        return $results;
    }
    public function getTagNames($horse_id){
        $sql ="SELECT `tag_text` FROM `{$this->tableName()}`";
        $sql.=" WHERE `horse_id` LIKE :id AND `is_enabled`=1";
        $sql.=" ORDER BY `tag_text` ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id',$horse_id,PDO::PARAM_STR);
        $stmt->execute();
        $results=[];
        while(($row=$stmt->fetch(PDO::FETCH_COLUMN))!==false){
            $results[]=$row;
        }
        return $results;
    }
    // タグを一括更新
    public function updateHorseTags(string $horse_id, array $input_tags,string $log_date_time){
        $sql="SELECT `{$this->tableName()}`.`tag_text`, `{$this->tableName()}`.* FROM `{$this->tableName()}` WHERE `horse_id` LIKE :id;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id',$horse_id,PDO::PARAM_STR);
        $stmt->execute();
        $data=$stmt->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE);

        $update_sql ="UPDATE `{$this->tableName()}`";
        $update_sql.=" SET `is_enabled`=:is_enabled, `updated_at`=:updated_at";
        $update_sql.=" WHERE `horse_id` LIKE :id AND `tag_text` LIKE :tag";
        $update_stmt=$this->pdo->prepare($update_sql);
        $update_stmt->bindValue(':id',$horse_id,PDO::PARAM_STR);
        $is_enabled=0;

        $record_tags=array_keys($data); // レコードにあるタグの切り出し
        $insert_tags=array_diff($input_tags,$record_tags); // テーブルに存在しないタグをInsert用に切り出し

        $update_stmt=$this->pdo->prepare($update_sql);
        $update_stmt->bindValue(':id',$horse_id,PDO::PARAM_STR);
        $update_stmt->bindValue(':updated_at',$log_date_time,PDO::PARAM_STR);
        $bind_tag=''; $is_enabled=0;
        $update_stmt->bindParam(':tag',$bind_tag,PDO::PARAM_STR);
        $update_stmt->bindParam(':is_enabled',$is_enabled,PDO::PARAM_INT);

        foreach($data as $row){
            $tag=(new (static::ROW_CLASS)())->setFromArray($row);
            $exist_in_input=in_array($tag->tag_text,$input_tags);

            $bind_tag=$tag->tag_text;
            if($exist_in_input && $tag->is_enabled==0){
                // 無効で入力値にある→UPDATEで復活させる
                $is_enabled=1;
                $update_stmt->execute();
                Elog::debug($horse_id."[{$tag->tag_text}]復活");
                continue;
            }
            if(!$exist_in_input && $tag->is_enabled==1){
                // 有効だが入力値にない→UPDATEで廃止させる（AUTO INCREMENTを節約）
                $is_enabled=0;
                $update_stmt->execute();
                Elog::debug($horse_id."[{$tag->tag_text}]無効化");
                continue;
            }
            // 有効で入力値にある・無効で入力値にもないは無視
        }
        unset($update_stmt);

        // テーブルに存在しなかったレコードのリストからINSERT
        $insert_sql= "INSERT INTO `{$this->tableName()}`";
        $insert_sql.="( `horse_id`,`tag_text`, `created_at`,`updated_at`)";
        $insert_sql.=" VALUES ( :id, :tag, :date_time, :date_time )";
        $insert_stmt=$this->pdo->prepare($insert_sql);
        $insert_stmt->bindValue(':id',$horse_id,PDO::PARAM_STR);
        $insert_stmt->bindValue(':date_time',$log_date_time,PDO::PARAM_STR);
        $bind_tag='';
        $insert_stmt->bindParam(':tag',$bind_tag,PDO::PARAM_STR);
        $i=0;
        foreach($insert_tags as $tag_name){
            // INSERTする
            $bind_tag=$tag_name;
            $insert_stmt->execute();
            $i++;
        }
        if($i>0){
            Elog::debug(__CLASS__.__METHOD__," INSERT $i 件(".implode(',',$insert_tags).")");
        }
    }
}
