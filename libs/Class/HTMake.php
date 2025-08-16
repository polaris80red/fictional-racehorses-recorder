<?php
class HTMake{
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
     * 値が0の場合は空、それ以外は入力値を返す
     */
    public static function ifZero2Empty($input){
        if(intval($input)!==0){ return $input; }
        return '';
    }
    /**
     * $inputが空でなければ、指定した値（$output）を返す
     */
    public static function IfNotEmpty($input, string $output_if_not_empty=''){
        if(empty($input)){ return ''; }
        return $output_if_not_empty;
    }
    /**
     * inputが空でなければcheckedを返す
     */
    public static function CheckedIfNotEmpty($input){
        return self::IfNotEmpty($input,'checked');
        return '';
    }
    /**
     * $inputが$searchと一致していればcheckedを返す
     */
    public static function CheckedIfEqual($input, $search){
        if($input==$search){ return 'checked';}
        return '';
    }
    /**
     * $inputが$searchと一致していればselectedを返す
     */
    public static function SelectedIfEqual($input, $search){
        if($input==$search){ return 'selected';}
        return '';
    }
    /**
     * Hiddenのinputタグを生成
     */
    public static function Hidden($name, $value){
        return "<input type=\"hidden\" name=\"{$name}\" value=\"{$value}\">";
    }
    /**
     * Hiddenのinputタグとvalueのテキストを生成
     */
    public static function HiddenAndText($name, $value){
        $html=$value.self::Hidden($name,$value);
        return $html;
    }
    /**
     * optionタグを生成
     */
    public static function Option($label, $value, $selected_value){
        $html="<option value=\"{$value}\"".self::SelectedIfEqual($selected_value,$value).">{$label}</option>";
        return $html;
    }
    /**
     * ラジオボタンのinputタグを生成
     */
    public static function Radio($name, $value, $checked_value){
        $html="<input type=\"radio\" name=\"{$name}\" value=\"{$value}\"".self::CheckedIfEqual($checked_value,$value).">";
        return $html;
    }
    /**
     * aタグを生成
     */
    public static function ALink(string $link_text, string $url='', string $raw_inner_text=''){
        if($raw_inner_text!==''){ $raw_inner_text= " ".$raw_inner_text; }
        $href=($url==="")?"":" href=\"{$url}\"";
        $html="<a{$href}{$raw_inner_text}>".$link_text."</a>";
        return $html;
    }
    /**
     * 配列からDataListタグをprint
     */
    public static function DataList(
        string $list_id,
        array $data_list,
        array $caption_list=[]
    ) {
        $html="<datalist id=\"{$list_id}\">\n";
        foreach($data_list as $key=>$val){
            $html.="<option value=\"{$val}\">".(!empty($caption_list[$key])?$caption_list[$key]:'')."</option>\n";
        }
        $html.="</datalist>\n";
        return $html;
    }
}
