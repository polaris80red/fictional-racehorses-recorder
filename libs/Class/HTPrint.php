<?php
class HTPrint{
    /**
     * @return string
     */
    private static function PrintOrReturn($string, bool $return=false){
        if($return===false){
            print $string;
        }
        return $string;
    }
    /**
     * $inputが0でない場合だけprintする
     */
    public static function ifZero2Empty($input){
        if(intval($input)!==0){
            print $input;
        }
    }
    /**
     * $inputが空でなければ$outputを返す
     */
    public static function IfNotEmpty($input, string $output='', bool $return=false){
        if(empty($input)){ return; }
        return self::PrintOrReturn($output,$return);
    }
    /**
     * inputが空でなければcheckedを返す
     */
    public static function CheckedIfNotEmpty($input, $return=false){
        return self::IfNotEmpty($input,' checked',$return);
    }
    /**
     * $inputが$searchと一致していればcheckedを返す
     */
    public static function CheckedIfEqual($input, $search, bool $return=false){
        if($input==$search){
            return self::PrintOrReturn(' checked',$return);
        }
    }
    /**
     * bool型でtrueならcheckedを返す
     */
    public static function Checked(bool $bool = true, bool $return=false){
        if($bool){
            return self::PrintOrReturn(' checked',$return);
        }
    }
    /**
     * Hiddenのinputタグを生成
     */
    public static function Hidden($name, $value, bool $return=false){
        $html="<input type=\"hidden\" name=\"".h($name)."\" value=\"".h($value)."\">";
        return self::PrintOrReturn($html,$return);
    }
    /**
     * Hiddenのinputタグとvalueのテキストを生成
     */
    public static function HiddenAndText($name, $value, bool $return=false){
        $html=h($value).self::Hidden($name,$value,true);
        return self::PrintOrReturn($html,$return);
    }
    /**
     * ラジオボタンのinputタグを生成
     */
    public static function Radio($name, $value, $checked_value, string $raw_inner_text='', bool $return=false){
        $html="<input type=\"radio\" name=\"".h($name)."\" value=\"".h($value)."\"".self::CheckedIfEqual($checked_value,$value,true).($raw_inner_text?" $raw_inner_text":"").">";
        return self::PrintOrReturn($html,$return);
    }
    /**
     * 配列からDataListタグをprint
     */
    public static function DataList(
        string $list_id,
        array $data_list,
        array $caption_list=[],
        bool $return=false
    ) {
        $html="<datalist id=\"{$list_id}\">\n";
        foreach($data_list as $key=>$val){
            $html.="<option value=\"{$val}\">".(!empty($caption_list[$key])?h($caption_list[$key]):'')."</option>\n";
        }
        $html.="</datalist>\n";
        return self::PrintOrReturn($html,$return);
    }
}
