<?php
namespace ir\e\drivers;
use ir\e\App;

abstract class Driver
{
    /**
     * Driver constructor.
     * @param $rawArgs
     */
    function __construct($rawArgs){
        $_argsArr = explode('&',$rawArgs);
        $args = [];
        if(count($_argsArr)>0) {
            foreach ($_argsArr as $item) {
                $tmp = explode('=', $item . '=');
                $args[trim($tmp[0])] = urldecode(trim($tmp[1]));
            }
        }
        return $this->_init($args,$rawArgs);
    }
    abstract protected function _init($args,$rawArgs);

    /**
     * 获取指定的数据
     * @param $id
     * @return mixed false|['id'=>int, 'name'=>'string name'...]
     */

    abstract function get($id);

    /**
     * 移除一条数据
     * @param $id
     * @return bool
     */

    abstract function remove($id);

    /**
     * 保存一条数据
     * @param array $data [
     *        'name'=>'',
     *        'dependency'=>'',
     *      'starting_time'=0,
     *        'args'=>[]
     *    ];
     * @param $time 时间戳
     * @return int id
     */

    abstract function create($data,$time);

    /**
     * 修改指定 ID 数据的 starting_time值
     * @param int $id
     * @param int $time 暂停多少秒
     * @return bool
     */

    abstract function setStartingTime($id, $time);

    protected function _createUniqId($data,$checkOriginalId=true){
        if($checkOriginalId && isset($data['id']) && !empty($data['id'])) {
            return $data['id'];
        }

        $str = $data['name'] . '/' . $data['args'];
        return strtolower(substr(md5($str), 8, 16));
    }

    /**
     * 临时目录 确保 WEB 和 CLI 都能操作该文件
     * @return string
     */
    static private function _getMarkPath(){
        return App::cfg('temp_path') .DIRECTORY_SEPARATOR. 'event-queue-mark';
    }
    function setMark($time,$compulsory){
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

    function getMark(){
        $f = self::_getMarkPath();
        $content = file_get_contents($f);
        return intval($content);
    }

    //下面的方法 用于记录每个订阅者的收到消息的相应结果
    //您可以重写该方法
    /**
     * 获取指定消息的 订阅确认情况
     * @param string $id 事件ID
     * @return array
     */

    function getRuntimeTracking($id){
        $file = App::cfg('temp_path') . DIRECTORY_SEPARATOR . 'ir-e-t_'.md5($id);
        if(file_exists($id)){
            $text = file_get_contents($file);
            $r = unserialize($text);
            return  is_array($r)?$r:[];
        }
        return [];
    }
    
    function setRuntimeTracking($id,$listener,$status){
        $file = App::cfg('temp_path') . DIRECTORY_SEPARATOR . 'ir-e-t_'.md5($id);
        $val = $this->get($id);
        $val[$listener] = $status;
        file_put_contents($file,serialize($val));
    }
    function rmRuntimeTracking($id){
        $file = App::cfg('temp_path') . DIRECTORY_SEPARATOR . 'ir-e-t_'.md5($id);
        unlink($file);
    }
}