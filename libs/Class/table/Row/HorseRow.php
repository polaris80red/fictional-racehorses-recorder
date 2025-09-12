<?php
class HorseRow extends TableRow {
    public const INT_COLUMNS=[
        'world_id',
        'birth_year',
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
    ];
    public $horse_id;
    public $world_id;
    public $name_ja;
    public $name_en;
    public $birth_year=null;
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
}
