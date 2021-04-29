<?php
namespace ir\e\drivers;
abstract class Driver
{
    /**
     * Driver constructor.
     * @param string $args
     */
    function __construct($rawArgs){
        $_argsArr = explode(';',$rawArgs);
        $args = [];
        foreach ($_argsArr as $item){
            $tmp = explode('=',$item.'=');
            $args[trim($tmp[0])] = trim($tmp[1]);
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
     *        'sign'=>'',
     *
     *        'listener'=>'',     *
     *        'dependency'=>'',
     *      'starting_time'=0,
     *        'cfg'=>[]
     *    ];
     * @return int id
     */

    abstract function create($data);

    /**
     * 检查数据是否存在
     * @param $sign
     * @return int 0|id
     */

    abstract function isExist($sign);

    /**
     * 修改指定 ID 数据的 starting_time值
     * @param int $id
     * @param int $time 暂停多少秒
     * @return bool
     */

    abstract function setStartingTime($id, $time);
}