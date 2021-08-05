<?php


namespace iry\e;

class App
{
    /**
     * @var array [
     *      temp_path=>'临时目录 需可写'  默认系统临时目录
     *      store_driver=>'存储器的驱动Class ./drivers/Driver.php'
     *      subscribers=> callback | fiels:绝对路径/*
     *      event=>'事件配置文件'
     * ]
     */
    static private  $_cfgCls='';
    static private $_cfgObj;
    static public function setCfg($cls){
        self::$_cfgCls = $cls;
    }

    /**
     * @return Config
     */
    static public function cfg(){
        if(!is_object(self::$_cfgObj)){
            $cfgCls = self::$_cfgCls;
            self::$_cfgObj = new $cfgCls();
        }
        return self::$_cfgObj;
    }

    static public function getTempPath($file=''){
        $path = self::cfg()->getTempPath();
        if(empty($path)) $path = sys_get_temp_dir();
        return $path.(empty($file)?'': (DIRECTORY_SEPARATOR.$file) );
    }

    /**
     * @param string $event
     * @param array $args
     * @param int $delay 异步延时
     * @param int $dependency 事件依赖
     * @return int
     */

    static public function fire($event,$args,$delay=3,$dependency=0){
        return Fire::event($event,$args,$delay,$dependency);
    }


}