<?php
/** 
 * 競走馬ページ上部の情報表テンプレート
 * @var Page $page
*/
?><table class="horse_base_data">
    <tr>
        <th>馬名</th>
        <td style="min-width: 12em;"><?=h($page->horse->name_ja)?></td>
    </tr>
    <tr>
        <th>馬名(欧字)</th>
        <td><?=h($page->horse->name_en)?></td>
    </tr>
    <tr>
        <th>所属</th>
        <td><?=h($page->horse->tc)?></td>
    </tr>
    <?php
        $trainer_name='';
        $trainer_is_anonymous=false;
        do{
            $trainer=Trainer::getByUniqueName($pdo,$page->horse->trainer_name);
            if($trainer===false || $trainer->is_enabled==0){
                $trainer_name=$page->horse->trainer_name;
                break;
            }
            if($trainer->is_anonymous==1){
                $trainer_is_anonymous=true;
                if(!$page->is_editable){
                    $trainer_name='□□□□';
                    break;
                }
            }
            $trainer_name=$trainer->name?:($trainer->short_name_10?:$page->horse->trainer_name);
        }while(false);
        $trainer_search_tag=new MkTagA($trainer_name??'');
        $trainer_search=new HorseSearch();
        if($page->horse->trainer_name!=''){
            $trainer_search->trainer=$page->horse->trainer_name;
            $trainer_search_tag->href($page->to_horse_search_path.'?'.$trainer_search->getUrlParam());
        }
    ?>
    <tr>
        <th>調教師</th>
        <td><?=$trainer_search_tag?></td>
    </tr>
    <tr>
        <th><?=$setting->birth_year_mode===0?"生年":"世代"?></th>
        <td><?php
        if($page->horse->birth_year>0){
            $birth_year_search=new HorseSearch();
            $birth_year_search->birth_year=$page->horse->birth_year;
            $url =$page->to_horse_search_path.'?'.$birth_year_search->getUrlParam();
            $birth_year_str=$setting->getBirthYearFormat($page->horse->birth_year);
            (new MkTagA(h($birth_year_str),$url))->print();
        }
        ?></td>
    </tr>
    <?php
        $a_tag_sanku=new MkTagA('産駒');
        $a_tag_sanku->setStyles(['display'=>'inline-block','float'=>'right;']);
    ?>
    <tr>
        <th>父</th>
        <td><?php
        $url =$page->to_horse_search_path.'?';
        $sire=Horse::getByHorseId($pdo,$page->horse->sire_id);
        if($sire!==false){
            $sire_search=new HorseSearch();
            $sire_search->sire_id=$sire->horse_id;
            $sire_search->order='birth_year__asc';
            $url .= $sire_search->getUrlParam();
            $sire_name=$sire->name_ja?:$sire->name_en;
            (new MkTagA($sire_name?:ANNONYMOUS_HORSE_NAME,"?horse_id={$sire->horse_id}"))->print();
            $a_tag_sanku->href($url)->print();
        } else if($page->horse->sire_name!=''){
            $sire_search=new HorseSearch();
            $sire_search->sire_name=$page->horse->sire_name;
            $sire_search->order='birth_year__asc';
            $url .= $sire_search->getUrlParam();
            (new MkTagA($page->horse->sire_name,$url))->print();
            $a_tag_sanku->href($url)->print();
        }
        ?></td>
    </tr>
    <tr>
        <th>母</th>
        <td><?php
        $url =$page->to_horse_search_path.'?';
        $mare=Horse::getByHorseId($pdo,$page->horse->mare_id);
        if($mare!==false){
            $mare_search=new HorseSearch();
            $mare_search->mare_id=$mare->horse_id;
            $mare_search->order='birth_year__asc';
            $url .= $mare_search->getUrlParam();
            $mare_name=$mare->name_ja?:$mare->name_en;
            (new MkTagA($mare_name?:ANNONYMOUS_HORSE_NAME,"?horse_id={$mare->horse_id}"))->print();
            $a_tag_sanku->href($url)->print();
        } else if($page->horse->mare_name!=''){
            $mare_search=new HorseSearch();
            $mare_search->mare_name=$page->horse->mare_name;
            $mare_search->order='birth_year__asc';
            $url .= $mare_search->getUrlParam();
            (new MkTagA($page->horse->mare_name,$url))->print();
            $a_tag_sanku->href($url)->print();
        }
        ?></td>
    </tr>
    <tr>
        <th>母の父</th>
        <td><?php
        $bms_name='';
        do{
            if($mare!==false && $mare->is_enabled==1){
                if($mare->sire_id){
                    // 母に父IDがある場合は母の父レコードか母の父名
                    $bms=Horse::getByHorseId($pdo,$mare->sire_id);
                    if($bms!==false && $bms->is_enabled==1){
                        // 母父の馬レコードがある場合はその名前
                        $bms_name=$bms->name_ja?:$bms->name_en;
                        break;
                    }
                }
                // 母に有効な父のレコードがない場合は母の父名
                $bms_name=$mare->sire_name;
                break;
            }
            // 母に父IDがない場合は自身の母父名
            $bms_name = $page->horse->bms_name;
        }while(false);
        if($bms_name!=''){
            $bms_name_search=new HorseSearch();
            $bms_name_search->bms_name=$bms_name;
            $url =$page->to_horse_search_path.'?'.$bms_name_search->getUrlParam();
            (new MkTagA($bms_name,$url))->print();
        }
        ?></td>
    </tr>
</table>
