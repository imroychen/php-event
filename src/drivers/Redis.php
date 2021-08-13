<?php


namespace iry\e\drivers;

/**
 * Redis 驱动
 * @Redis?host=localhost&port=6379&key=ir-e-store&password=123
 * @package iry\e\drivers
 */
class Redis extends Driver
{
    protected $_dataset, $_dataset_bak;
    /**
     * @var \Redis
     */
    protected $_redis;


    /**
     * Redis constructor.
     * @param array $args
     * @param string $rawArgs 'host=localhost&port=6379&key=ir-e-store&password=123'
     */
    protected function _init($args,$rawArgs)
    {
        $this->_dataset = (isset($args['key']) && !empty($args['key']))? preg_replace('[^\w\-]','',$args['key']):'ir-e-store';
        $this->_dataset_bak = $this->_dataset.'-bak';
        $this->_redis = new \Redis();
        $this->_redis->connect($args['host'], (isset($args['port'])?$args['port']:6379) );
        if(isset($args['password'])) {
            $this->_redis->auth($args['password']);
        }
    }

    /**
     * @param $id
     * @return mixed false|['字段名'=>'字段值', ...]
     */
    public function get($id)
    {
        $result =  json_decode($id,true);
        $result['result'] = $this->_getResult($id);
        return $result;
    }

    private function _getResult($id){
        $resultKey = 'ir-e'.md5($id);
        $result = $this->_redis->get($resultKey);
        return  empty($r)? $result:null;
    }

    /**
     * 移除事件监听器动作
     * @param string $id
     * @return bool
     */

    public function remove($id)
    {
        $res = $this->_redis->zRem($this->_dataset, $id);
        return $res? true:false;
    }

    /**
     * 插入事件监听器动作到池中
     * @param array $data [];
     * @param int $time 时间戳
     * @return int
     */
    public function create($data,$time)
    {
        if (!empty($data)) {
            $time = $time<1 ? (time()-1) : intval($time);
            $str = json_encode($data);
            $this->_redis->zAdd($this->_dataset, $time,$str);
            return $str;
        }
        return false;
    }


    /**
     * 暂停事件监听器动作
     * @param string $id
     * @param int $time 时间戳
     * @return bool
     */

    public function setStartingTime($id, $time)
    {
        $this->_redis->zAdd($this->_dataset, $time,$id );
        return true;
    }

    /**
     * @param string $id
     * @param string $result
     * @return bool
     */

    public function setResult($id, $result)
    {
        $key = 'ir-e'.md5($id);
        $this->_redis->set($key,$result);
        return true;
    }

    /**
     * @inheritDoc
     * @return int
     */
    function getMinTime()
    {
        $record = $this->_redis->zRange($this->_dataset, 0, 1, true);
        if(!empty($record)) {
            $item = current($record);
            //$r = $this->_redis->zScore($this->_dataset, $item);
            //return $r*1;
            return $item*1;
        }else{
            return -1;
        }

    }

    /**
     * 扫描可运行的任务
     */

    public function scan()
    {
        $records = $this->_redis->zRange($this->_dataset, 0, 0, true);

        if(!empty($records)) {
            foreach ($records as $text=>$score) {
                $res = json_decode($text, true);
                $res = is_array($res) ? $res : [];
                $res['id'] = $text;
                $res['result'] = $this->_getResult($text);
                return  $res;
            }
        }
        return false;
    }


    //========================重写信号方法========================
    public function getSignal(){
        return $this->_redis->get($this->_dataset.'__ir-e-mark');
    }

    public function sendSignal($time){
        $key = $this->_dataset . '__ir-e-mark';
        $this->_redis->set($key, $time);
        return true;
    }
}