<?php
namespace iry\e\drivers;
use iry\e\App;

abstract class Driver
{
    private $_markFile;
    /**
     * Driver constructor.
     * @param $rawArgs
     */
    function __construct($rawArgs){
        $this->_markFile = App::getTempPath( App::cfg()->name().'-iry-event-signal-pipeline');
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
     *        'starting_time'=0,
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

    abstract function setResult($id,$res);

    /**
     * 获取最小的时间
     * @return int -1：未找到
     */
    abstract function getMinTime();

    /**
     * 创建一个唯一ID
     * @param $data
     * @param bool $checkOriginalId
     * @return string
     */
    protected function _createUniqId($data,$checkOriginalId=true){
        if($checkOriginalId && isset($data['id']) && !empty($data['id'])) {
            return $data['id'];
        }

        $str = $data['name'] . '/' . $data['args'];
        return strtolower(substr(md5($str), 8, 16));
    }

    function initSignal(){
        $f = $this->_markFile;
        if(!file_exists($f)){
            file_put_contents($f,'');
        }
        chmod($f,0666);
    }

    /**
     * 发送信号给服务端
     * 如果是文件模拟 请确保该文件 确保 WEB 和 CLI 都能操作该文件
     * @param int $time
     * @return string
     */
    function sendSignal($time){
        $f = $this->_markFile;
        file_put_contents($f, $time);
        return true;
    }

    /**
     * 接收信号
     * @return int
     */

    function getSignal(){
        $f = $this->_markFile;
        try {
            $content = file_get_contents($f);
        }catch (\Exception $e){
            $this->initSignal();
            $content = '';
        }

        return intval($content);
    }
}