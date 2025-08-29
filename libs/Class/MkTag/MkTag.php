<?php
class MkTag{
    protected const Tag='';
    protected const UseCloseTag=false;
    protected $tag=''; // タグメイン文字列
    protected $contents='';// タグの間のテキスト
    protected $use_close_tag=null; // trueなら終了タグをつける

    protected $name_suffix='';
    protected $name='';
    protected $value='';
    protected $title='';
    /**
     * 汎用 param_name="value" 形式パラメータ
     */
    protected $key_value_params=[];

    // param_name="[a,b,c]"になり個別に追加・削除したいもの
    protected $class_array=[];
    protected $style_array=[];
    // styleタグの内容を['display:none','color:red']のようにした状態
    protected $style_assoc=[];
    // styleタグの内容を['display'=>'none,'color'=>'red']のように連想配列にした状態

    protected $non_value_params=[]; // checkedなど名称だけのパラメータ
    protected $raw_inner_text='';// その他直接入力


    public function __construct()
    {
    }
    public function setTagString(string $input){ $this->tag=$input; }
    public function setUseCloseTag(bool $input){ $this->use_close_tag=$input; }

    public function __toString() { return $this->get(); }
    public function get(){
    }

    protected function getDirect(array $raw_params=[], string $raw_inner_text='',string $contents=''){
        // タグ文字と閉じるタグの有無を設定
        if($this->tag===''){ $this->tag=static::Tag; }
        if($this->use_close_tag===null){ $this->use_close_tag=static::UseCloseTag; }

        $tag_inner_text='';
        // インスタンスの汎用パラメータをセット
        if(count($this->key_value_params)>0){
            foreach($this->key_value_params as $key=>$value){
                $tag_inner_text.=" {$key}=\"".h($value).'"';
            }
        }
        $raw_params[]=$this->getClassParam();
        $raw_params[]=$this->getStyleParam();
        // 追加で入力されたパラメータをセット
        $raw_params=array_diff($raw_params,['']);
        if(count($raw_params)>0){
            $tag_inner_text .= ' '.implode(' ',$raw_params);
        }
        // 入力された生テキストをセット
        if($raw_inner_text!==''){ $tag_inner_text .=" ".$raw_inner_text; }
        $html ="<{$this->tag}{$tag_inner_text}>";
        $html.=h($contents);
        if($this->use_close_tag){
            $html.="</{$this->tag}>";
        }
        return $html;
    }
    /**
     * 汎用key=>value型パラメータの末尾に追記
     */
    public function addKV($param_name,$value){
        // 存在しない場合は新規登録
        if(!isset($this->key_value_params[$param_name])){
            return $this->setKV($param_name,$value);
        }
        $this->key_value_params[$param_name].=$value;
        return $this;
    }
    /**
     * 汎用key=>value型パラメータの登録・更新
     */
    public function setKV($param_name,$value){
        $this->key_value_params[$param_name]="{$param_name}=\"{$value}\"";
        return $this;
    }
    /**
     * 汎用key=>value型パラメータのから指定したものを除去する
     */
    public function removeKV($param_name){
        if(array_key_exists($param_name,$this->key_value_params)){
            unset($this->key_value_params[$param_name]);
        }
        return $this;
    }
    /**
     * class名を追加
     */
    public function addClass(string $class){
        if($class!==''){
            $this->class_array[]=$class;
        }
        return $this;
    }
    /**
     * class名をリセット・置き換え
     */
    public function class(string $class=''){
        // classを空にする
        $this->class_array=[];
        return $this->addClass($class);
    }
    /**
     * class名を除去
     */
    public function removeClass(string $class){
        $this->class_array=array_diff($this->class_array,[$class]);
        return $this;
    }
    /**
     * Classパラメータを取得
     */
    protected function getClassParam(){
        if(count($this->class_array)>0){
            $param=implode(" ",$this->class_array);
            return "class=\"$param\"";
        }
        return "";
    }
    /**
     * スタイルをプロパティ名と値で設定（既にあれば置き換え、値なしは除去）
     */
    public function setStyle(string $property_name='', $value=''){
        if($value===''){
            unset($this->style_array[$property_name]);
            return $this;
        }
        $this->style_array[$property_name]=$value;
        return $this;
    }
    /**
     * スタイルを[プロパティ=>値]形式の配列で一括設定
     */
    public function setStyles(array $styles){
        foreach($styles as $key=>$value){ $this->setStyle($key,$value);}
        return $this;
    }
    /**
     * スタイル連想からスタイル宣言の配列を得る
     */
    protected function getStyleDeclarations():array|false {
        if(count($this->style_array)==0){ return false; }
        $declarations=[];
        foreach($this->style_array as $key=>$value){
            if(is_numeric($key)){
                $declarations[]=$value;
                continue;
            }
            $declarations[]=$key.":".$value;
        }
        return $declarations;
    }
    /**
     * Styleパラメータを取得
     */
    protected function getStyleParam(){
        if(($declarations=$this->getStyleDeclarations())===false){
            return "";
        }
        return 'style="'.implode(";",$declarations).';"';
    }
    public function name(string $name){
        $this->name=$name;
        return $this;
    }
    /**
     * Replace Name Suffix
     */
    public function name_s(string $input=''){
        $this->name_suffix=$input;
        return $this;
    }
    public function value(string $value){
        $this->value=$value; return $this;
    }
    public function title(string $title){ $this->title=$title; return $this;}

    public function contents(string $contents){
        $this->contents=$contents; return $this;
    }
    public function print(){
        print $this->get();
    }
}
