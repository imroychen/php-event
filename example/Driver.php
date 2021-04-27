<?php
namespace MyNamespace;

use ir\e\drivers\Mysql;

class Driver extends Mysql
{
    /**
     * @param $sql
     * @return array
     */
    protected function _query($sql)
    {
        //query sql
        return [
            ['field'=>'value', 'more fields...'],
            'more records....'
        ];
    }

    /**
     * @param $sql
     * @return bool
     */

    protected function _exec($sql)
    {
        // TODO: Implement _exec() method.
        //exec sql
        return true;
    }
}