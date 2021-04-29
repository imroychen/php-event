<?php


namespace ir\e\drivers;

/*
 * Tp3.*下使用数据为存储仓库
 */

class Tp3Db extends Driver
{

    private $_table = 'event_pool';

    /**
     * @param array $args
     * @param string $rawArgs
     */

    protected function _init($args, $rawArgs)
    {
        $this->_table = $rawArgs;
    }

    private function _m(){
        return M($this->_table);
    }

    /**
     * @param $id
     * @return mixed false|['字段名'=>'字段值', ...]
     */
    public function get($id)
    {
        $id = intval($id);
        return $this->_m()->where(['id'=>$id])->find();
    }

    /**
     * 移除事件监听器动作
     * @param int $id
     * @return bool
     */

    public function remove($id)
    {
        $id = intval($id);
        $r =  $this->_m()->where(['id'=>$id])->delete();
        return $r!==false;
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
        return  $this->_m()->add($data);//返回ID
    }

    /**
     * 检查事件监听器动作否存在
     * @param $sign
     * @return int 0|id
     */

    public function isExist($sign)
    {
        $_res = $this->_m()->where(['event_listener_sign'=>$sign])->field('id')->find();
        return intval((!empty($_res) && !empty($_res['id']))? $_res['id']: 0 ); //0|id
    }

    /**
     * 暂停事件监听器动作
     * @param int $id
     * @param int $time 时间戳
     * @return bool
     */

    public function setStartingTime($id, $time)
    {
        $id = intval($id);
        return ( ($this->_m()->where(['id'=>$id])->data(['starting_time'=>$time])->save())!==false );
    }

    /**
     * 扫描可运行的任务
     */

    public function scan()
    {
        $time = time();
        return $this->_m()->where("starting_time<$time")->order('starting_time,id')->find();
    }
}