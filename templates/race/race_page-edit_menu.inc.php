<hr>
<input type="button" id="edit_tgl" value="編集" style="<?=!EDIT_MENU_TOGGLE?'display:none;':''?>">
<input type="hidden" id="hiddden_race_id" value="<?=h($race->race_id)?>">
<input type="button" value="レースIDをクリップボードにコピー" onclick="copyToClipboard('#hiddden_race_id');">
(race_id=<?=h($race->race_id)?>)<a id="edit_menu"></a>
<div class="edit_menu" style="<?=EDIT_MENU_TOGGLE?'display:none;':''?>"> 
<input type="hidden" id="edit_menu_states" value="0">
<table>
    <tr>
        <?php $url=InAppUrl::to('race/manage/edit/',['race_id'=>$race->race_id,'edit_mode'=>1]);?>
        <td><a href="<?=h($url)?>">このレースの情報を編集</a></td>
        <?php $url=InAppUrl::to('race/horse_result/form.php',['race_id'=>$race->race_id]);?>
        <td><a href="<?=h($url)?>">このレースに戦績を追加</a></td>
        <?php $url=InAppUrl::to('race/manage/update_race_result_id/',['race_id'=>$race->race_id,'edit_mode'=>1]);?>
        <td><a href="<?=h($url)?>">レースID修正</a></td>
    </tr>
    <tr>
<?php
$a_tag=new MkTagA('最後に開いた馬をこのレースに追加');
$latest_horse=new Horse();
if(!empty($session->latest_horse['id'])){
    $latest_horse->setDataById($pdo,$session->latest_horse['id']);
}
if($latest_horse->record_exists){
    if($latest_horse_exists){
        $a_tag->title("最後に開いた競走馬は既に登録されています")->setStyle('text-decoration','line-through');
    }else if($latest_horse->birth_year==null){
        $a_tag->title("生年仮登録馬のため戦績追加不可")->setStyle('text-decoration','line-through');
    }else{
        $url=InAppUrl::to('race/horse_result/form.php',['horse_id'=>$session->latest_horse['id'],'race_id'=>$race->race_id]);
        $a_tag->href($url);
    }
}
?>
        <td colspan="2"><?=$a_tag?></td>
        <td>
<?php if(!empty($session->latest_horse['id'])): ?>
    <?php $url=InAppUrl::to('horse/',['horse_id'=>$session->latest_horse['id']]);?>
    <a href="<?=h($url)?>"><?=h($session->latest_horse['name']?:$session->latest_horse['id'])?></a>
<?php endif; ?>
        </td>
    </tr>
    <tr>
        <td>
            <?php $url=InAppUrl::to('race/manage/edit/',['race_id'=>$race->race_id,'edit_mode'=>0]);?>
            <a href="<?=h($url)?>">コピーして新規登録</a>
        </td>
<?php
    $a_tag=new MkTagA('同日同場で新規登録');
    if($race->date!=''){
        $a_tag->setLinkText('同日同場で新規登録');
        $a_tag->href(InAppUrl::to('race/manage/edit/',[
            'date'=>$race->date,
            'race_course_name'=>$race->race_course_name
        ]));
    }else{
        $a_tag->setLinkText('同週同場で新規登録');
        $a_tag->href(InAppUrl::to('race/manage/edit/',[
            'year'=>$race->year,
            'week_id'=>$race->week_id,
            'race_course_name'=>$race->race_course_name
        ]));
    }
    ?>
        <td><?=$a_tag?></td>
        <td></td>
    </tr>
</table>
</div>
<script>
$(function() {
    $('#edit_tgl').click(function(){
    if($('#edit_menu_states').val()=='0') {
        $('.edit_menu').css('display','block');
        $('#edit_menu_states').val('1');
    } else {
        $('.edit_menu').css('display','none');
        $('#edit_menu_states').val('0');
    }
    });
});
</script>
