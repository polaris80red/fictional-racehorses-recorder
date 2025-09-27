<?php
class Page{
    public $title='';
    public $is_editable = false;
    private $noindex=false;

    public $common_msgs=[];
    public $error_msgs=[];
    public $debug_msgs=[];
    public $debug_dump_var=[];
    public $error_exists=false;

    public $error_return_url='';
    public $error_return_link_text='';

    public $to_app_root_path='./';

    public $to_race_list_path='race/list/';
    public $to_horse_search_path='horse/search/';

    public $race; // レース1件に対するページの場合用
    public $horse; // 競走馬1頭に対するページの場合用
    public $has_edit_menu = false; // ページ内リンクを付ける下部編集メニューの有無 
    private Theme $theme;

    protected $setting = null;
    public function __construct(int $hierarchy_number_from_app_root=0){
        $this->setHierarchyNumberFromAppRoot($hierarchy_number_from_app_root);
    }
    public function setSetting(Setting $setting){
        $this->setting=$setting;
        if($setting->theme_dir_name!=''){
            $this->theme=(new ThemeLoader(APP_ROOT_DIR.'/themes'))->loadTheme($setting->theme_dir_name);
            // テーマディレクトリのテンプレートを読み込み元に指定
            TemplateImporter::setThemeDir(APP_ROOT_DIR."/themes/{$setting->theme_dir_name}/templates");
        }
    }
    /**
     * WEBアプリルートディレクトリからの階層数を指定
     * @param int $num 階層数
     * @return string
     */
    public function setHierarchyNumberFromAppRoot(int $num = 0){
        if($num===0){
            $this->to_app_root_path="./";
        } else {
            $this->to_app_root_path="";
            for($i=0; $i<$num; $i++){
                $this->to_app_root_path.="../";
            }
        }
        $this->to_race_list_path=$this->to_app_root_path.$this->to_race_list_path;
        $this->to_horse_search_path=$this->to_app_root_path.$this->to_horse_search_path;

        return $this->to_app_root_path;
    }
    /**
     * @param string $message
     */
    public function addErrorMsg(string $message){
        $this->error_exists=true;
        $this->error_msgs[]=$message;
    }
    /**
     * @param array $messages
     */
    public function addErrorMsgArray(array $messages){
        $this->error_exists=true;
        $this->error_msgs=array_merge($this->error_msgs,$messages);
    }
    /**
     * エラーページに表示する移動用リンクを設定する
     */
    public function setErrorReturnLink(string $linsText, string $href){
        $this->error_return_link_text=$linsText;
        $this->error_return_url=$href;
        return $this;
    }
    /**
     * 相対パスをprintする
     * @param string $suffix パスの後に追加する文字列
     */
    public function printAppRootPath(string $suffix=''){
        print $this->to_app_root_path.$suffix;
        return $this;
    }
    /**
     * 基本的なスタイルシートLinkのセットをprint
     */
    public function printBaseStylesheetLinks(){
        $this->printStylesheetLink('style/main.css');
        if(isset($this->theme)){
            $css_files=$this->theme->getCssFiles();
            foreach($css_files as $file_path){
                $this->printStylesheetLink("themes/".$this->theme->getName()."/$file_path");
            }
        }else{
            $this->printStylesheetLink('style/color.css');
        }
        $this->printStylesheetLink('user/style.css');
        return $this;
    }
    /**
     * パスにアプリルートのパスを付与してスタイルシートのlinkを表示
     */
    public function printStylesheetLink(string $path){
        $this->printStylesheetLinkRaw($this->to_app_root_path.$path);
        return $this;
    }
    /**
     * 完全なパスでスタイルシートのlinkを表示
     */
    public function printStylesheetLinkRaw(string $href){
        print "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$href}\">\n";
        return $this;
    }
    public function printScriptLink(string $path){
        $this->printScriptLinkRaw($this->to_app_root_path.$path);
        return $this;
    }
    public function printScriptLinkRaw(string $src){
        print "<script src=\"{$src}\"></script>\n";
        return $this;
    }
    protected function printVendorStylesheetLink(string $path){
        $this->printStylesheetLink('vendor/'.$path);
        return $this;
    }
    protected function printVendorScriptLink(string $path){
        $this->printScriptLink('vendor/'.$path);
        return $this;
    }
    public function printJqueryResource(){
        if(USE_LOCAL_JQUERY_FILE){
            $this->printVendorStylesheetLink(VENDOR_Jquery_UI_DIR.'/jquery-ui.min.css');
            $this->printVendorScriptLink(VENDOR_Jquery_FILE);   
            $this->printVendorScriptLink(VENDOR_Jquery_UI_DIR.'/jquery-ui.min.js');
        }else{
            print CDN_Jquery_UI_CSS_TAG."\n";
            print CDN_Jquery_TAG."\n";
            print CDN_Jquery_UI_JS_TAG."\n";
        }
        $this->printVendorScriptLink(VENDOR_Jquery_DatePicker_FILE);
        return $this;
    }
    /**
     * ページタイトルをprintする
     */
    public function printTitle(){
        print $this->title;
        return $this;
    }
    /**
     * ページタイトルをprintする
     */
    public function printSiteName(string $prefix='', string $suffix=''){
        print $prefix.SITE_NAME.$suffix;
        return $this;
    }
    /**
     * 設定と関係なくnoindexに設定する
     */
    public function ForceNoindex(){
        $this->noindex=true;
        return $this;
    }
    /**
     * noindex,nofollowメタタグを使用する設定ならタグ文字列を取得
     */
    public function getMetaNoindex(){
        if($this->noindex||FORCE_NOINDEX){
            return '<meta name="robots" content="noindex, nofollow">';
        }
        return '';
    }
    /**
     * デバッグ用データに格納した内容をdumpする
     */
    public function printDebugDumpDiv(){
        if(count($this->debug_dump_var)>0){
            echo "<div style=\"border:solid 1px red;padding: 1em;\">";
            echo "<div>dump</div>";
            foreach($this->debug_dump_var as $var){
                echo "<pre style=\"border:solid 1px red;\">";
                var_dump($var);
                echo "</pre>";
            }
            echo "</div>";
        }
    }
    public function printHeaderNavigation(){
        $pref=$this->to_app_root_path;
?><div class="header_navigation"><div style="width:70%;float:left;">
<?php if(SHOW_PARENT_SITE_LINK): ?>
<a href="<?=PARENT_SITE_URL?>"><?=PARENT_SITE_LINK_TEXT?></a>
<?php endif; ?>
<a href="<?=$pref?>">[HOME]</a>
<span class="nowrap">
<a href="<?=$this->to_horse_search_path;?>">[競走馬検索]</a>
<a href="<?=$this->to_race_list_path;?>?set_by_session=false&session_is_not_update=1">[レース検索]</a>
</span>
<?php
$race_history_count=(new RaceAccessHistory())->count();
if( !empty($_SESSION[APP_INSTANCE_KEY][Session::PUBLIC_PARAM_KEY]['latest_horse']['id'])||$race_history_count>0): ?>
<span class="nowrap">（ 最新：
<?php if(!empty($_SESSION[APP_INSTANCE_KEY][Session::PUBLIC_PARAM_KEY]['latest_horse']['id'])):
$url=$pref.'horse/?horse_id='.$_SESSION[APP_INSTANCE_KEY][Session::PUBLIC_PARAM_KEY]['latest_horse']['id']; ?>
<a href="<?=$url?>">[馬]</a>
<?php endif; ?>
<?php if($race_history_count>0):
$url=$this->to_app_root_path."race/list/access_history.php";
?>
<a href="<?=$url?>">[レース履歴]</a>
<?php endif; ?>）</span>
<?php endif; ?>
</div><!-- /float_left -->
<div style="width:20%;float:right;text-align:right;">
<?php if(SHOW_DISPLAY_SETTINGS_FOR_GUESTS||Session::isLoggedIn()): ?>
    <a href="<?=$pref?>setting/">[設定]</a>
<?php endif; ?>
<?php if(SHOW_LOGIN_LINK): ?>
    <?php if(!Session::isLoggedIn()): ?>
    <a href="<?=$pref?>sign-in/" class="nowrap">[ログイン]</a>
    <?php else: ?>
    <a href="<?=$pref?>sign-out.php" class="nowrap">[ログアウト]</a>
    <?php endif; ?>
<?php endif; ?>
</div><!-- /float_right -->
</div>
<div style="clear:both"></div>
<?php
    }
    public function getHorsePageUrl($horse_id){
        return $this->to_app_root_path."horse/?horse_id=$horse_id";
    }
    public function getRaceResultUrl($race_id){
        return $this->to_app_root_path.'race/result/?race_id='.$race_id;
    }
    public function getRaceYearSearchUrl($year){
        $url_params=[
            'search_word'=>'',
            'year'=>$year,
            'distance'=>'',
            'search_detail_tgl_status'=>'open',
            'session_is_not_update'=>1,
        ];
        $url =$this->to_race_list_path.'?'.http_build_query($url_params);
        return $url;
    }
    public function getRaceNameSearchUrl($race_name){
        $race_name=rtrim($race_name,'*');
        $url_params=[
            'search_word'=>$race_name,
            'year'=>'',
            'grade_reset'=>1,
            'age_reset'=>1,
            'distance'=>'',
            'search_detail_tgl_status'=>'close',
            'session_is_not_update'=>1,
        ];
        $url =$this->to_race_list_path.'?'.http_build_query($url_params);
        return $url;
    }
    public function getDateRaceListUrl($date,$optional_params_array=[]){
        $date_str='';
        if(is_string($date)){
            $date_str=$date;
        }else if(is_object($date) && $date instanceof DateTime){
            $date_str=$date->format('Y-m-d');
        }
        $params=['date'=>$date_str];
        if(count($optional_params_array)>0){
            $params=array_merge($params,$optional_params_array);
        }
        $url =$this->to_app_root_path.'race/list/in_date.php?'.http_build_query($params);
        return $url;
    }
    public function getTurnRaceListUrl($year,$month,$turn='',$optional_params_array=[]){
        $url = $this->to_app_root_path."race/list/in_week.php";
        $url_params=new UrlParams(['year'=>$year,'month'=>$month]);
        if($turn!=''){
            $url_params->set('turn',$turn);
        }
        $url.="?".$url_params->toString($optional_params_array);
        return $url;
    }
    public function printFooterHomeLink($is_enabled=true){
        echo "<div class=\"footer_navigation\">";
        if($is_enabled){
            echo '<a href="'.$this->to_app_root_path.'">[HOME]</a>';
            if(Session::isLoggedIn() && Session::currentUser()->canManageMaster()){
            echo ' <a href="'.$this->to_app_root_path.'admin/">[管理画面]</a>';
            }
            if(Session::isLoggedIn() && !Session::currentUser()->isSuperAdmin()){
                $tag=new MkTagA('[ユーザー情報設定]');
                $tag->href($this->to_app_root_path.'account/edit.php');
                echo ' '.$tag;
            }
        }else{
            echo '<a>[HOME]</a>';
        }
        echo "</div>";
    }
    public function exitToHome(){
        header("Location: {$this->to_app_root_path}");
        exit;
    }
    /**
     * 簡易エラーページを出力する
     */
    public function printCommonErrorPage(){
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <title><?php echo $this->title; ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="ja">
    <?=$this->ForceNoindex()->getMetaNoindex();?>
    <?php $this->printBaseStylesheetLinks(); ?>
<style>
</style>
</head>
<body>
<header>
<?php $this->printHeaderNavigation(); ?>
<h1 class="page_title"><?php echo $this->title; ?></h1>
</header>
<main>
<hr class="no-css-fallback">
<h2>エラー</h2>
<?php if($this->error_return_url): ?>
<p><a href="<?php echo $this->error_return_url; ?>"><?php echo ($this->error_return_link_text?:$this->error_return_url) ?></a></p>
<?php endif; ?>
<div style="border:solid 1px red;padding:1.0em;">
<?php
        if(count($this->error_msgs)>0){
            echo nl2br(h(implode("\n",$this->error_msgs)));
        }
?>
</div>
<?php
        if(count($this->debug_msgs)>0){
            echo "<div style=\"border:solid 1px red;padding: 1em;\"><pre>";
            echo h(implode("\n\n",$this->debug_msgs));
            echo "</pre></div>";
        }
        $this->printDebugDumpDiv();
?>
<hr class="no-css-fallback">
</main>
<footer>
<?php $this->printFooterHomeLink(); ?>
</footer>
</body>
</html>
<?php
        return;
    }
}