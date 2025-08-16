<?php
class MkTagInputRadio extends MkTagInput{
    protected $type='radio';
    public function __construct(string $name='',$tag_value='',$check_value=null)
    {
        parent::__construct($this->type,$name,$tag_value,$check_value);
    }
}
