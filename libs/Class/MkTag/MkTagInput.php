<?php
class MkTagInput extends MkTag{
    protected const Tag='input';
    protected const UseCloseTag=false;

    public function __construct($type='',$name='',$tag_value='',$check_value=null)
    {
        if($type !== ''){ $this->type($type); }
        if($name !== ''){ $this->name($name); }
        if($tag_value !== ''){
            $this->value($tag_value);
            if($check_value!==null){
               $this->checkedIf($check_value);
            }
        }
    }
    public static function Radio($name='',$tag_value=''){
        return new MkTagInputRadio($name,$tag_value);
    }
    public static function Text($name='',$tag_value=''){
        return new MkTagInput('text',$name,$tag_value);
    }
    public static function Number($name='',$tag_value=''){
        return new MkTagInput('number',$name,$tag_value);
    }
    public static function Hidden($name='',$tag_value=''){
        return new MkTagInput('hidden',$name,$tag_value);
    }
    public static function Checkbox($name='',$tag_value=''){
        return new MkTagInput('checkbox',$name,$tag_value);
    }
    public function get(){
        $raw_params=[];
        return $this->getDirect( $raw_params, $this->raw_inner_text);
    }

    /**
     * Replace input type
     */
    public function type(string $type){
        $this->setKV('type',$type);
        return $this;
    }
    public function checked($is_checked=true){
        $this->setBool('checked',filter_var($is_checked,FILTER_VALIDATE_BOOL));
        return $this;
    }
    /**
     * 入力値$inputがvalueと一致していればcheckedを有効化する
     */
    public function checkedIf($input){
        if($this->getValue()==$input){
            $this->checked(true);
        }else{
            $this->checked(false);
        }
        return $this;
    }
    public function readonly(bool $is_readonly=true){
        $this->setBool('readonly',$is_readonly);
        return $this;
    }
    public function disabled(bool $is_disabled=true){
        $this->setBool('disabled',$is_disabled);
        return $this;
    }
    public function required(bool $is_required=true){
        $this->setBool('required',$is_required);
        return $this;
    }
}
