<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
defineAppRootRelPath(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース結果一括複写";
$page->ForceNoindex();
$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }  
$pdo= getPDO();
$id_list = filter_input(INPUT_GET, 'id_list', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
if(!isset($id_list) || count($id_list)==0){
    $page->addErrorMsg('レースIDが1件も選択されていません');
    $page->printCommonErrorPage();
    exit;
}
$data=(function($pdo,$id_list){
    $binder=new StatementBinder();
    $where_in_parts=[];
    $i=0;
    foreach($id_list as $id){
        $ph=':id_'.(++$i);
        $where_in_parts[]=$ph;
        $binder->add($ph,$id);
    }
    $sql ="SELECT * FROM `".Race::TABLE."`";
    $sql.=" WHERE `".Race::UNIQUE_KEY_COLUMN."` IN (".implode(',',$where_in_parts).")";
    $sql.=" ORDER BY `week_id` ASC, `year` ASC, `date` ASC";
    $stmt=$pdo->prepare($sql);
    $binder->bindTo($stmt);
    $stmt->execute();
    return $stmt->fetchAll();
})($pdo,$id_list);

?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?=h($page->renderTitle())?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink('js/functions.js'); ?>
<style>
th{ background-color: #EEE;}
#content table{
    margin-top: 8px;
    font-size: 0.9em;
}
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<form action="./execute.php" method="post">
<p>新しい年度と複写先ワールドを指定してレース情報を複写します。<br>
※ 正規日付は除去します。</p>
<input type="submit" value="一括複写実行" style="color:red;">
<table>
    <tr><th colspan="2">複写先</th></tr>
    <tr>
        <th>ワールド</th>
        <td class="in_input"><select name="world_id" class="required" required>
        <option value="">未選択</option>
        <?php
        $world_list=World::getAll($pdo);
        if(count($world_list)>0){
            foreach($world_list as $row){
                $selected= $row['id']==$setting->world_id?" selected":"";
                echo "<option value=\"{$row['id']}\" {$selected}>{$row['id']}: {$row['name']}</option>";
            }
        }
        ?></select></td>
    </tr>
    <tr>
        <th>年度</th>
        <td class="in_input"><input type="text" name="year" class="required" required></td>
    </tr>
</table>
<table>
<tr>
    <th rowspan="2"></th>
    <th colspan="2">複写元</th>
    <th rowspan="2">週</th>
    <th rowspan="2">場</th>
    <th rowspan="2">R</th>
    <th rowspan="2">名称</th>
    <th rowspan="2">補足</th>
    <th rowspan="2">新ID<br>(重複と未入力は自動採番)</th>
</tr>
<tr>
    <th>ID</th>
    <th>元年度</th>
</tr>
<?php foreach($data as $row): ?>
<tr>
    <td class="in_input">
        <label style="width: 100%; height:100%;"><input type="checkbox" name="race[<?=$row['race_id']?>][save]" value="1" checked></label>
    </td>
    <td>
        <input type="hidden" name="race[<?=$row['race_id']?>][orig_id]" value="<?=$row['race_id']?>">
        <a href="<?=APP_ROOT_REL_PATH?>race/?race_id=<?=$row['race_id']?>" target="_blank"><?=$row['race_id']?></a>
    </td>
    <td><?=h($row['year'])?></td>
    <td>第<?=h($row['week_id'])?>週<?=h($row['date']==''?'':('('.(new DateTime($row['date']))->format('D').')'))?></td>
    <td><?=h(mb_substr($row['race_course_name']??'',0,2).(mb_strlen($row['race_course_name']??'')>2?'…':''))?></td>
    <td><?=h($row['race_number']??'')?></td>
    <td><?=h($row['race_name'])?></td>
    <td><?=h($row['caption'])?></td>
    <td class="in_input">
        <input type="text"   name="race[<?=h($row['race_id'])?>][new_id]" value="<?=h($row['race_id'])?>" placeholder="重複と未入力は自動">
    </td>
</tr>
<?php endforeach; ?>
</table>
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(false); ?>
</footer>
</body>
</html>
