<?php
/**
 * 競走馬ページ上部の馬名など情報行
 * @var Page $page
 * @var Setting $setting
 * @var Horse $horse
 */
?><div style="float:left">
    <?php $horse_main_name=($horse->name_ja?:($horse->name_en===''?ANNONYMOUS_HORSE_NAME:''));?>
    <?php if($horse_main_name): ?>
        <span style="font-size:1.2em;"><?=h($horse_main_name)?></span>
    <?php endif; ?>
    <?php if($horse->name_en): ?>
        <?=$horse_main_name?'&nbsp;':''?>
        <span style="font-size:1.1em;"><?=h($horse->name_en)?></span>
    <?php endif; ?>
    <?php if($horse->birth_year>0): ?>
        <?php
            if($setting->year_view_mode===Setting::YEAR_VIEW_MODE_DEFAULT){
                $birth_year_str="（{$horse->birth_year}）";
            }else{
                $birth_year_str="（{$setting->getBirthYearFormat($horse->birth_year)}）";
            }
        ?>
        <?=h($birth_year_str)?>
    <?php endif; ?>
    <?=h("{$horse->color} {$sex_str}")?>
</div>
<div style="float:right;">
    <?php if($page->has_edit_menu && $page->is_editable): ?>
    <a href="#edit_menu" style="text-decoration: none;" title="下部編集メニューへスクロール">▽</a>
    <?php else: ?>
    <a href="#under_results_table" style="text-decoration: none;" title="下部へスクロール">▽</a>
    <?php endif; ?>
</div>
<hr style="clear: both;margin-bottom: 2px;">
<?php $param=['horse_id'=>$horse->horse_id,'show_registration_only'=>$show_registration_only??false]; ?>
<div style="font-size:0.9em;"><a href="<?=InAppUrl::to('horse/',$param)?>">TOP</a>
｜<a href="<?=InAppUrl::to('horse/results_detail.php',$param)?>">詳細戦績</a></div>
<hr>
