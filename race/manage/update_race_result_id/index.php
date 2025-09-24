<?php
session_start();
require_once dirname(__DIR__,3).'/libs/init.php';
InAppUrl::init(3);
$page=new Page(3);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース結果ID一括修正";
$page->ForceNoindex();
$session=new Session();
if(!Session::isLoggedIn()){ $page->exitToHome(); }

$race_result_id=filter_input(INPUT_GET,'race_id');

$pdo= getPDO();
# 対象取得
do{
}while(false);

?><!DOCTYPE html>
<html>
<head>
    <title><?=h($page->title)?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$page->getMetaNoindex()?>
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink('js/functions.js'); ?>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?=h($page->title)?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<form action="confirm.php" method="post">
<table class="edit-form-table">
<tr>
    <th>置換前レースID</th>
    <td style="min-width:15em;"><?php HTPrint::HiddenAndText('race_id',$race_result_id); ?></td>
</tr>
<tr>
    <th>置換後レースID</th>
    <td class="in_input"><input type="text" name="new_race_id" value="" onchange="checkRaceIdExists();"></td>
</tr>
<tr id="duplicate_id">
    <th>新ID重複確認</th>
    <td><a id="duplicate_id_link" href="" target="_blank"></a></td>
</tr>
</table>
<hr>
<input type="submit" value="処理内容確認">
</form>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
<script>
function checkRaceIdExists() {
  const raceId = $('input[name="new_race_id"]').val().trim();
  if (raceId !== '') {
    $.ajax({
      url: '<?=InAppUrl::to('api/checkRaceIdExists.php')?>',
      method: 'GET',
      data: { race_id: raceId },
      dataType: 'text',
      success: function(response) {
        if (response.trim() === 'true') {
          const href_pref ='<?=InAppUrl::to('race/result/?race_id=')?>';
          $('#duplicate_id_link').attr('href', href_pref+raceId);
          $('#duplicate_id_link').text('ID: '+raceId+' は存在します');
        }else{
          $('#duplicate_id_link').attr('href', '');
          $('#duplicate_id_link').text('');
        }
      },
      error: function(xhr, status, error) {
        console.error('通信エラー:', error);
      }
    });
  }
}
</script>
</body>
</html>
