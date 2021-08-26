<?php


namespace iry\e\service;

/*
if(class_exists('\iry\cli\Cli')) {
    class Cli extends \iry\cli\Cli{}
}else{
    class Cli{
        static function stdin($msg='',$validator=false,$processor = false,$limitLen=100000){
            echo $msg.':';
            $stdin=fopen('php://stdin','r');
            $content=trim(fgets($stdin,$limitLen));
            fclose($stdin);

            if(is_callable($processor)){
                $content = call_user_func($processor,$content);
            }

            if(is_callable($validator) && !call_user_func($validator,$content) ){
                echo "[error] \n";
                $content = self::stdin($msg, $validator,$processor,$limitLen);
            }
            return $content;
        }
        static function select($arr,$colQty=1,$msg='请选择 / Please Select',$mul=false){
            echo "\n\n----------------------------\n";
            foreach ($arr as $k=>$v){
                echo $k.'. '.$v."\r\n";
            }
            echo "----------------------------\n\n";
            return self::stdin($msg,function($v)use ($arr){return isset($arr[$v]);},'intval');
        }
    }
}
*/

class Cli{
    static private $_hasIryCli=null;
    static function _hasIryCli(){
        if (is_null(self::$_hasIryCli)) {
            self::$_hasIryCli = class_exists('\iry\cli\Cli')?true:false;
        }
        return self::$_hasIryCli;
    }

    static function stdin($msg='',$validator=false,$processor = 'trim'){
        $limitLen = 10000000;
        if(self::_hasIryCli()){
            return \iry\cli\Cli::stdin($msg,$validator,$processor,$limitLen);
        }else {
            echo $msg . ':';
            $stdin = fopen('php://stdin', 'r');
            $content = trim(fgets($stdin, $limitLen));
            fclose($stdin);

            if (is_callable($processor)) {
                $content = call_user_func($processor, $content);
            }

            if (is_callable($validator) && !call_user_func($validator, $content)) {
                echo "[error] \n";
                $content = self::stdin($msg, $validator, $processor);
            }
            return $content;
        }
    }

    static function select($arr,$msg='请选择 / Please Select'){
        if(self::_hasIryCli()){
            return \iry\cli\Cli::select($arr,1,$msg,false);
        }else {
            echo "\n\n----------------------------\n";
            foreach ($arr as $k => $v) {
                echo $k . '. ' . $v . "\r\n";
            }
            echo "----------------------------\n\n";
            return self::stdin($msg, function($v) use($arr) {return isset($arr[$v]);});
        }
    }
}