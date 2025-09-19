<?php
trait TableRowValidate{
    /**
     * @var bool $hasErrors validateの結果、エラーがあるかどうか
     * @var array $errorMessages validate時にエラー内容を登録する
     */
    public bool $hasErrors=false;
    public array $errorMessages=[];
    /**
     * 検証処理
     */
    abstract public function validate():bool;
    public function addErrorMessage(string $message){
        $this->errorMessages[]=$message;
        $this->hasErrors=true;
    }
    /**
     * 文字数チェック（エラー有無判定をテーブルクラスに持つクラス用の暫定）
     */
    protected function validateRequired($target_value,string $name){
        if(strval($target_value)===''){
            $this->addErrorMessage("{$name}が入力されていません。");
        }
        return;
    }
    /**
     * 文字数チェック（エラー有無判定をテーブルクラスに持つクラス用の暫定）
     */
    protected function validateStrLength(string|null $target_value,string $name,int $max_length){
        if($target_value===null){
            return;
        }
        if(mb_strlen($target_value)>$max_length){
            $this->addErrorMessage("{$name}は{$max_length}文字以内で設定してください。");
        }
        return;
    }
    /**
     * 整数の範囲チェック
     */
    protected function varidateInt($target_value, string $name, int|null $min = null, int|null $max=null){
        $value=(int)$target_value;
        if($min!==null){
            if($value<$min){
                $this->addErrorMessage("{$name}は{$min}以上を指定してください。");
                return;
            }
        }else if($value<-2147483647){
            $this->addErrorMessage("{$name}は-2147483647以上を指定してください。");
            return;
        }
        if($max!==null){
            if($value>$max){
                $this->addErrorMessage("{$name}は{$max}以下を指定してください。");
                return;
            }
        }else if($value>2147483647){
            $this->addErrorMessage("{$name}は2147483647以下を指定してください。");
            return;
        }
        return;
    }
}
