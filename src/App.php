<?php


namespace ir\e;

class App
{
    static private  $_cfg = [];
    static public function setCfg($cfg){
        if(!empty($cfg)) {
            if (isset($cfg['temp_path'])) {
                $cfg['temp_path'] = rtrim($cfg['temp_path'], '/\\');
            }

            if (isset($cfg['store_driver'])) {
                if ($cfg['store_driver'][0] === '@') {
                    $cfg['store_driver'] = __NAMESPACE__ . '\\drivers';
                }
            }

            self::$_cfg = array_merge(self::$_cfg, $cfg);
        }

        if(!isset(self::$_cfg['temp_path'])){
            self::$_cfg['temp_path'] = sys_get_temp_dir();
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
            $eventSign = Pool::createSign($event . '/' . var_export($args, true));

            $poolId = Pool::isExist($eventSign);

            if (!$poolId) {
                $data = [
                    'name' => $event,
                    'listener' => '',
                    'sign' => $eventSign,
                    'pool_run_time' => time() + $delay,
                    'rely' => '',
                    'dependency' => $dependency,
                    //'cfg' => $args
                ];
                $data['cfg'] = Pool::dataEncode($args);
                //$data['listener_sign'] = $sign;
                $poolId = Pool::add($data);
            }
            return $poolId;
        }else{
            return false;
        }
    }


}