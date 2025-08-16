<?php
if(function_exists('h')){
    echo "エラー：h関数が定義済\n";
    exit;
}else{
    function h($input){
        return htmlspecialchars((string)$input,ENT_QUOTES,'UTF-8');
    }
}
function is_remote_access(){
    $remote_addr = $_SERVER['REMOTE_ADDR'] ?? '';
    $local_hosts = ['127.0.0.1', '::1', 'localhost'];
    if(in_array($remote_addr,$local_hosts,true)){
        // localhostに一致すればリモートアクセスでない
        return false;
    }
    return true;
}

function print_h($input){
    print htmlspecialchars((string)$input,ENT_QUOTES,'UTF-8');
}
function exists_htmlspecialchars($input,$flags=ENT_QUOTES,$encoding='UTF-8'){
    if($input===htmlspecialchars($input,$flags,$encoding)){
        return false;
    }
    return true;
}
/**
 * キー名配列を使って配列から除去
 */
function array_diff_key_by_name(array $array, array $remove_keys=[]){
    if(count($remove_keys)>0){
        $remove_tmp_array=array_fill_keys($remove_keys, false);
        $array=array_diff_key($array,$remove_tmp_array);
    }
    return $array;
}
function redirect_exit(string $url){
    header("Location: {$url}");
    exit;
}
/**
 * $inputが空文字列はnullに変換、整数ならそのまま
 */
function intOrNull($input){
    $str=(string)$input;
    if($str===''){ return null; }
    return (int)$str;
}
/**
 * $inputが0や空文字列ならnullに変換、0以外の整数はそのまま
 */
function intOrNullIfZero($input){
    $int=(int)$input;
    return $int===0 ? null : $int;

}
function getBackRelativePath(int $num){
    if($num===0){ return ""; }
    $ret_str="";
    for($i=0; $i<$num; $i++){
        $ret_str.="../";
    }
    return $ret_str;
}
function printHiddenAndText($name,$value=''){
    echo "<input type=\"hidden\" name=\"$name\" value=\"$value\">$value";
}
function printInputTagByArray(array $params){
    printInputTag(
        $params['name'],$params['type'],$params['value'],$params['raw']
    );
}
function printInputTag($name,$type,$value='',$raw=''){
    echo "<input type=\"$type\" name=\"$name\" value=\"$value\" $raw>";
}
/**
 * 配列からSelectのOptionタグをprint
 */
function printSelectOptions(array $data, $empty_is_enabled=true, $selected='', $option_prefix='', $option_suffix=''){
    if($empty_is_enabled){
        echo "<option value=\"\"></option>\n";
    }
    foreach($data as $val){
        $s=(($val==$selected)?' selected':'');
        echo "<option value=\"{$val}\"$s>{$option_prefix}{$val}{$option_suffix}</option>\n";
    }
}
/**
 * INSERT SQL生成
 * @param string $table テーブル名
 * @param array $insert_columns カラム名配列
 */
function getInsertSql(string $table, array $insert_columns){
    $columns=[];
    $values=[];
    foreach($insert_columns as $column){
        $columns[]="`{$column}`";
        $values[]=":{$column}";
    }
    $columns_str = implode(",",$columns);
    $values_str = implode(",",$values);
    return "INSERT INTO `{$table}` ( {$columns_str} ) VALUES ( {$values_str} );";
}
/**
 * SELECT SQL 生成
 * @return string
 */
function getSelectSqlWhereRaw(string $table, string $where_text='`is_enabled`=1'){
    return $sql="SELECT * FROM `{$table}` WHERE $where_text";
}
/**
 * UPDATE SQL 生成
 * @param string $table テーブル名
 * @param array $update_columns SETするカラム
 * @param string $where_text WHERE条件
 */
function getUpdateSqlWhereRaw(string $table, array $update_columns, string $where_text){
    $columns=[];
    foreach($update_columns as $column){
        $columns_parts[]="`{$column}`=:{$column}";
    }
    $columns_part_str = implode(",\n",$columns_parts);
    return "UPDATE `{$table}` SET \n{$columns_part_str} \nWHERE $where_text;";
}
function getUpdateSqlWhereOneColumn(string $table, $update_columns, $key_column){
    $where_text="WHERE `{$key_column}`=:{$key_column}";
    return getUpdateSqlWhereRaw($table, $update_columns, $where_text);
}
function InsertByArray(PDO $pdo, $table, array $params){
    $columns=[];
    $values=[];
    foreach($params as $key=>$val){
        $columns[]="`{$key}";
        $values[]=":{$key}";
    }
    $columns_str = implode(", ",$columns);
    $values_str = implode(", ",$values);
    $sql = "INSERT INTO `{$table}` ( {$columns_str} ) VALUES ( {$values_str} );";
    $stmt = $pdo->prepare($sql);
    foreach($params as $key=>$val){
        if(is_array($key)){

        }else{
            $stmt->bindValue(":{$key}",$val,PDO::PARAM_STR);
        }
    }
    $result = $stmt->execute();
}
