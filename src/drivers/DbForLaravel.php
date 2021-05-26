<?php


namespace ir\e\drivers;

/*
 * Laravel *下使用数据为存储仓库
 *
 * 使用方法
 * @DbForLaravel?table=tableName
 * 或
 * @DbForLaravel?tableName
 */

class DbForLaravel extends Db
{

    protected function _query($sql)
    {
        $results = \Illuminate\Support\Facades\DB::select($sql);
        return $this->_itemToArray($results);
    }

    protected function _exec($sql,$sqlType)
    {
        if ($sqlType==='insert'){
            $res = \Illuminate\Support\Facades\DB::insert($sql);
            return $res !==false;
        }elseif(in_array($sqlType,['delete','update'])){
            $res = \Illuminate\Support\Facades\DB::$sqlType($sql);
            return ($res!==false && $res>=0);
        }else{
            return false;
        }
    }
}