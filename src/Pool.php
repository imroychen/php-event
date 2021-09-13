<?php
namespace iry\e;

/**
 * Class Pool
 * @package iry\e
 */
class Pool
{

    static private $_driver;
    /**
     * @return drivers\Driver
     */
    static private function _driver(){

        if(empty(self::$_driver)){
            $driverCfg = App::cfg()->getPoolDriver();
            if ($driverCfg[0] === '@') {
                $driverCfg = str_replace('^@',__NAMESPACE__ . '\\drivers\\','^'.$driverCfg);
            }

            $driver = explode('?', $driverCfg);

            $cls = $driver[0];
            unset($driver[0]);
            $args = count($driver) > 0 ? implode('?', $driver) : '';
            self::$_driver = new $cls($args);
        }
        return self::$_driver;
    }

    /**
     * 读取事件消息
     * @param $id
     * @return mixed false|['字段名'=>'字段值', ...]
     */

	static function get($id){
		$r = self::_driver()->get($id);
        $r['args'] = self::_dataDecode($r['args']);
        $r['result'] = (isset($r['result']) && !empty($r['result']))? unserialize($r['result']):[];
        return $r;
	}

	/**
	 * 移除事件消息
	 * @param $id
	 * @return bool
	 */

	static function remove($id){
        $driver = self::_driver();
        return $driver->remove($id);
	}

	/**
	 * 插入事件消息到池中
	 * @param array $data [...
	 *	];
	 * @return string
	 */

	static function add($data,$time){
        $data['args']=self::_dataEncode( (isset($data['args'])?$data['args']:[]) );
        $driver = self::_driver();
        $id = $driver->create($data,$time);
        $driver->sendSignal($time);
		return $id;//返回ID
	}

	/**
	 * 暂停事件事件消息
	 * @param string $id
	 * @param int $s 暂停多少秒
	 * @return bool
	 */

	static function pause($id,$s){
        $time = time()+$s;
        $driver = self::_driver();
		return $driver->setStartingTime($id,$time);
        $driver->sendSignal($time);
	}

	static function setResult($id,$res){
        self::_driver()->setResult($id,serialize($res));
    }

    static function getMinTime(){
       return self::_driver()->getMinTime();
    }

    /**
     * 扫描可运行的任务
     * @return mixed
     */
    static function scan(){

        $r = self::_driver()->scan();
        if(!empty($r) && $r['id']) {
            $r['args'] = self::_dataDecode($r['args']);
            $r['result'] = (isset($r['result']) && !empty($r['result']))? unserialize($r['result']):[];
            Pool::pause($r['id'], 20);
        }
        return $r;
    }

    static function initSignal(){
        self::_driver()->initSignal();
    }

    static function sendSignal($time){
        return self::_driver()->sendSignal($time);
    }

    static function getSignal(){
        return intval(self::_driver()->getSignal());
    }


	private static function _createSign($data){
        $str = $data['name'] . '/' .  $data['args'];
		return strtolower(substr(md5($str),8,16));
	}

	private static function _dataEncode($data){
		return json_encode($data);
	}

	private static function _dataDecode($dataStr){
		return json_decode($dataStr,true);
	}

}