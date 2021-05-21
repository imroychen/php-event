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
            $driver = explode(':', App::cfg('store_driver'));
            $cls = $driver[0];
            unset($driver[0]);
            $args = count($driver) > 0 ? implode(':', $driver) : '';
            self::$_driver = new $cls($args);
            self::resetRuntimeTracking(0);
        }
        return self::$_driver;
    }

    /**
     * 读取事件消息
     * @param $id
     * @return mixed false|['字段名'=>'字段值', ...]
     */

	static function get($id){
		return self::_driver()->get($id);
	}

	/**
	 * 移除事件消息
	 * @param $id
	 * @return bool
	 */

	static function remove($id){
	    self::resetRuntimeTracking($id);
        return self::_driver()->remove($id);
	}

	/**
	 * 插入事件消息到池中
	 * @param array $data [
	 *		'event_name'=>'',
	 * 		'event_sign'=>'',
	 *
	 *		'event_listener'=>'',	 *
	 *		'event_dependency'=>'',
	 *		'event_cfg'=>[]
	 *	];
	 * @return int
	 */

	static function add($data){
		$id = self::_driver()->create($data);

		self::setMark($data['starting_time']);
		return $id;//返回ID
	}

	/**
	 * 检查事件事件消息否存在
	 * @param $sign
	 * @return int 0|id
	 */

	static function isExist($sign){
		return self::_driver()->isExist($sign);
	}

	/**
	 * 暂停事件事件消息
	 * @param int $poolId
	 * @param int $s 暂停多少秒
	 * @return bool
	 */

	static function pause($id,$s){
        $time = time()+$s;
		self::setMark($time);
		return self::_driver()->setStartingTime($id,$time);
	}

    /**
     * @param string $id 事件ID
     * @return array
     */

	static function getRuntimeTracking($id){
        $file = App::cfg('temp_path') . DIRECTORY_SEPARATOR . 'event_runtime_tracking';
        $text = file_get_contents($file);
	    $list = explode("\n",$text);
	    $r = [];
	    foreach ($list as $v){
	        if($v!='' && strpos($v,',')) {
                list($id, $listener,$status) = explode(',', $v);
                $r[$id][$listener] = $status;
            }
        }
	    return isset($r[$id])? $r[$id]:[];

    }
	static function setRuntimeTracking($id,$listener,$status = 1){
	    file_put_contents(App::cfg('temp_path').DIRECTORY_SEPARATOR.'event_runtime_tracking',"\n".$id.','.$listener.','.$status,FILE_APPEND);
    }
    static function resetRuntimeTracking($id){
        $file = App::cfg('temp_path') . DIRECTORY_SEPARATOR . 'event_runtime_tracking';

        if(file_exists($file)) {
            $list = explode("\n", file_get_contents($file));
            $r = [];
            foreach ($list as $v) {
                if ($v != '' && strpos($v, $id . ',') !== 0) {
                    $r[] = $v;
                }
            }
            file_put_contents($file,implode("\n",$r));
        }else{
            file_put_contents($file,"");
        }


    }

    /**
     * 临时目录 确保 WEB 和 CLI 都能操作该文件
     * @return string
     */
    static private function _getMarkPath(){
        return App::cfg('temp_path') .DIRECTORY_SEPARATOR. 'event-queue-mark';
    }

    /**
     * 扫描可运行的任务
     * @return mixed
     */
    static function scan(){
        $r = self::_driver()->scan();
        if(!empty($r) && $r['id']) {
            Pool::pause($r['id'], 20);
        }
        return $r;
    }


	static function setMark($time,$compulsory=false){
		$f = self::_getMarkPath();

		if($compulsory){
			file_put_contents($f, $time);
		}else {
			$content = file_get_contents($f);
			if (!empty($content)) {
				$val = min(intval($content), $time);
			} else {
				$val = $time;
			}
			file_put_contents($f, $val);
		}

		return true;
	}

	static function getMark(){
        $f = self::_getMarkPath();
		$content = file_get_contents($f);
		return intval($content);
	}



	static function createSign($str){
		return strtolower(substr(md5($str),8,16));
	}

	static function dataEncode($data){
		return json_encode($data);
	}

	static function dataDecode($dataStr){
		return json_decode($dataStr,true);
	}

}