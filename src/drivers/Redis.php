<?php


namespace ir\e\drivers;

/**
 * Redis 驱动
 * 该驱动不能直接使用，需要继承后自己去实现 _query和 _exec两个方法
 * @package ir\e\drivers
 */
abstract class Redis implements Driver
{
    protected $_dataset, $_dataset_bak;
    protected $_redis;


    public function __construct($args)
    {
        $this->_dataset = 'ir-e-store';
        $this->_dataset_bak = $this->_dataset.'-bak';

        if ($args != '') {
            $this->_table = $args;
        }
    }

    /**
     * @param $id
     * @return mixed false|['字段名'=>'字段值', ...]
     */
    public function get($id)
    {
        return json_decode($id,true);
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
     * @param array $data [
     *        'name'=>'',
     *        'sign'=>'',
     *
     *        //'listener'=>'',     *
     *        'dependency'=>'',
     *        'cfg'=>[]
     *    ];
     * @return int
     */
    public function create($data)
    {
        if (!empty($data)) {
            $startingTime = isset($data['string_time'])?$data['string_time']:0;
            $startingTime = $startingTime<1?1:$startingTime;
            $str = json_encode($data);
        }
        $this->_redis->zAdd($this->_dataset, $startingTime,$str);
        return $str;
    }


    /**
     * 检查事件监听器动作否存在
     * @param $sign
     * @return  mixed false|id
     */

    public function isExist($sign)
    {
        return false;
        //$res = $redis->zScore($this->_dataset, "three");
    }

    /**
     * 暂停事件监听器动作
     * @param string $id
     * @param int $time 时间戳
     * @return bool
     */

    public function setStartingTime($id, $time)
    {
        $data = $this->get($id);
        $data['starting_time'] = $time;
        $this->create($data);
    }

    /**
     * 扫描可运行的任务
     */

    public function scan()
    {
        $time = time();
        $res = $this->_redis->zAdd($this->_dataset, 0,$time,['limit' => [0, 1]]);
        if($res){
            $this->_redis->zAdd($this->_dataset_bak, $time+300, $res);
            return $this->get($res);
        }
        return false;
    }
}