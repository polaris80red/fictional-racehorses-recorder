<hr>
<input type="button" id="edit_tgl" value="編集" style="<?=EDIT_MENU_TOGGLE===false?'display:none;':'';?>">
<input type="hidden" id="hiddden_horse_id" value="<?=h($page->horse->horse_id)?>">
<input type="button" value="競走馬IDをクリップボードにコピー" onclick="copyToClipboard('#hiddden_horse_id');">
(horse_id=<?=h($page->horse->horse_id)?>)<a id="edit_menu"></a>
<input type="hidden" id="edit_menu_states" value="0">
<div class="edit_menu" style="<?=EDIT_MENU_TOGGLE?'display:none;':'';?>">
<table>
    <tr>
        <td>
            <?=(new MkTagA('この馬の情報を編集',InAppUrl::to('horse/manage/edit/',['horse_id'=>$page->horse->horse_id])))?>
        </td>
        <td>
<?php
    $url=InAppUrl::to(Routes::HORSE_RACE_RESULT_EDIT,['horse_id'=>$page->horse->horse_id]);
    $a_tag=new MkTagA('この馬の戦績を追加');
        $a_tag->href($url);
    if($page->horse->birth_year==null){
        $a_tag->href('')->setStyle('text-decoration','line-through');
        $a_tag->title("生年仮登録馬のため戦績追加不可");
    }
    print $a_tag;
?>
        </td>
        <td>
            <?=(new MkTagA('競走馬ID修正',"./manage/update_horse_id/?horse_id=".urlencode($page->horse->horse_id)))?>
        </td>
    </tr>
    <tr>
        <td><?=(new MkTagA('レース結果一括編集',$race_history->race_count_all>0?InAppUrl::to('horse/manage/bulk_edit/',['horse_id'=>$page->horse->horse_id]):''))?></td>
        <td></td><td></td>
    </tr>
<?php if($page->horse->birth_year!==null): ?>
    <tr>
        <td colspan="2">
<?php
    $a_tag=new MkTagA('最後に開いたレースにこの馬の戦績を追加');
    if(!empty($session->latest_race['id'])){
        $url=InAppUrl::to(Routes::HORSE_RACE_RESULT_EDIT,['horse_id'=>$page->horse->horse_id,'race_id'=>$session->latest_race['id']]);
        $a_tag->href($url);
        if($latest_race_is_exists===true){
            $a_tag->href('')->setStyle('text-decoration','line-through');
            $a_tag->title("最後に開いたレースには既に登録されています");
        }
    }
    print $a_tag;
?>
        </td>
        <td>
<?php
    if(!empty($session->latest_race['id'])){
        $url=$page->getRaceResultUrl($session->latest_race['id']);
        $text= $session->latest_race['year']." ".($session->latest_race['name']?:$session->latest_race['id']);
        (new MkTagA($text,$url))->print();
    }
?>
        </td>
    </tr>
    <tr>
        <td colspan="3">レースを新規登録してからこの馬の戦績を追加</td>
    </tr>
    <tr>
        <td colspan="3" style="text-align: right;">
<?php
$params=['horse_id'=>$page->horse->horse_id];
$url='race/manage/edit/';
$params['year']=$page->horse->birth_year+2;
echo (new MkTagA('[2歳年]'))->href(InAppUrl::to($url,$params));
echo "　";
$params['year']=$page->horse->birth_year+3;
echo (new MkTagA('[3歳年]'))->href(InAppUrl::to($url,$params));
echo "　";
$params['year']=$page->horse->birth_year+4;
echo (new MkTagA('[4歳年]'))->href(InAppUrl::to($url,$params));
echo "　";
$params['year']=$page->horse->birth_year+5;
echo (new MkTagA('[5歳年]'))->href(InAppUrl::to($url,$params));
?>
        </td>
    </tr>
    <tr><td colspan="3"></tr>
    <tr>
        <td rowspan="2">レース検索（重賞）</td>
        <td colspan="2" style="text-align: right;">
<?php
$url_param=new UrlParams(['session_is_not_update'=>1,'grade_g1'=>1,'grade_g2'=>1,'grade_g3'=>1]);
$url=InAppUrl::to("race/list/?");
echo (new MkTagA('[2歳年]'))->href($url.$url_param->toString(['year'=>$page->horse->birth_year+2,'age[20]'=>1]));
echo "　".(new MkTagA('[3歳年]'))->href($url.$url_param->toString(['year'=>$page->horse->birth_year+3,'age[30]'=>1,'age[31]'=>1]));
echo "　".(new MkTagA('[4歳年]'))->href($url.$url_param->toString(['year'=>$page->horse->birth_year+4,'age[31]'=>1,'age[41]'=>1]));
echo "　".(new MkTagA('[5歳年]'))->href($url.$url_param->toString(['year'=>$page->horse->birth_year+5,'age[31]'=>1,'age[41]'=>1]));
?>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: right;">
<?php
    $url_param=new UrlParams(['session_is_not_update'=>1,'grade_g1'=>1,'grade_g2'=>1,'grade_g3'=>1,'show_organization_jra'=>1]);
    $url=InAppUrl::to("race/list/?");
    echo (new MkTagA('[世代基準・中央重賞]'))->href($url.$url_param->toString(['year'=>$page->horse->birth_year+3,'is_generation_search'=>1]));
?>
        </td>
    </tr>
    <tr>
        <td>レース検索（すべて）</td>
        <td colspan="2" style="text-align: right;">
<?php
if($page->horse->birth_year!==null){
    $url_param=new UrlParams(['session_is_not_update'=>1]);
    $url=InAppUrl::to('race/list/?');
    echo (new MkTagA('[2歳年]'))->href($url.$url_param->toString(['year'=>$page->horse->birth_year+2,'age[20]'=>1]));
    echo "　".(new MkTagA('[3歳年]'))->href($url.$url_param->toString(['year'=>$page->horse->birth_year+3,'age[30]'=>1,'age[31]'=>1]));
    echo "　".(new MkTagA('[4歳年]'))->href($url.$url_param->toString(['year'=>$page->horse->birth_year+4,'age[31]'=>1,'age[41]'=>1]));
    echo "　".(new MkTagA('[5歳年]'))->href($url.$url_param->toString(['year'=>$page->horse->birth_year+5,'age[31]'=>1,'age[41]'=>1]));
}
?>
        </td>
    </tr>
<?php endif; ?>
</table>
</div><!-- /.edit_menu -->
<script>
$(function() {
    // 編集メニュー開閉
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
