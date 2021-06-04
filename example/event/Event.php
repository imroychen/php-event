<?php


namespace MyNamespace\event;

/**
 * 事件配置
 * @package MyNamespace
 *
 * 一个方法一个事件
 * 事件返回结果如下
 * [
 *      'args'=>array 可用参数列表 可选
 *      'requires'=>[] 必须的参数字段 可选
 *      'check_function'=>callback function($args){return true|false;} 可选 *
 *
 *      'actions'=>[],// '绑定的动作' 可选
 *      'exec'=>[] //'在该事件触发处注入代码(Class)，同步运行会阻塞源代码,作用：在不改变源代码逻辑结构的情况下执行指定代码'
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
            'requires'=>['test_id','test_name'],
            'actions'=>['TestAction']
        ];
    }

    static function beforeRequest(){
        return [];
    }
}