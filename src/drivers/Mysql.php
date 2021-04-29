<?php


namespace ir\e\drivers;

/**
 * Mysql 驱动
 * 该驱动不能直接使用，需要继承后自己去实现 _query和 _exec两个方法
 * @package ir\e\drivers
 */

abstract class Mysql extends Driver
{
    protected $_table='event_pool';

    /**
     * @param $sql
     * @return array    [ ['field'=>'value', 'more fields...'], 'more records....']
     */
    abstract protected function _query($sql);

    /**
     * @param $sql
     * @return bool
     */

    abstract protected function _exec($sql);

    /**
     * @param array $args
     * @param string $rawArgs
     */

    protected function _init($args,$rawArgs)
    {
        if($args != '') {
            $this->_table = $args;
        }
    }


    protected function _getLastId(){
        $res =  $this->_getRecord('SELECT LAST_INSERT_ID() as _id_',false);
        if(!empty($res) && isset($res['_id_'])){
            return $res['_id_'];
        }
        return false;
    }

    private function _getRecord($sql,$appendLimit = true){
        $r = $this->_query($sql.' limit 0,1');
        if($r){
            return $r[0];
        }
        return false;
    }

    /**
     * @param $id
     * @return mixed false|['字段名'=>'字段值', ...]
     */
    public function get($id)
    {
        $id = intval($id);
        return $this->_getRecord('select * from {{table}} where `id`='.$id);
    }

    /**
     * 移除事件监听器动作
     * @param int $id
     * @return bool
     */

    public function remove($id)
    {
        $id = intval($id);
        $r = $this->_query('delete from {{table}} where `id`='.$id);
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
        if(!empty($data)) {
            $fields = '';
            $values = '';
            foreach ($data as $f => $v) {
                $fields[] = '`' . $f . '`';
                $values[] = var_export($v, true);
            }
            $res = $this->_exec('insert into {{table}} ' . $fields . ' values ' . $values);
            return $res ? $this->_getLastId():false;
        }
        return true;
    }

    /**
     * 检查事件监听器动作否存在
     * @param $sign
     * @return int 0|id
     */

    public function isExist($sign)
    {
        $_res = $this->_getRecord('select `id` from {{table}} where `sign`='.var_export($sign.'',true));
        return intval((is_array($_res) && !empty($_res['id']))? $_res['id']: 0 ); //0|id
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
        return $this->_exec('update {{table}} set `starting_time`='.$time );
    }

    /**
     * 扫描可运行的任务
     */

    public function scan()
    {
        $time = time();
        return $this->_getRecord('select * from {{table}} where `starting_time`<'.$time.' order by `starting_time`,`id`');
    }
}