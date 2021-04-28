<?php


namespace MyNamespace;

/**
 * 事件配置
 * @package MyNamespace
 *
 * 一个方法一个事件
 * 事件返回结果如下
 * [
 *     'args'=>array 支持的参数 可选
 *     'requires': array 必须的参数字段 可选
 *     'check_function'=>callback function($args){return true|false;} 可选 *
 * ]
 */

class Event
{
    static function __callStatic($name, $arguments)
    {
        return [];
    }

    static function test(){
        return [
            'requires'=>['test_id','test_name']
        ];
    }

    static function beforeRequest(){
        return [];
    }
}