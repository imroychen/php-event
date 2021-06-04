<?php
namespace ir\e;

/**
 * Class Pool
 * @package ir\e
 */
class Pool
{

    static private $_driver;
    /**
     * @return drivers\Driver
     */
    static private function _driver(){

        if(empty(self::$_driver)){
            $driver = explode('?', App::cfg('store_driver'));
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
        return $r;
	}

	/**
	 * 移除事件消息
	 * @param $id
	 * @return bool
	 */

	static function remove($id){
        $driver = self::_driver();
        $driver->rmRuntimeTracking($id);
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
        $driver->setMark($time,false);
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
        $driver->setMark($time,false);
		return $driver->setStartingTime($id,$time);
	}

    /**
     * 扫描可运行的任务
     * @return mixed
     */
    static function scan(){

        $r = self::_driver()->scan();
        if(!empty($r) && $r['id']) {
            $r['args'] = self::_dataDecode($r['args']);
            Pool::pause($r['id'], 20);
        }
        return $r;
    }

    static function setMark($time,$compulsory=false){
        return self::_driver()->setMark($time,$compulsory);
    }

    static function getMark(){
        return intval(self::_driver()->getMark());
    }

    /**
     * @param $id
     * @return array
     */
    static function getRuntimeTracking($id){
        return self::_driver()->getRuntimeTracking($id);
    }

    static function setRuntimeTracking($id,$val,$status=1){
        return self::_driver()->setRuntimeTracking($id,$val,$status);
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