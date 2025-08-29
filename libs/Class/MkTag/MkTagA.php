<?php
class MkTagA extends MkTag{
    protected const Tag='a';
    protected const UseCloseTag=true;

    protected $href='';
    protected $link_text='';
    public function __construct(string $link_text='',string $url='')
    {
        $this->link_text=$link_text;
        $this->href=$url;
    }
    public function get(){
        $raw_params=[];
        if($this->href!==''){ $raw_params[]='href="'.h($this->href).'"'; }
        if($this->title!==''){ $raw_params[]='title="'.h($this->title).'"'; }
        return $this->getDirect($raw_params,$this->raw_inner_text,$this->link_text);
    }
    /**
     * Href内容を更新
     */
    public function href(string $href){
        $this->href=$href;
        return $this;
    }
    /**
     * Href内容を追記
     */
    public function addHref(string $href){
        $this->href.=$href;
        return $this;
    }
    /**
     * リンクテキストの内容を追記
     */
    public function addLinkText(string $link_text){
        $this->link_text.=$link_text;
        return $this;
    }
    /**
     * リンクテキストの内容を更新
     */
    public function setLinkText(string $link_text){
        $this->link_text=$link_text;
        return $this;
    }
}
