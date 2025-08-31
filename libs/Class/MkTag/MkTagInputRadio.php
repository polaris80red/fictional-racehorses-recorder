<?php
class MkTagInputRadio extends MkTagInput{
    public function __construct(string $name='',$tag_value='',$check_value=null)
    {
        parent::__construct('radio',$name,$tag_value,$check_value);
    }
}
