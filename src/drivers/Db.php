<?php


namespace ir\e\drivers;

/**
 *
 * 该驱动不能直接使用，需要继承后自己去实现 _query和 _exec两个方法
 * 使用方法
 * 1.推荐 驱动Class名?table=tablename
 * 如 \MyNameSpace\Mysql?table=ir_event_pool
 * 2.驱动Class名?tablename
 *
 *
 * @example
  ```php
    namespace MyNameSpace;
    class MyDriver extend ir\e\drivers\Db{
       protected function _query(){
            //todo
           return [ ['field'=>'value', 'more fields...'], 'more records....']
       }

       protected function _exec(){
            //todo
           return true|false;
       }
  }
  ```
 *
 * @package ir\e\drivers
 */

/*
-- Mysql 表
 CREATE TABLE IF NOT EXISTS `ir_event_pool` (
  `id` varchar(32) NOT NULL,
  `name` varchar(100) NOT NULL,
  `starting_time` int(11) NOT NULL DEFAULT 0,
  `dependency` int(11) NOT NULL DEFAULT 0,
  `cfg` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `starting_time` (`starting_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- sqlite
create table ir_event_pool
(
    id            varchar(32)  not null primary key,
    name          varchar(100) not null,
    starting_time int(11) default 0 not null,
    dependency    int(11) default 0 not null,
    cfg           text         not null
);
create index starting_time  on ir_event_pool (starting_time);
*/

abstract class Db extends Driver
{
    protected $_table='ir_event_pool';

    /**
     * @param string $sql
     * @return array    [ ['field'=>'value', 'more fields...'], 'more records....']
     */

    abstract protected function _query($sql);

    /**
     * @param string $sql
     * @param string $sqlType 'delete/insert/update'
     * @return bool
     */

    abstract protected function _exec($sql,$sqlType);

    /**
     * @param array $args
     * @param string $rawArgs
     */

    protected function _init($args, $rawArgs)
    {
        if(isset($args['table'])) {
            $this->_table = $args['table'];
        }elseif(!empty($rawArgs)){
            $this->_table = preg_replace('/\W+/','',$rawArgs);
        }
    }

    protected function _itemToArray($result){
        if(!empty($result)){
            foreach ($result as &$v) {
                if (is_object($v)) {
                    $v = get_object_vars($v);
                }
            }unset($v);
        }
        return $result;
    }

    private function _sql($sql){
        return str_replace('{{table}}',$this->_table,$sql);
    }


    private function _getRecord($sql,$appendLimit = true){
        $r = $this->_query($sql.($appendLimit?' limit 0,1':''));
        if($r){
            return $r[0];
        }
        return false;
    }

    /**
     * @param string $id
     * @return mixed false|['字段名'=>'字段值', ...]
     */
    public function get($id)
    {
        $id = var_export(strval($id),true);
        return $this->_getRecord($this->_sql('select * from {{table}} where `id`='.$id));
    }

    /**
     * 移除事件消息
     * @param int $id
     * @return bool
     */

    public function remove($id)
    {
        $id = var_export(strval($id),true);
        $r = $this->_exec($this->_sql('delete from {{table}} where `id`='.$id),'delete');
        return $r!==false;
    }

    /**
     * 插入事件消息到池中
     * @param array $data [
     *        'name'=>'',
     *
     *        //'listener'=>'',     *
     *        'dependency'=>'',
     *         'starting_time'
     *        'cfg'=>[]
     *    ];
     * @return string $id
     */
    public function create($data)
    {
        if(!empty($data)) {
            $id = $data['id'];
            $fields = '';
            $values = '';
            foreach ($data as $f => $v) {
                $fields[] = '`' . $f . '`';
                $values[] = var_export($v, true);
            }
            $res = $this->_exec($this->_sql('insert into {{table}} ' . $fields . ' values ' . $values),'insert');
            return $res ? $id:false;
        }
        return true;
    }

    /**
     * 检查事件消息否存在
     * @param string $id
     * @return bool|string false|id
     */

    public function isExist($id)
    {
        $id = var_export(strval($id),true);
        $_res = $this->_getRecord($this->_sql('select `id` from {{table}} where `id`='.$id));
        return (is_array($_res) && !empty($_res['id']))? $_res['id']: false;
    }

    /**
     * 暂停事件消息
     * @param string $id
     * @param int $time 时间戳
     * @return bool
     */

    public function setStartingTime($id, $time)
    {
        $id = var_export(strval($id),true);
        return $this->_exec($this->_sql('update {{table}} set `starting_time`='.$time.' where `id`='.$id ), 'update' );
    }

    /**
     * 扫描可运行的任务
     */

    public function scan()
    {
        $time = time();
        return $this->_getRecord($this->_sql('select * from {{table}} where `starting_time`<'.$time.' order by `starting_time`'));
    }
}