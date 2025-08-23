<?php
session_start();
require_once dirname(__DIR__,2).'/libs/init.php';
defineAppRootRelPath(2);
$page=new Page(2);
$setting=new Setting();
$page->setSetting($setting);
$page->title="レース結果";
$session=new Session();
// 暫定でログイン＝編集可能
$page->is_editable=Session::is_logined();

$page->error_return_url=$page->to_race_list_path;
$page->error_return_link_text="レース検索に戻る";

$pdo= getPDO();

$is_edit_mode = false;
if(filter_input(INPUT_GET,'mode')==='edit'){
    $is_edit_mode = true;
}
$is_edit_mode=true;
if(empty($_GET['race_id'])){
    $page->error_msgs[]="レースID未指定";
    $page->printCommonErrorPage();
    exit;
}
$race_id=filter_input(INPUT_GET,'race_id');
$show_registration_only=(bool)filter_input(INPUT_GET,'show_registration_only');
# レース情報取得
$race = new RaceResults($pdo, $race_id);
if(!$race->record_exists){
    $page->error_msgs[]="レース情報取得失敗";
    $page->error_msgs[]="入力ID：{$race_id}";
    $page->printCommonErrorPage();
    exit;
}
$session->latest_race=[
    'id'=>$race_id,
    'year'=>$race->year,
    'name'=>$race->race_short_name?:$race->race_name
];
$session->login_return_url='race/result/?race_id='.$race_id;
$race_access_history=(new RaceAccessHistory())->set($race_id)->saveToSession();

$week_data=RaceWeek::getById($pdo,$race->week_id);
$week_month=$week_data->month??null;
$turn=$week_data->umm_month_turn??null;

?><!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php $page->printTitle(); ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?php $page->printBaseStylesheetLinks(); ?>
    <?php $page->printJqueryResource(); ?>
    <?php $page->printScriptLink("js/functions.js"); ?>
<style>
.race_results td:nth-child(1){ text-align:center; }
.race_results td:nth-child(2){ text-align:center; }
.race_results td:nth-child(3){ text-align:center; }
.race_results td:nth-child(5){ text-align:center; }
.race_results td:nth-child(8){ text-align:center; }
.race_results td:nth-child(10){ text-align:center; }
.race_info th{ background-color: #EEEEEE; }
.disabled_row{ background-color: #dddddd; }
</style>
</head>
<body>
<header>
<?php $page->printHeaderNavigation(); ?>
<h1 class="page_title"><?php echo $page->title; ?></h1>
</header>
<main id="content">
<hr class="no-css-fallback">
<?php require_once APP_ROOT_DIR."/race/race_content_header.inc.php"; ?>
<hr>
<?php
# レース着順取得
$sql=(function(){
    $horse_tbl=Horse::TABLE;
    $r_results_tbl=RaceResults::TABLE;
    $rr_detail_tbl=RaceResultDetail::TABLE;

    $horse_s_columns=new SqlMakeSelectColumns(Horse::TABLE);
    $horse_s_columns->addColumnsByArray([
        'name_ja','name_en','birth_year'
    ]);
    $horse_s_columns->addColumnAs('sex','horse_sex');
    $horse_s_columns->addColumnAs('tc','horse_tc');
    $horse_s_columns->addColumnAs('training_country','horse_training_country');
    $horse_s_columns->addColumnAs('is_affliationed_nar','horse_is_affliationed_nar');
    $sql_part_select_columns=implode(",\n",[
        "`det`.*",
        $horse_s_columns->get(true),
        "`race`.*"
    ]);
     
    $sql=<<<END
    SELECT
    {$sql_part_select_columns}
    FROM `{$r_results_tbl}` AS `race`
    LEFT JOIN `{$rr_detail_tbl}` AS `det`
        ON `race`.`race_id`=`det`.`race_results_id`
    LEFT JOIN `{$horse_tbl}`
        ON `det`.`horse_id`=`{$horse_tbl}`.`horse_id`
    WHERE `race_id`=:race_id
    ORDER BY
        `det`.`result_number` IS NULL,
        `det`.`result_number` ASC,
        `det`.`result_order` IS NULL,
        `det`.`result_order` ASC,
        `det`.`result_text` ASC;
    END;
    return $sql;
})();

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':race_id', $race_id, PDO::PARAM_STR);
$flag = $stmt->execute();
$table_data=[];
while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if(empty($data['horse_id'])){ continue; }
    $data['sex']=(int)($data['sex']?:$data['horse_sex']);
    $data['sex_str']=sex2String($data['sex']);
    $data['age']=empty($data['birth_year'])?'':($race->year-$data['birth_year']);
    $table_data[]=$data;
}
$empty_row_2="<td>&nbsp;</td><td></td><td class=\"horse_name\"></td><td></td><td></td><td></td><td></td><td></td><td></td>";
?>
<table class="race_results">
<tr>
<th>着順</th><th>枠</th><th>馬番</th>
<th style="min-width:12em;">馬名</th>
<th><?php if(
    $setting->age_view_mode===Setting::AGE_VIEW_MODE_UMAMUSUME||
    $setting->age_view_mode===Setting::AGE_VIEW_MODE_UMAMUSUME_S){
        print '級';
    }else{ print '性齢'; }
    ?></th>
<th>負担<br>重量</th>
<th>着差</th>
<th>コーナー<br>通過順位</th>
<th>所属</th>
<th>人気</th>
<?php if($page->is_editable): ?><th>編</th><?php endif; ?>
</tr><?php
$i=0;
$registration_only_horse_is_exists=false;
foreach ($table_data as $data) {
    $i++;
    $tr_class=new imploader(' ');
    // 馬情報がない異常データのスキップ
    if(empty($data['horse_id'])){
        Elog::debug($data);
        //echo "<tr><td></td>".$empty_row_2."</tr>\n";
        continue;
    }
    // 特別登録のみのデータは表示フラグがなければスキップ
    $horse_url_add_param='';
    if($data['is_registration_only']){
        $registration_only_horse_is_exists=true;
        if(!$show_registration_only){
            continue;
        }else{
            $horse_url_add_param='&show_registration_only=true';
            $tr_class->add('disabled_row');
        }
    }
    // 途中着順の場合
    /*
    18着以内かつ現在処理中の行より先の着順が出てきたときはその着順まで空行を挟む。
    */
    if($data['result_number']>$i && $data['result_number']<=18){
        for($j=$i;$j<$data['result_number'];$j++){
            echo "<tr class=\"result_number_{$j}\"><td>{$j}</td>".$empty_row_2;
            if($page->is_editable){
                echo "<td>";
                if(!empty($data['horse_id'])){
                    $url=$page->to_app_root_path."race/horse_result/form.php?race_id={$race->race_id}&result_number={$i}";
                    echo '<a href="'.$url.'" title="新規登録">新</a><br>';
                }
                echo "</td>";
            }
            echo "</tr>\n";
            $i++;
        }
    }
    $tr_class->add('result_number_'.$data['result_number']);
?><tr class="<?php echo $tr_class; ?>">
<td><?php
if($data['result_text']!=''){
    print_h($data['result_text']);
}else if($data['result_number']>0){
    print($data['result_number']);
    if($data['result_before_demotion']>0){ print"<span title=\"※".$data['result_before_demotion']."位入線降着\">(降)</span>";}
}
?></td>
<td class="waku_<?php echo $data['frame_number']; ?>"><?php echo $data['frame_number']; ?></td>
<td><?php echo $data['horse_number']; ?></td>
<td class="horse_name">
<?php
    $training_country='';
    if(!empty($data['training_country'])){
        $training_country=$data['training_country'];
    }else{
        $training_country=$data['horse_training_country'];
    }
    if(($data['is_jra']==1 || $data['is_nar']==1)&& $training_country!='JPN'){
        echo "[外] ";
    }
    $is_affliationed_nar=0;
    if($data['is_affliationed_nar']===null){
        $is_affliationed_nar=$data['horse_is_affliationed_nar'];
    }else{
        $is_affliationed_nar=$data['is_affliationed_nar'];
    }
    if($data['is_jra']==1&& $is_affliationed_nar==1){
        echo "[地] ";
    }
    echo '<a href="'.$page->to_app_root_path.'horse/?horse_id='.$data['horse_id'].$horse_url_add_param.'">';
    echo ($data['name_ja']?:$data['name_en']);
    if($data['is_jra']==0 && $data['is_nar']==0){
        echo " <span>({$training_country})</span> ";
    }
    echo "</a>";
?></td>
<td class="sex_<?php echo $data['sex']; ?>"><?php
    if($setting->age_view_mode===Setting::AGE_VIEW_MODE_DEFAULT){
        // 通常表記の場合
        print $data['sex_str'];
    }
    echo $setting->getAgeSexSpecialFormat($data['age'],$data['sex']);
?></td>
<td><?php echo $data['handicap']; ?></td>
<td><?php echo $data['margin']; ?></td>
<td><?php
    $corner_numbers=[];
    if($data['corner_1']>0){ $corner_numbers[]=$data['corner_1']; }
    if($data['corner_2']>0){ $corner_numbers[]=$data['corner_2']; }
    if($data['corner_3']>0){ $corner_numbers[]=$data['corner_3']; }
    if($data['corner_4']>0){ $corner_numbers[]=$data['corner_4']; }
    echo implode('-',$corner_numbers);
?></td>
<td><?php echo !empty($data['tc'])?$data['tc']:$data['horse_tc']; ?></td>
<td class="favourite_<?php echo $data['favourite']; ?>"><?php echo $data['favourite']; ?></td>
<?php
    if(!empty($data['horse_id'])){
        $url=$page->to_app_root_path."race/horse_result/form.php?race_id={$race->race_id}&horse_id={$data['horse_id']}&edit_mode=1";
    }
?>
<?php if($page->is_editable): ?>
<td>
<a href="<?php echo $url; ?>" title="編集">編</a>
</td>
<?php endif; ?>
</tr>
<?php } ?></table>
<?php if($page->is_editable): ?>
<hr><input type="button" id="edit_tgl" value="編集">
<input type="hidden" id="hiddden_race_id" value="<?php echo $race->race_id; ?>">
<input type="button" value="レースIDをクリップボードにコピー" onclick="copyToClipboard('#hiddden_race_id');">
(race_id=<?php echo $race->race_id; ?>)
<div class="edit_menu" style="display:none; border:solid 1px #00FFFF; margin-top:0.2em;">
<hr> 
<input type="hidden" id="edit_menu_states" value="0">
<?php $url="{$page->to_app_root_path}race/result/form.php?race_id={$race->race_id}&edit_mode=1"; ?>
<a href="<?php echo $url; ?>">[このレースの情報を編集]</a>
　
<?php $url="{$page->to_app_root_path}race/horse_result/form.php?race_id={$race->race_id}"; ?>
<a href="<?php echo $url; ?>">[このレースの戦績を追加]</a>
<?php if(!empty($session->latest_horse['id'])): ?>
<hr>
<?php
$url="{$page->to_app_root_path}race/horse_result/form.php?horse_id={$session->latest_horse['id']}&race_id={$race->race_id}";
$a_tag=new MkTagA('[最後に開いた馬をこのレースに追加]');
$latest_horse=new Horse();
$latest_horse->setDataById($pdo,$session->latest_horse['id']);
if($latest_horse->birth_year!==null){
    $a_tag->href($url);
}else{
    $a_tag->title("生年仮登録馬のため戦績追加不可")->setStyle('text-decoration','line-through');;
}
print $a_tag."<br>";
?>
<?php $url="{$page->to_app_root_path}horse/?horse_id={$session->latest_horse['id']}"; ?>
（<a href="<?php echo $url; ?>"><?php echo ($session->latest_horse['name']?:$session->latest_horse['id']) ?></a>）
<?php endif; ?>
<hr>
<a href="<?php echo $page->to_app_root_path; ?>race/result/form.php?race_id=<?php echo $race->race_id; ?>&edit_mode=0">[コピーして新規登録]</a><?php
    $addparams=implode('&',[
        "date=".$race->date,
        "year=".$race->year,
        "month=".$race->month,
        "race_course_name=".$race->race_course_name
    ])
?>　<a href="<?php echo $page->to_app_root_path; ?>race/result/form.php?<?php echo $addparams; ?>">[同日同場で新規登録]</a><br>
<hr>
<a href="<?php echo $page->to_app_root_path; ?>race/update_race_result_id/form.php?race_id=<?php echo $race->race_id; ?>&edit_mode=1">[レースID修正]</a>
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
<?php endif; ?>
<hr>
<a href="<?php echo $page->getRaceNameSearchUrl($race->race_name); ?>" style="">他年度の<?php echo $race->race_name; ?>を検索</a>
<?php
    if($registration_only_horse_is_exists||$show_registration_only){
        $a_tag=new MkTagA("特別登録のみの馬を".($show_registration_only?"非表示(現在:表示)":"表示(現在:非表示)")."");
        $a_tag->href("?race_id={$race_id}".($show_registration_only?'':"&show_registration_only=true"));
        $a_tag->print();
    }
    ?>
<hr>
<table class="race_info">
<tr><th>名称</th><td><?php print_h($race->race_name); ?></td></tr>
<tr><th>略名</th><td><?php print_h($race->race_short_name); ?></td></tr>
<tr><th>補足</th><td style="min-width: 200px;"><?php print_h($race->caption); ?></td></tr>
<?php if($race->date): ?>
<tr>
    <th>日付</th>
    <?php
    $a_tag=new MkTagA($race->date.($race->date&&$race->is_tmp_date?'(仮)':''));
    if(!$race->is_tmp_date){
        $a_tag->href($page->getDateRaceListUrl($race->date));
    }
    ?>
    <td><?=$a_tag; ?></td></tr>
<?php endif; ?>
<?php if(!empty($race->week_id)): ?>
<tr><th>ターン</th><td><?php
    print $setting->getYearSpecialFormat($race->year)."｜";
    $week_url_param=new UrlParams(['year'=>$race->year,'week'=>$race->week_id]);
    $a_tag=new MkTagA("第{$race->week_id}週");
    $a_tag->href($page->to_app_root_path."race/list/in_week.php?".$week_url_param);
    print $a_tag;
    print "｜";
    $turn_url_param=new UrlParams(['year'=>$race->year,'month'=>$week_month,'turn'=>$turn]);
    $a_tag=new MkTagA("{$week_month}月".($turn===2?"後半":"前半"));
    $a_tag->href($page->to_app_root_path."race/list/in_week.php?".$turn_url_param);
    print $a_tag;
?></td></tr>
<?php endif; ?>
<tr><th>備考</th><td><?php print(str_replace(["\r\n","\r","\n"],"<br>\n",h($race->note))); ?></td></tr>
</table>
<hr class="no-css-fallback">
</main>
<footer>
<?php $page->printFooterHomeLink(); ?>
</footer>
</body>
</html>