<?php


namespace ir\e;

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
        $r =  self::cfg()->getTempPath().(empty($file)?'': (DIRECTORY_SEPARATOR.$file) );
        return empty($r)?sys_get_temp_dir():$r;
    }

    /**
     * @param string $event
     * @param array $args
     * @param int $delay 异步延时
     * @param int $dependency 事件依赖
     * @return int
     */

    static public function fire($event,$args,$delay=3,$dependency=0){
        $checkRes = true;//$this->_checkArgs($args);
        if($checkRes) {
            //在不改变原代码结构的情况下，注入自己的代码
            $cls = self::cfg()->getEventRules();
            $eventInfo  = method_exists($cls,$event)?$cls::$event():[];
            if(isset($eventInfo['exec']) && count($eventInfo['exec'])>0){
                foreach ($eventInfo['exec'] as $cls){
                    $_tmp = new $cls();
                    if(method_exists($_tmp,'exec')){
                        $_tmp->exec();
                    }
                }
            }

            if ($event) {
                $data = [
                    'name' => $event,
                    'dependency' => $dependency,
                    'args' => $args
                ];
                //$data['args'] = Pool::dataEncode($args);
                return Pool::add($data,time() + $delay);
            }
        }
        return false;
    }


}