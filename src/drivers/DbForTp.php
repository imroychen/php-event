<?php


namespace ir\e\drivers;

/*
 * Laravel *下使用数据为存储仓库
 *
 * 使用方法
 * @DbForTp?table=tableName
 */

class DbForTp extends Db
{

    private $_model = false;
    protected function _init($args, $rawArgs)
    {
        parent::_init($args, $rawArgs);
        if(defined(THINK_VERSION)){
            $versionInfo = explode('.',THINK_VERSION);
            if($versionInfo<5){
                $this->_model = m($this->_table);
            }
        }
    }

    protected function _query($sql)
    {
        if($this->_model){
            return $this->_model->query($sql);
        }else {
            return \think\Db::query($sql);
        }
    }

    protected function _exec($sql,$sqlType)
    {
        if($this->_model){
            $result = $this->_model->execute($sql);
        }else {
            $result = \think\Db::execute($sql);
        }
        return $result !=false;
    }
}