<?php


namespace ir\e\drivers;

/*
 * Tp5.*下使用数据为存储仓库
 *
 */

#Exception
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\exception\PDOException;

class Tp5Db extends Driver
{

    private $_table = 'event_pool';

    /**
     * @param array $args
     * @param string $rawArgs
     */

    protected function _init($args, $rawArgs)
    {
        if(isset($args['table'])) {
            $this->_table = $args['table'];
        }else{
            $this->_table = preg_replace('/\W+/','',$rawArgs);
        }
    }

    /**
     * @param $id
     * @return mixed false|['字段名'=>'字段值', ...]
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function get($id)
    {
        return \think\Db::table($this->_table)->where('id', $id)->find();
    }

    /**
     * 移除事件监听器动作
     * @param $id
     * @return bool
     * @throws Exception
     * @throws PDOException
     */

    public function remove($id)
    {
        $r = \think\Db::table($this->_table)->where('id', $id)->delete();
        return $r !== false;
    }

    /**
     * 插入事件监听器动作到池中
     * @param array $data [
     *        'name'=>'',
     *        'dependency'=>'',
     *        'args'=>[]
     *    ];
     * @return int
     */
    public function create($data)
    {
        return  \think\Db::table($this->_table)->insert($data, false, true);//返回ID
    }

    /**
     * 检查事件监听器动作否存在
     * @param $sign
     * @return int 0|id
     */

    public function isExist($sign)
    {
        $id = \think\Db::table($this->_table)->where('sign', $sign)->value('id');
       return  empty($id) ? 0 : $id; //0|id
    }

    /**
     * 暂停事件监听器动作
     * @param int $poolId
     * @param int $time 时间戳
     * @return bool
     * @throws Exception
     * @throws PDOException
     */

    public function setStartingTime($poolId, $time)
    {
        return ((\think\Db::table($this->_table)->where('id', $poolId)->update(['starting_time' => $time])) !== false);
    }

    /**
     * 扫描可运行的任务
     */

    public function scan()
    {
        return (\think\Db::table($this->_table)->where('starting_time', '<', time())->order('starting_time,id')->find());
    }
}