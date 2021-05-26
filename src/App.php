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
    static private  $_cfg = [];
    static public function setCfg($cfg){
        if(!empty($cfg)) {
            if (isset($cfg['temp_path'])) {
                $cfg['temp_path'] = rtrim($cfg['temp_path'], '/\\');
            }

            if (isset($cfg['store_driver'])) {
                if ($cfg['store_driver'][0] === '@') {
                    $cfg['store_driver'] = str_replace('^@',__NAMESPACE__ . '\\drivers\\','^'.$cfg['store_driver']);
                }
            }

            self::$_cfg = array_merge(self::$_cfg, $cfg);
        }

        if(!isset(self::$_cfg['temp_path'])){
            self::$_cfg['temp_path'] = sys_get_temp_dir();
        }

        if(!isset(self::$_cfg['store_driver'])){//智能选择驱动
            if(defined('THINK_PATH') && defined(THINK_VERSION )){
                //thinkphp <5.1
                $info = explode('.',THINK_VERSION.'.0.0.0');
                $info = array_map('intval',$info);
                $version = $info[0]*1000*1000 + $info[1]*1000 + $info[2];
                //000 000 000
                if($version<5000000){
                    self::$_cfg['store_driver']=__NAMESPACE__ . '\\drivers\\DbForTp3';
                }else{
                    self::$_cfg['store_driver']=__NAMESPACE__ . '\\drivers\\DbForTp';
                }
            }elseif (class_exists('\\think\\Db')){ //thinkphp >5.1
                self::$_cfg['store_driver']=__NAMESPACE__ . '\\drivers\\DbForTp';
            }elseif (class_exists('\\Illuminate\\Support\\Facades\\DB')){ //laravel >5.1
                self::$_cfg['store_driver']=__NAMESPACE__ . '\\drivers\\DbForLaravel';
            }else{

            }
        }
    }

    static public function cfg($key){
        if(!empty(self::$_cfg)){
            self::setCfg([]);
        }
        return isset(self::$_cfg[$key])?self::$_cfg[$key]:false;
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
            $cls = self::cfg('event');
            $eventInfo  = method_exists($cls,$event)?$cls::$event():[];
            if($eventInfo['exec']){
                foreach ($eventInfo['exec'] as $cls){
                    new $cls();
                }
            }


            $eventSign = Pool::createSign($event . '/' . var_export($args, true));

            $id = Pool::isExist($eventSign);

            if (!$id) {
                $data = [
                    'id'=>$eventSign,
                    'name' => $event,
                    'starting_time' => time() + $delay,
                    'dependency' => $dependency,
                    'cfg' => Pool::dataEncode($args)
                ];
                //$data['cfg'] = Pool::dataEncode($args);
                $id = Pool::add($data);
            }
            return $id;
        }else{
            return false;
        }
    }


}