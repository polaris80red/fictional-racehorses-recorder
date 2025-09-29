<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
defineAppRootRelPath(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース結果一括複写・実行";
$page->ForceNoindex();
$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }  
$pdo= getPDO();

$posted_race_list = filter_input(INPUT_POST, 'race', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$year=filter_input(INPUT_POST,'year');
$world_id=filter_input(INPUT_POST,'world_id');

$insert_objs=[];
$currentUser=Session::currentUser();
$currentUserId=$currentUser->getId();
foreach($posted_race_list as $key => $posted_race){
    if(empty($posted_race['save'])){
      continue;
    }
    $race_obj=new Race($pdo,$posted_race['orig_id']);
    $race_obj->race_id= $posted_race['new_id']??'';
    if($posted_race['new_id']!=='' && (new Race($pdo,$posted_race['new_id']))->record_exists){
      $race_obj->race_id='';
    }
    $race_obj->world_id=$world_id;
    $race_obj->date = null;
    $race_obj->is_tmp_date = 1;
    $race_obj->year = $year;
    $race_obj->previous_note='';
    $race_obj->after_note='';
    $race_obj->created_by=$currentUserId;
    $race_obj->updated_by=$currentUserId;
    $race_obj->created_at=PROCESS_STARTED_AT;
    $race_obj->updated_at=PROCESS_STARTED_AT;
    $insert_objs[]=$race_obj;
}
$insert_id_list=[];
foreach($insert_objs as $race){
  $race->InsertExec($pdo);
  $insert_id_list[]=$race->race_id;
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
    $sql.=" ORDER BY `year` ASC,`week_id` ASC, `date` ASC";
    $stmt=$pdo->prepare($sql);
    $binder->bindTo($stmt);
    $stmt->execute();
    return $stmt->fetchAll();
})($pdo,$insert_id_list);
$world=World::getById($pdo,$world_id);
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
<p>[<?=h($world->name)?>]に以下のレースを登録しました。</p>
<table>
<tr>
  <th>ID</th>
  <th>年度</th>
  <th>週</th>
  <th>名称</th>
</tr>
<?php foreach($data as $row): ?>
<tr>
  <td><?=h($row['race_id'])?></td>
  <td><?=h($row['year'])?></td>
  <td><?=h($row['week_id'])?></td>
  <td><?=h($row['race_name'])?></td>
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
