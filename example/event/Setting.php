<?php


namespace MyNamespace\event;


class Setting implements \iry\e\interfaces\Setting
{

    /**
     * 当前项目或者设置的名称
     * The name of the current project or setting
     *
     * 多套事件同时使用时 该名称用于隔离
     * When multiple sets of events are used at the same time, the name is used for isolation
     *
     * @return string
     *  允许 字母、数组、下划线
     *  Allow letters, arrays, underscores
     *
     * @inheritDoc
     */
    public function name(){
        return 'default';
    }


    public function getPoolDriver()
    {
        return '@Sqlite?dsn=sqlite:./database/sqlite.db&table=ir_event_pool';
        //return '@Redis?host=localhost&port=6379&password=123&key=ir-e-store';
        //return '\MyNamespace\event\MyDriver'
    }

    /**
     * List subscribers
     * 获取订阅者列表
     *
     * @return array ['Subscriber Class1','Subscriber Class2']
     *
     * @inheritDoc
     */
    public function getSubscribers()
    {
        $result = [];

        $files = glob(__DIR__ . DIRECTORY_SEPARATOR . 'subscribers' . DIRECTORY_SEPARATOR . '*.php');
        foreach ($files as $f) {
            $clsName = preg_replace('/^class\.|\.class\.php$|\.php$/i', '', basename($f));
            $result[] = __NAMESPACE__ . '\\subscribers\\' . $clsName;
        }
        return $result;
    }

    /**
     * 事件规则配置
     * @inheritDoc
     */
    public function getEventRules()
    {
        return __NAMESPACE__.'\\Event';
    }



    /**
     * Writable temporary directory     *
     * 临时目录 (可写的临时目录) 推荐系统临时目录
     *
     * @return string 绝对路径
     * @inheritDoc
     */
    public function getTempPath(){
        return sys_get_temp_dir();
    }
}