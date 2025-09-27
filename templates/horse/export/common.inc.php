<?php
/**
 * @var string $horse_id
 * @var HorseRow $horse
 * /horse/export/の内容部
 */
?><?php
if(filter_input(INPUT_GET,'mode')==='sample'){
    // このように手前で割り込んで分岐してexitすると簡単
    header('Content-Type: application/json');
    $arr=[
        "サンプル分岐"=>'JSON例',
        '競走馬ID'=>$horse_id,
    ];
    echo json_encode($arr,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
    exit;
}
header('Content-Type: text/plain');
$text=<<<EOL
現在はデフォルトテンプレート /templates/horse/export/common.inc.php を読み込んでいます。
/user/templates/horse/export/index.inc.php を作成すると、このファイルの代わりに読み込まれるため
そこに任意に成形したエクスポートを記載することでカスタマイズできます。

また、エクスポート種類の一覧画面 /horse/export/ の内容部分は
/templates/horse/export/index.inc.php を使用しているため
/user/templates/horse/export/index.inc.php でオーバーライドできます。

一覧に「./common.php に任意のパラメータを付与して開く」リンクを追加し、
/user/templates/horse/export/index.inc.phpには追加パラメータに応じた分岐処理を追加することで、
複数種のエクスポート処理を追加することもできます。
EOL;
echo $text;
echo str_repeat("\n",2);
echo "●読み込み変数参考\n";
echo '$horse_id = '.$horse_id."\n";
echo '$horse = '.print_r($horse,true);
