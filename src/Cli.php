<?php


namespace iry\e;

if(class_exists('\iry\cli\Cli')) {
    class Cli extends \iry\cli\Cli
    {

    }
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