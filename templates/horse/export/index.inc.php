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
    <li><?=(new MkTagA('汎用エクスポート（カスタマイズ用）','./common.php?'.http_build_query([
                'horse_id'=>$horse->horse_id,
                ])))?></li>
    <li><?=(new MkTagA('汎用エクスポート（分岐サンプル）','./common.php?'.http_build_query([
                'horse_id'=>$horse->horse_id,
                'mode'=>'sample',
                ])))?></li>
</ul>
