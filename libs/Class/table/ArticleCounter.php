<?php
class ArticleCounter {
    public const TABLE = 'log_article_counter';
    public const UNIQUE_KEY_COLUMN="id";
    private const DEFAULT_WHERE = " WHERE `article_type`=:article_type AND `article_id`=:article_id";
    const TYPE_HORSE="horse";
    const TYPE_HORSE_RESULTS_DETAIL="horse_results_detail";
    const TYPE_RACE_RESULT="race_result";
    const TYPE_RACE_SYUTSUBA_SIMPLE="syutsuba_simple";
    const TYPE_RACE_SYUTSUBA_SP="syutsuba_sp";

    private function __construct() {}
    /**
     * 記事のカウントを記録する
     */
    public static function countup(PDO $pdo, string $article_type, string $article_id){
        if(!self::recordExists($pdo,$article_type,$article_id)){
            self::insert($pdo,$article_type,$article_id);
            return;
        }
        self::increment($pdo,$article_type,$article_id);
        return;
    }
    /**
     * レコードの有無を確認する
     * @return bool true:レコードあり|false:レコード無し
     */
    private static function recordExists(PDO $pdo, string $article_type, string $article_id){
        $sql ="SELECT `".self::UNIQUE_KEY_COLUMN."`";
        $sql.=" FROM `".self::TABLE."`";
        $sql.=self::DEFAULT_WHERE;
        $stmt=$pdo->prepare($sql);
        $stmt->bindValue(':article_type',$article_type,PDO::PARAM_STR);
        $stmt->bindValue(':article_id',$article_id,PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_COLUMN)>0?true:false;
    }
    /**
     * レコードを登録する
     */
    private static function insert(PDO $pdo, string $article_type, string $article_id){
        $sql=SqlMake::InsertSql(self::TABLE,[
            'article_type',
            'article_id',
            'view_count',
            'created_at',
            'updated_at',
        ]);
        $stmt=$pdo->prepare($sql);
        $stmt->bindValue(':article_type',$article_type,PDO::PARAM_STR);
        $stmt->bindValue(':article_id',$article_id,PDO::PARAM_STR);
        $stmt->bindValue(':view_count',1,PDO::PARAM_INT);
        $stmt->bindValue(':created_at',PROCESS_STARTED_AT,PDO::PARAM_STR);
        $stmt->bindValue(':updated_at',PROCESS_STARTED_AT,PDO::PARAM_STR);
        $stmt->execute();
        return;
    }
    /**
     * 既存レコードをupdateする
     */
    private static function increment(PDO $pdo, string $article_type, string $article_id){
        $sql = "UPDATE `".self::TABLE."`";
        $sql.=" SET `view_count`=`view_count`+1, `updated_at`=:updated_at";
        $sql.=self::DEFAULT_WHERE;
        $stmt=$pdo->prepare($sql);
        $stmt->bindValue(':article_type',$article_type,PDO::PARAM_STR);
        $stmt->bindValue(':article_id',$article_id,PDO::PARAM_STR);
        $stmt->bindValue(':updated_at',PROCESS_STARTED_AT,PDO::PARAM_STR);
        $stmt->execute();
        return;
    }
}
