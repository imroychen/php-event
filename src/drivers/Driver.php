<?php
namespace ir\e\drivers;
interface Driver
{
    /**
     * Driver constructor.
     * @param string $args
     */
    function __construct($args);

    /**
     * 获取指定的数据
     * @param $id
     * @return mixed false|['id'=>int, 'name'=>'string name'...]
     */

    function get($id);

    /**
     * 移除一条数据
     * @param $id
     * @return bool
     */

    function remove($id);

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

    function create($data);

    /**
     * 检查数据是否存在
     * @param $sign
     * @return int 0|id
     */

    function isExist($sign);

    /**
     * 修改指定 ID 数据的 starting_time值
     * @param int $id
     * @param int $time 暂停多少秒
     * @return bool
     */

    function setStartingTime($id, $time);
}