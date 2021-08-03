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
    private $_dbCls = '';
    protected function _init($args, $rawArgs)
    {
        parent::_init($args, $rawArgs);
        if(defined('THINK_VERSION')){
            $versionInfo = explode('.',THINK_VERSION);
            if($versionInfo<5){
                $this->_model = m($this->_table);
            }
        }

        if(!$this->_model){
            if(class_exists('\think\Db')){
                $this->_dbCls = '\\think\\Db'; //TP V5.0.*
            }else {
                $this->_dbCls = '\\think\\facade\\Db'; //TP >= V5.1
            }
        }
    }

    protected function _query($sql)
    {
        if($this->_model){
            return $this->_model->query($sql);//TP V1.*-V3.*
        }else{
            $dbCls = $this->_dbCls;
            $dbCls::query($sql);
        }
    }

    protected function _exec($sql,$sqlType)
    {
        if($this->_model){
            $result = $this->_model->execute($sql);
        }else {
            $dbCls = $this->_dbCls;
            $result = $dbCls::execute($sql);
        }
        return $result !=false;
    }
}