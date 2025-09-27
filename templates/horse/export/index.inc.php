<?php
/**
 * 競走馬情報エクスポート画面のメインページの内容部分
 * @var HorseRow $horse
 */
?>
<ul>
    <li>戦績｜スプレッドシート・Excel用タブ区切りテキスト
        <ul>
            <li><?=(new MkTagA('戦績TSV昇順','./history_tsv.php?'.http_build_query([
                'horse_id'=>$horse->horse_id,
                'horse_history_order'=>'asc'
                ])))?></li>
            <li><?=(new MkTagA('戦績TSV降順','./history_tsv.php?'.http_build_query([
                'horse_id'=>$horse->horse_id,
                'horse_history_order'=>'desc'
                ])))?></li>
        </ul>
    </li>
</ul>
