<?php
class HorseSearch extends Search{
    protected const SESSION_PARENT_KEY='horse_search';
    public $keyword='';
    public $sire_name='';
    public $mare_name='';
    public $bms_name='';
    public $sire_id='';
    public $mare_id='';
    public $search_text='';
    public $birth_year='';
    public $world_id=0;
    
    public $limit=20;
    public $page_number=0;

    public $order='';
    public $horse_id_is_visibled=false;
    public $null_birth_year=false;

    public $current_page_results_count=null;

    public $executed=false;
    public $can_execute=false;
    public $stmt=null;

    public $executed_by_form=false;

    public const ORDER_BIRTH‗YEAR__ASC='birth_year__asc';
    public const ORDER_BIRTH‗YEAR__DESC='birth_year__desc';
    public const ORDER_ID__ASC='id__asc';

    public function __construct(){
    }
    public function setBySessionOrUrl(){
        $do_reset=filter_input(INPUT_GET,'reset',FILTER_VALIDATE_BOOLEAN);
        if($do_reset){
            $this->setSessionAndParam('keyword','');
            $this->setSessionAndParam('birth_year','');
            $this->setSessionAndParam('sire_name','');
            $this->setSessionAndParam('mare_name','');
            $this->setSessionAndParam('bms_name','');
            $this->setSessionAndParam('sire_id','');
            $this->setSessionAndParam('mare_id','');
            $this->setSessionAndParam('search_text','');
            $this->setSessionAndParam('order','');
            $this->setSessionAndParam('horse_id_is_visibled','');
            $this->setSessionAndParam('null_birth_year','');
            return;
        }
        $keyword=$this->getSessionOrGet('keyword');
        if($keyword!=''){ $keyword = trim($keyword); }
        $this->keyword=$keyword;

        $birth_year=$this->getSessionOrGet('birth_year');
        if($birth_year===''){
            $birth_year = '';
        }else{
            $birth_year = (int)$birth_year;
        }
        $this->birth_year=$birth_year;

        $sire_name=$this->getSessionOrGet('sire_name');
        if($sire_name!=''){ $sire_name = trim($sire_name); }
        $this->sire_name=$sire_name;

        $mare_name=$this->getSessionOrGet('mare_name');
        if($mare_name!=''){ $mare_name = trim($mare_name); }
        $this->mare_name=$mare_name;

        $bms_name=$this->getSessionOrGet('bms_name');
        if($bms_name!=''){ $bms_name = trim($bms_name); }
        $this->bms_name=$bms_name;

        $search_text=$this->getSessionOrGet('search_text');
        if($search_text!=''){ $search_text = trim($search_text); }
        $this->search_text=$search_text;

        $this->limit=$this->getSessionOrGet('limit');
        $this->page_number=(int)$this->getSessionOrGet('page');

        $this->order=strtolower((string)$this->getSessionOrGet('order'));
        
        $this->horse_id_is_visibled=$this->getSessionOrGet('horse_id_is_visibled');
        $this->null_birth_year=$this->getSessionOrGet('null_birth_year');

        $this->setToSessionByParamNameArray([
            'keyword','birth_year',
            'sire_name','mare_name','bms_name',
            'sire_id','mare_id','search_text',
            'limit','page','order','horse_id_is_visibled',
            'null_birth_year'
        ]);
    }
    public function setBySession(){
        $do_reset=filter_input(INPUT_GET,'reset',FILTER_VALIDATE_BOOLEAN);
        if($do_reset){
            $this->getBySession('keyword','');
            $this->setSessionAndParam('birth_year','');
            $this->setSessionAndParam('sire_name','');
            $this->setSessionAndParam('mare_name','');
            $this->setSessionAndParam('bms_name','');
            $this->setSessionAndParam('sire_id','');
            $this->setSessionAndParam('mare_id','');
            $this->setSessionAndParam('search_text','');
            $this->setSessionAndParam('order','');
            $this->setSessionAndParam('horse_id_is_visibled','');
            $this->setSessionAndParam('null_birth_year','');
            return;
        }
        $keyword=$this->getBySession('keyword');
        if($keyword!=''){
            $keyword = trim($keyword);
        }
        $this->keyword=$keyword;

        $birth_year=$this->getBySession('birth_year');
        if($birth_year===''){
            $birth_year = '';
        }else{
            $birth_year = (int)$birth_year;
        }
        $this->birth_year=$birth_year;

        $sire_name=$this->getBySession('sire_name');
        if($sire_name!=''){ $sire_name = trim($sire_name); }
        $this->sire_name=$sire_name;

        $mare_name=$this->getBySession('mare_name');
        if($mare_name!=''){ $mare_name = trim($mare_name); }
        $this->mare_name=$mare_name;

        $bms_name=$this->getBySession('bms_name');
        if($bms_name!=''){ $bms_name = trim($bms_name); }
        $this->bms_name=$bms_name;

        $search_text=$this->getBySession('search_text');
        if($search_text!=''){ $search_text = trim($search_text); }
        $this->search_text=$search_text;

        $this->limit=$this->getBySession('limit');
        $this->page_number=(int)$this->getBySession('page',0);

        $this->order=strtolower((string)$this->getBySession('order'));
        $this->horse_id_is_visibled=$this->getBySession('horse_id_is_visibled');
        $this->horse_id_is_visibled=$this->getBySession('null_birth_year');
    }
    public function setByUrl(){
        $do_reset=filter_input(INPUT_GET,'reset',FILTER_VALIDATE_BOOLEAN);
        if($do_reset){
            $this->setSessionAndParam('keyword','');
            $this->setSessionAndParam('birth_year','');
            $this->setSessionAndParam('sire_name','');
            $this->setSessionAndParam('mare_name','');
            $this->setSessionAndParam('bms_name','');
            $this->setSessionAndParam('sire_id','');
            $this->setSessionAndParam('mare_id','');
            $this->setSessionAndParam('search_text','');
            $this->setSessionAndParam('order','');
            $this->setSessionAndParam('horse_id_is_visibled','');
            $this->setSessionAndParam('null_birth_year','');
            return;
        }
        // フォームからの検索実行
        $this->executed_by_form=filter_input(INPUT_GET,'executed_by_form',FILTER_VALIDATE_BOOLEAN);

        $keyword=filter_input(INPUT_GET,'keyword');
        if($keyword!=''){
            $keyword = trim($keyword);
        }
        $this->keyword=$keyword;

        // まずは手入力欄を優先取得
        $birth_year=filter_input(INPUT_GET,'birth_year');
        if($birth_year===''||$birth_year===null){
            // 0 以外の空である場合、プルダウンの値で上書き
            $birth_year=filter_input(INPUT_GET,'birth_year_select');
        }
        // プルダウンからの値も空であれば空文字列、そうでなければ整数にする。
        if($birth_year===''||$birth_year===null){
            $birth_year = '';
        }else{
            $birth_year = (int)$birth_year;
        }
        $this->birth_year=$birth_year;
        $sire_name=filter_input(INPUT_GET,'sire_name');
        if($sire_name!=''){ $sire_name = trim($sire_name); }
        $this->sire_name=$sire_name;

        $mare_name=filter_input(INPUT_GET,'mare_name');
        if($mare_name!=''){ $mare_name = trim($mare_name); }
        $this->mare_name=$mare_name;

        $bms_name=filter_input(INPUT_GET,'bms_name');
        if($bms_name!=''){ $bms_name = trim($bms_name); }
        $this->bms_name=$bms_name;

        $sire_id=filter_input(INPUT_GET,'sire_id');
        if($sire_id!=''){ $sire_id = trim($sire_id); }
        $this->sire_id=$sire_id;

        $mare_id=filter_input(INPUT_GET,'mare_id');
        if($mare_id!=''){ $mare_id = trim($mare_id); }
        $this->mare_id=$mare_id;

        $search_text=filter_input(INPUT_GET,'search_text');
        if($search_text!=''){ $search_text = trim($search_text); }
        $this->search_text=$search_text;

        if(isset($_GET['limit'])){
            $this->limit=filter_input(INPUT_GET,'limit');
        }
        $this->page_number=(int)filter_input(INPUT_GET,'page');

        $this->order=strtolower((string)filter_input(INPUT_GET,'order'));
        $this->horse_id_is_visibled=filter_input(INPUT_GET,'horse_id_is_visibled');
        $this->null_birth_year=filter_input(INPUT_GET,'null_birth_year',FILTER_VALIDATE_BOOL);

        $this->setToSessionByParamNameArray([
            'keyword','birth_year',
            'sire_name','mare_name','bms_name','sire_id','mare_id','search_text',
            'limit','page','order','horse_id_is_visibled',
            'null_birth_year'
        ]);
    }
    public function getUrlParam(array $remove_param_name_array=[]){
        $param_name_array=[
            'keyword','birth_year',
            'sire_name','mare_name','bms_name','sire_id','mare_id','search_text',
            'limit','page','order','horse_id_is_visibled',
            'null_birth_year'
        ];
        return $this->getUrlParamByArray($param_name_array, $remove_param_name_array);
    }
    public function SelectExec(PDO $pdo){
        $sql_parts=[];
        $where_parts=[];
        $sql_parts[]='SELECT * FROM `'.Horse::TABLE.'` AS h';
        $pre_bind=new StatementBinder();
        if($this->search_text!=''){
            $sql_parts[]='LEFT JOIN `'.HorseTag::TABLE.'` AS t';
            $sql_parts[]='ON h.horse_id LIKE t.horse_id';

            $where_parts[]='t.tag_text LIKE :tag';
            $pre_bind->add(':tag', $this->search_text, PDO::PARAM_STR);
        }
        if($this->keyword!=''){
            $where_parts[]='(`name_ja` LIKE :name_ja OR `name_en` LIKE :name_en)';
            $pre_bind->add(':name_ja', "%{$this->keyword}%", PDO::PARAM_STR);
            $pre_bind->add(':name_en', "%{$this->keyword}%", PDO::PARAM_STR);
        }
        if($this->birth_year!==''){
            $where_parts[]='`birth_year` = :birth_year';
            $pre_bind->add(':birth_year', $this->birth_year, PDO::PARAM_INT);
        }else if($this->null_birth_year){
            $where_parts[]='`birth_year` IS NULL';
        }
        if($this->sire_id!=''){
            $where_parts[]='`sire_id` LIKE :sire_id';
            $pre_bind->add(':sire_id', "{$this->sire_id}", PDO::PARAM_STR);
        }
        if($this->sire_name!=''){
            $where_parts[]='`sire_name` LIKE :sire_name';
            $pre_bind->add(':sire_name', "%{$this->sire_name}%", PDO::PARAM_STR);
        }
        if($this->mare_id!=''){
            $where_parts[]='`mare_id` LIKE :mare_id';
            $pre_bind->add(':mare_id', "{$this->mare_id}", PDO::PARAM_STR);
        }
        if($this->mare_name!=''){
            $where_parts[]='`mare_name` LIKE :mare_name';
            $pre_bind->add(':mare_name', "%{$this->mare_name}%", PDO::PARAM_STR);
        }
        if($this->bms_name!=''){
            $where_parts[]='`bms_name` LIKE :bms_name';
            $pre_bind->add(':bms_name', "%{$this->bms_name}%", PDO::PARAM_STR);
        }
        if(count($where_parts)>0){
            // 手動検索条件がある場合のみ固定の条件を追加して実行
            if($this->world_id>0){
                $where_parts[]='`world_id` LIKE :world_id';
                $pre_bind->add(':world_id', $this->world_id, PDO::PARAM_INT);
            }
            $where_parts[]='h.is_enabled = 1';
            $sql_parts[]="WHERE ".implode(' AND ',$where_parts);
        }else{
            return false;
        }

        $order_parts=[];
        switch($this->order){
            case self::ORDER_BIRTH‗YEAR__ASC:
                $order_parts[]="`birth_year` ASC";
                break;
            case self::ORDER_BIRTH‗YEAR__DESC:
                $order_parts[]="`birth_year` DESC";
                break;
            case self::ORDER_ID__ASC:
                $order_parts[]="`horse_id` ASC";
                break;
            default:
                break;
        }
        $order_parts[]="`name_ja` ASC";
        $order_parts[]="`name_en` ASC";
        if(count($order_parts)>0){
            $sql_parts[]="ORDER BY ".implode(', ',$order_parts);
        }
        if($this->limit>0){
            $sql_parts[]="LIMIT {$this->limit} OFFSET ".($this->limit*$this->page_number);
        }

        $sql=implode(' ',$sql_parts);
        $stmt = $pdo->prepare($sql);

        $pre_bind->bindTo($stmt);

        $stmt->execute();
        $this->stmt=$stmt;
        $this->executed=true;
        return true;
    }
    /**
     * 検索条件を文章で取得
     */
    public function getSearchParamStr(){
        $params=[];
        if($this->keyword){
            $params[]="馬名[".$this->keyword."]";
        }
        if($this->birth_year!==''){
            $params[]="生年[".$this->birth_year."]";
        }
        if($this->sire_name){
            $params[]="父名[".$this->sire_name."]";
        }
        if($this->mare_name){
            $params[]="母名[".$this->mare_name."]";
        }
        if($this->bms_name){
            $params[]="母父名[".$this->bms_name."]";
        }
        if($this->sire_id){
            $params[]="父ID[".$this->sire_id."]";
        }
        if($this->mare_id){
            $params[]="母ID[".$this->mare_id."]";
        }
        if($this->search_text){
            $params[]="検索タグ[".$this->search_text."]";
        }
        return implode(', ',$params);
    }
    /**
     * 簡易検索フォーム
     */
    public static function printSimpleForm($page){
        ?><form action="<?php echo $page->to_app_root_path."horse/search/";?>" method="get">
        <input type="text" name="keyword" style="width:150px;height:1.5em;" value="" placeholder="馬名で検索">
        <input type="submit" value="検索">
        <input type="hidden" name="executed_by_form" value="1">
        </form><?php
    }
    /**
     * 検索フォームをHTML出力
     */
    public function printForm($page, $setting){
        ?>
<form action="<?php echo $page->to_app_root_path."horse/search/";?>" method="get">
<fieldset>
<table class="horse_search">
    <tr><th>馬名</th>
        <td><input type="text" name="keyword" style="width:200px;height:1.5em;" value="<?php print $this->keyword; ?>" placeholder="馬名"></td>
        <td><input type="button" value="クリア" onclick="clearElmVal('*[name=keyword]');">　<input type="submit" value="検索実行"></td>
    </tr>
    <tr><th><?php
            if($setting->birth_year_mode==1||$setting->birth_year_mode==2){
                if($setting->year_view_mode===3){
                    echo "期";
                }else{
                    echo "世代";
                }
            }else{
                echo "生年";
            }
    ?></th>
        <td>
            <select name="birth_year_select" style="width:6em;" onchange="clearElmVal('*[name=birth_year]');">
            <?php
                $year_min=$setting->select_zero_year-$setting->year_select_min_diff-3;
                $year_max=$setting->select_zero_year+$setting->year_select_max_diff;
                echo '<option value=""></option>'."\n";
                $year_option_exists=false;
                for($i=$year_min; $i<=$year_max; $i++){
                    if($i==$this->birth_year){ $year_option_exists=true; }
                    echo '<option value="'.$i,'"'.(($i==$this->birth_year)?' selected ':'').'>';
                    echo $setting->getBirthYearFormat($i);
                    if($setting->birth_year_mode==1||$setting->birth_year_mode==2){
                        if($setting->year_view_mode===3){
                            echo "期";
                        }else{
                            echo "世代";
                        }
                    }else{
                        if($setting->year_view_mode==0){ echo "年"; }
                        if($setting->year_view_mode==2){ echo "年"; }
                    }
                    echo '</option>'."\n";
                }
            ?></select>
            ／ <input type="number" name="birth_year" style="width:4em;" value="<?php print $year_option_exists?'':$this->birth_year; ?>" placeholder="生年" onchange="clearElmVal('*[name=birth_year_select]');">
        </td>
        <td><input type="button" value="クリア" onclick="clearElmVal('*[name=birth_year]');clearElmVal('*[name=birth_year_select]');"></td>
    </tr>
    <tr><th>父名</th>
        <td><input type="text" name="sire_name" value="<?php print $this->sire_name; ?>" placeholder="父名" onchange="clearElmVal('*[name=sire_id]');"></td>
        <td><input type="button" value="クリア" onclick="clearElmVal('*[name=sire_name]');"></td>
    </tr>
    <tr><th>母名</th>
        <td><input type="text" name="mare_name" value="<?php print $this->mare_name; ?>" placeholder="母名" onchange="clearElmVal('*[name=mare_id]');"></td>
        <td><input type="button" value="クリア" onclick="clearElmVal('*[name=mare_name]');"></td>
    </tr>
    <tr><th>母父名</th>
        <td><input type="text" name="bms_name" value="<?php print $this->bms_name; ?>" placeholder="母父名" onchange="clearElmVal('*[name=mare_id]');"></td>
        <td><input type="button" value="クリア" onclick="clearElmVal('*[name=bms_name]');"></td>
    </tr>
    <tr><td colspan="3" style="height: 6px;"></td></tr>
    <tr><th>父ID</th>
        <td><input type="text" name="sire_id" value="<?php print $this->sire_id; ?>" placeholder="父の競走馬ID(完全一致)" onchange="clearElmVal('*[name=sire_name]');"></td>
        <td><input type="button" value="クリア" onclick="clearElmVal('*[name=sire_id]');"></td>
    </tr>
    <tr><th>母ID</th>
        <td><input type="text" name="mare_id" value="<?php print $this->mare_id; ?>" placeholder="母の競走馬ID(完全一致)" onchange="clearElmVal('*[name=mare_name]');clearElmVal('*[name=bms_name]');"></td>
        <td><input type="button" value="クリア" onclick="clearElmVal('*[name=mare_id]');"></td>
    </tr>
    <tr><td colspan="3" style="height: 6px;"></td></tr>
    <tr><th>検索タグ</th>
        <td><input type="text" name="search_text" value="<?php print $this->search_text; ?>" placeholder="検索タグ"></td>
        <td><input type="button" value="クリア" onclick="clearElmVal('*[name=search_text]');"></td>
    </tr>
    <tr>
        <td colspan="3">
            <label><input type="checkbox" name="horse_id_is_visibled" value="1"<?php print $this->horse_id_is_visibled?" checked":""; ?>>競走馬ID列の表示</label>
            <label><input type="checkbox" name="null_birth_year" value="1"<?php print $this->null_birth_year?" checked":""; ?>>生年仮登録馬を生年検索</label>
        </td>
    </tr>
</table>
<hr>
1ページあたり：<select name="limit">
<?php
foreach([10=>10, 20=>20, 50=>50, 0=>'無制限'] as $key=>$val){
    $selected=($this->limit==$key?' selected':'');
    echo "<option value=\"{$key}\"{$selected}>{$val}</option>\n";
}
?>
</select>件
<input type="hidden" name="page" value="0">
<input type="hidden" name="executed_by_form" value="1"><!-- フォームからの検索 -->
</fieldset>
</form><?php
    }
    /**
     * ページネーションのHTMLを出力
     */
    public function printPagination(){
        if($this->limit==0){return false;}
        $prev = (int)$this->page_number - 1;
        $next = 1+(int)$this->page_number;
        ?>
<?php if($this->page_number>0): ?>
<a href="?page=0&<?php echo $this->getUrlParam(['page']); ?>">[先頭]</a>
<a href="?page=<?php echo $prev; ?>&<?php echo $this->getUrlParam(['page']); ?>">[前のページ]</a>
<?php else: ?>
[先頭] [前のページ]
<?php endif; ?>
<a> [<?php echo str_pad($this->page_number+1,2,'0',STR_PAD_LEFT); ?>]</a>
<?php if(
    !is_null($this->current_page_results_count)
    &&(
        $this->current_page_results_count < $this->limit
        || $this->current_page_results_count===0
        )): ?>
[次のページ]
<?php else: ?>
<a href="?&page=<?php echo $next; ?>&<?php echo $this->getUrlParam(['page']); ?>">[次のページ]</a>
<?php endif; ?>
<?php
    }
}
