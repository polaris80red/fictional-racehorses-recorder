<?php
/**
 * HTMLタグのStyle属性(Attribute)を配列で管理する
 */
class MkTagAttrStyle extends MkTagAttr {
    protected array $styles=[];
    public function __construct(string|array $property, $value='') {
        $this->set($property, $value);
    }
    /**
     * プロパティをセットする
     * @param string|array $property プロパティ名または[プロパティ名=>値]の連想配列
     * @param $value プロパティの値
     */
    public function set(string|array $property, $value=''){
        // 配列なら一括登録処理
        if(is_array($property)){
            return $this->merge($property);
        }
        // 値がなく既存に存在する場合は除去する
        if($value==='' && isset($this->styles[$property])){
            unset($this->styles[$property]);
        }
        $this->styles[$property]=$value;
        return $this;
    }
    /**
     * 複数のプロパティをセットする
     * @param string|array $property プロパティ名または[プロパティ名=>値]の連想配列
     */
    public function merge(array $property){
        foreach($property as $name => $value){
            $this->set((string)$name,$value);
        }
        return $this;
    }
    /**
     * 指定した名前のプロパティを除去する
     * @param string|array $property プロパティ名またはプロパティ名のリスト
     */
    public function remove(string|array $property){
        $property=is_string($property)?$property:[$property];
        foreach($property as $name){
            if(isset($this->styles[$name])){
                unset($this->styles[$name]);
            }
        }
        return $this;
    }
    /**
     * スタイル連想からスタイル宣言の配列を得る
     */
    public function getStyleDeclarations():array {
        if(count($this->styles)==0){ return []; }
        $declarations=[];
        foreach($this->styles as $key=>$value){
            if(is_numeric($key)){
                $declarations[]=$value;
                continue;
            }
            $declarations[]=$key.":".$value;
        }
        return $declarations;
    }
    /**
     * スタイル属性の値を文字列で得る
     */
    public function toString(){
        if(($declarations=$this->getStyleDeclarations())===[]){
            return "";
        }
        return implode(";",$declarations).";";
    }
    public function __toString() { return $this->toString(); }
}
