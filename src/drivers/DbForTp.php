<?php


namespace iry\e\drivers;

/*
 * Tp *下使用数据为存储仓库
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
            return $this->_model->query($sql);//TP V1.*-V3.*
        }elseif(class_exists('\think\Db')){
            return \think\Db::query($sql); //TP V5.0.*
        }else{
            \think\facade\Db::query($sql); //TP >= V5.1
        }
    }

    protected function _exec($sql,$sqlType)
    {
        if($this->_model){
            $result = $this->_model->execute($sql);
        }elseif(class_exists('\think\Db')){
            $result = \think\Db::execute($sql);
        }else {
            $result = \think\facade\Db::execute($sql);
        }
        return $result !=false;
    }
}