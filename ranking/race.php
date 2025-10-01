<?php
session_start();
require_once dirname(__DIR__,1).'/libs/init.php';
InAppUrl::init(1);
$page=new Page(1);
$setting=new Setting();
$page->setSetting($setting);
$page->title="アクセスランキング｜レース";
$page->ForceNoindex();

$session=new Session();

do{
    if($setting->world_id==0){
        $page->addErrorMsg('ワールドID未設定');
        break;
    }
    $pdo=getPDO();
    $sortMode=strtolower((string)filter_input(INPUT_GET,'sort'));
    $sortMode=($sortMode && in_array($sortMode,['count','last_access']))?$sortMode:'count';
    $limit=min(max((int)filter_input(INPUT_GET,'limit'),10),100);

    $sql=(function($sortMode,$limit){
        $race='race';
        $raceColumns=Race::getPrefixedSelectClause($race);
        $race="`$race`";
        $c='`c`';
        $sortOrderList=[
            'count'=>"$c.`view_count` DESC, $c.`updated_at` DESC",
            'last_access'=>"$c.`updated_at` DESC",
        ];
        $sortOrder=$sortOrderList[$sortMode] ?? $sortOrderList['count'];

        $sqlParts=[
            "SELECT $raceColumns, $c.`view_count`, $c.`updated_at` AS `last_access`",
            "FROM `".Race::TABLE."` AS $race",
            "RIGHT JOIN `".ArticleCounter::TABLE."` AS $c",
            "ON $c.`article_id` LIKE $race.`race_id`",
            "WHERE $c.`article_type` LIKE 'race\_result' AND $race.`world_id`=:world_id",
            "ORDER BY $sortOrder",
            "LIMIT $limit",
        ];
        return implode(' ',$sqlParts);
    })($sortMode,$limit);

    $stmt=$pdo->prepare($sql);
    $stmt->bindValue(':world_id',$setting->world_id,PDO::PARAM_INT);
    $stmt->execute();
    $tableData=$stmt->fetchAll();
}while(false);
$page->renderErrorsAndExitIfAny();
?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?=h($page->renderTitle())?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?=$page->renderBaseStylesheetLinks()?>
    <?=$page->renderJqueryResource()?>
    <?=$page->renderScriptLink("js/functions.js")?>
<style>
    td.col_count { text-align: right; }
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<table class="admin-master-list">
<tr>
    <th>年度</th>
    <th>名称</th>
    <th>回数</th>
    <th>最新アクセス</th>
</tr>
<?php foreach($tableData as $row): ?>
<?php
    $race=(new RaceRow)->setFromArray($row,Race::TABLE."__");
?>
<tr>
    <td class=""><?=h($race->year);?></td>
    <td class=""><a href="<?=h(InAppUrl::to('race/result/',['race_id'=>$race->race_id]))?>"><?=h($race->race_name);?></a></td>
    <td class="col_count"><?=h($row['view_count']);?></td>
    <td class=""><?=h($row['last_access']);?></td>
</tr>
<?php endforeach; ?>
</table>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>