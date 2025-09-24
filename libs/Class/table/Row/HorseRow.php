<?php
class HorseRow extends TableRow {
    use TableRowValidate;
    public const INT_COLUMNS=[
        'world_id',
        'birth_year',
        'birth_month',
        'birth_day_of_month',
        'sex',
        'is_affliationed_nar',
        'is_sire_or_dam',
        'is_enabled',
    ];
    public const STR_COLUMNS=[
        'horse_id',
        'name_ja',
        'name_en',
        'color',
        'tc',
        'trainer_name',
        'training_country',
        'owner_name',
        'breeder_name',
        'breeding_country',
        'sire_id',
        'sire_name',
        'mare_id',
        'mare_name',
        'bms_name',
        'meaning',
        'note',
        'profile',
        'created_at',
        'updated_at',
    ];
    public $horse_id;
    public $world_id;
    public $name_ja;
    public $name_en;
    public $birth_year=null;
    public $birth_month=null;
    public $birth_day_of_month=null;
    public $sex;
    public $color ='';
    public $tc ='';
    public $trainer_name =null;
    public $training_country ='JPN';
    public $owner_name =null;
    public $breeder_name =null;
    public $breeding_country ='';
    public $is_affliationed_nar =0;
    public $sire_id =null;
    public $sire_name ='';
    public $mare_id =null;
    public $mare_name ='';
    public $bms_name ='';
    public $is_sire_or_dam =0;
    public $meaning ='';
    public $note ='';
    public $profile =null;
    public $is_enabled=1;
    public $created_at =null;
    public $updated_at =null;

    public function validate():bool{
        if(exists_htmlspecialchars($this->horse_id)){
            $this->addErrorMessage("IDに使用できない文字（HTML）を含んでいます");
        }
        $enc_horse_id=urlencode($this->horse_id);
        if($this->horse_id!==$enc_horse_id){
            $this->addErrorMessage("IDに使用できない文字（URL直接使用不可）を含んでいます");
        }
        if(strpbrk($this->horse_id,ITEM_ID_FORBIDDEN_CHARS)){
            $this->addErrorMessage("IDに使用できない文字（その他）を含んでいます");
        }
        $this->validateStrLength($this->horse_id,'競走馬ID',100);

        $this->validateStrLength($this->name_ja,'馬名',18);
        $this->validateStrLength($this->name_en,'欧字馬名',18);
        if(intval($this->birth_day_of_month)>0 && $this->birth_month==null){
            $this->addErrorMessage("誕生日指定時は誕生月も指定してください。");
        }
        $this->validateStrLength($this->color,'毛色',3);
        $this->validateStrLength($this->tc,'所属',10);
        $this->validateStrLength($this->trainer_name,'調教師名',32);
        $this->validateStrLength($this->training_country,'調教国コード',3);
        $this->validateStrLength($this->owner_name,'馬主名',50);
        $this->validateStrLength($this->breeder_name,'生産者名',50);
        $this->validateStrLength($this->breeding_country,'生産国コード',3);
        $this->validateStrLength($this->sire_id,'父ID',100);
        $this->validateStrLength($this->sire_name,'父名',18);
        $this->validateStrLength($this->mare_id,'母ID',100);
        $this->validateStrLength($this->mare_name,'母名',18);
        $this->validateStrLength($this->bms_name,'母父名',18);
        if(($this->sex===0||$this->sex===3)&&$this->is_sire_or_dam===1){
            $this->addErrorMessage("牡馬・牝馬以外は繁殖馬に設定できません。");
        }
        $this->validateStrLength($this->meaning,'馬名意味',100);
        $this->validateStrLength($this->profile,'プロフィール',10000);
        $this->validateStrLength($this->note,'備考',100);
        return !$this->hasErrors;
    }
    public function setFromPost(){
        // 送信された内容をセット
        $this->horse_id=(string)filter_input(INPUT_POST,'horse_id');
        $this->world_id=(int)filter_input(INPUT_POST,'world_id');
        $this->name_ja=filter_input(INPUT_POST,'name_ja');
        $this->name_en=filter_input(INPUT_POST,'name_en');
        // 生年は未定義と0を区別
        $birth_year=(string)(filter_input(INPUT_POST,'birth_year')?:filter_input(INPUT_POST,'birth_year_select'));
        $this->birth_year=$birth_year===''?null:(int)$birth_year;

        // 誕生月の補正
        $birth_month=(int)(filter_input(INPUT_POST,'birth_month')?:filter_input(INPUT_POST,'birth_month_select'));
        $this->birth_month=($birth_month>0 && $birth_month<=12)?$birth_month:null;

        // 誕生日の補正
        $birth_day_of_month=(int)(filter_input(INPUT_POST,'birth_day_of_month')?:filter_input(INPUT_POST,'birth_day_of_month_select'));
        $this->birth_day_of_month=($birth_day_of_month>0 && $birth_day_of_month<=31)?$birth_day_of_month:null;

        $this->sex=(int)filter_input(INPUT_POST,'sex');

        $this->color=(string)(filter_input(INPUT_POST,'color')?:filter_input(INPUT_POST,'color_select'));
        $this->tc=(string)(filter_input(INPUT_POST,'tc')?:filter_input(INPUT_POST,'tc_select'));

        $this->trainer_name=filter_input(INPUT_POST,'trainer_name')?:null;
        $this->training_country=filter_input(INPUT_POST,'training_country');
        $this->owner_name=filter_input(INPUT_POST,'owner_name')?:null;
        $this->breeder_name=filter_input(INPUT_POST,'breeder_name')?:null;
        $this->breeding_country=filter_input(INPUT_POST,'breeding_country')?:null;
        $this->is_affliationed_nar=filter_input(INPUT_POST,'is_affliationed_nar');

        $this->sire_id=filter_input(INPUT_POST,'sire_id');
        $this->sire_name=filter_input(INPUT_POST,'sire_name');
        $this->mare_id=filter_input(INPUT_POST,'mare_id');
        $this->mare_name=filter_input(INPUT_POST,'mare_name');
        $this->bms_name=filter_input(INPUT_POST,'bms_name');

        $this->is_sire_or_dam=filter_input(INPUT_POST,'is_sire_or_dam',FILTER_VALIDATE_INT);
        $this->meaning=filter_input(INPUT_POST,'meaning');
        $this->profile=filter_input(INPUT_POST,'profile');
        $this->note=filter_input(INPUT_POST,'note')?:'';
        
        $this->is_enabled=filter_input(INPUT_POST,'is_enabled');
    }
}
