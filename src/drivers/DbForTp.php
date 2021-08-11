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

        $version = 0;
        if(defined('THINK_VERSION')){
            $versionInfo = explode('.',THINK_VERSION);
            $versionInfo = array_map('intval',$versionInfo);
            $version = $versionInfo[0]+round($versionInfo[1])/1000 + round($versionInfo[1])/1000000;
        }elseif(defined('\\think\\App::VERSION')) {
            $versionInfo = \think\App::VERSION;
            $versionInfo = explode('.',$versionInfo);
            $versionInfo = array_map('intval',$versionInfo);
            $version = $versionInfo[0]+round($versionInfo[1])/1000 + round($versionInfo[1])/1000000;
        }

        if($version>0){
            if($version<5){
                $this->_model = m($this->_table);
            }elseif ($version>=5 && $version<5.1){
                $this->_dbCls = '\\think\\Db'; //TP V5.0.*
                $this->_model = false;
            }else{
                $this->_dbCls = '\\think\\facade\\Db'; //TP >= V5.1
                $this->_model = false;
            }
        }else{
            exit('未知TP版本,请自定义驱动');
        }
    }

    protected function _query($sql)
    {
        if($this->_model){
            return $this->_model->query($sql);//TP V1.*-V3.*
        }else{
            $dbCls = $this->_dbCls;
            return $dbCls::query($sql);
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